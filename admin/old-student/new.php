<?php
// Uncomment for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth.php';

requireLogin();

$currentPage = 'student-questions';
$surveyType = 'Student Survey Questions';

// Set the current grade level from tab (default to ES if not set)
$currentGradeLevel = isset($_GET['grade']) ? $_GET['grade'] : 'ES';
$validGradeLevels = ['ES', 'MS', 'HS'];
if (!in_array($currentGradeLevel, $validGradeLevels)) {
    $currentGradeLevel = 'ES';
}

// Add another variable for current question type (core or teacher)
$currentQuestionType = isset($_GET['type']) ? $_GET['type'] : 'core';
if (!in_array($currentQuestionType, ['core', 'teacher'])) {
    $currentQuestionType = 'core';
}

// Get all categories for the current grade level
$categoryStmt = $pdo->prepare("
    SELECT DISTINCT
        c.id as category_id,
        c.name as category_name
    FROM categories c
    WHERE c.type = 'student'
    AND (
        c.id IN (
            SELECT DISTINCT category_id 
            FROM questions 
            WHERE grade_level = ? AND feedback_type = ?
        )
        OR c.id NOT IN (
            SELECT DISTINCT category_id
            FROM questions
        )
    )
    ORDER BY c.id DESC
");
$categoryStmt->execute([$currentGradeLevel, $currentQuestionType]);
$categories = $categoryStmt->fetchAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug code - log entire POST data array
    $postData = json_encode($_POST);
    echo "<script>console.log('POST data received: " . addslashes($postData) . "');</script>";
    
    // Debug code - log POST action
    echo "<script>console.log('Form submitted with action: " . ($_POST['action'] ?? 'none') . "');</script>";
    
    if (isset($_POST['action'])) {
        try {
            $response = ['success' => true, 'message' => ''];
            
            switch ($_POST['action']) {
                case 'add_category':
                    if (empty($_POST['category_name'])) {
                        throw new Exception('Category name is required');
                    }
                    $stmt = $pdo->prepare("
                        INSERT INTO categories (name, type) 
                        VALUES (?, 'student')
                    ");
                    $stmt->execute([$_POST['category_name']]);
                    
                    // Get the newly created category ID
                    $categoryId = $pdo->lastInsertId();
                    
                    // Add a dummy question for the current grade level to associate the category with this grade level
                    if (!empty($categoryId)) {
                        $stmt = $pdo->prepare("
                            INSERT INTO questions (category_id, question_text, question_type, options, sort_order, grade_level, feedback_type)
                            VALUES (?, 'Initial placeholder question - please replace', 'text', NULL, 0, ?, ?)
                        ");
                        $stmt->execute([$categoryId, $currentGradeLevel, $currentQuestionType]);
                    }
                    
                    $response['message'] = 'Category added successfully';
                    break;

                case 'edit_category':
                    $categoryId = $_POST['category_id'] ?? 0;
                    $categoryName = $_POST['category_name'] ?? '';
                    
                    echo "<script>console.log('Edit category with ID: " . $categoryId . " - Name: " . addslashes($categoryName) . "');</script>";
                    
                    if ($categoryId && $categoryName) {
                        try {
                            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                            $result = $stmt->execute([$categoryName, $categoryId]);
                            
                            if ($result) {
                                echo "<script>console.log('Category updated successfully');</script>";
                                $_SESSION['success'] = "Category updated successfully.";
                            } else {
                                echo "<script>console.log('Failed to update category');</script>";
                                $_SESSION['error'] = "Failed to update category.";
                            }
                        } catch (PDOException $e) {
                            echo "<script>console.log('Database error: " . addslashes($e->getMessage()) . "');</script>";
                            $_SESSION['error'] = "Database error: " . $e->getMessage();
                        }
                    } else {
                        echo "<script>console.log('Invalid category data received');</script>";
                        $_SESSION['error'] = "Invalid category data.";
                    }
                    
                    // Redirect to avoid form resubmission
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();

                case 'add_question':
                    if (empty($_POST['question_text']) || empty($_POST['category_id'])) {
                        throw new Exception('Question text and category are required');
                    }
                    
                    // Get the grade level and question type
                    $grade_level = isset($_POST['grade_level']) ? $_POST['grade_level'] : $currentGradeLevel;
                    $feedback_type = isset($_POST['feedback_type']) ? $_POST['feedback_type'] : $currentQuestionType;
                    
                    // Handle question type and options
                    $questionType = $_POST['question_type'];
                    $options = null;
                    
                    if ($questionType === 'likert_scale') {
                        $likertPreset = $_POST['likert_preset'];
                        switch ($likertPreset) {
                            case 'agreement':
                                $options = json_encode(['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']);
                                break;
                            case 'numeric':
                                $options = json_encode(['1', '2', '3', '4', '5']);
                                break;
                            case 'frequency':
                                $options = json_encode(['Never', 'Rarely', 'Sometimes', 'Often', 'Always']);
                                break;
                            case 'custom':
                                if (isset($_POST['custom_scale']) && is_array($_POST['custom_scale'])) {
                                    $customOptions = array_filter(array_map('trim', $_POST['custom_scale']));
                                    if (count($customOptions) === 5) {
                                        $options = json_encode(array_values($customOptions)); // Reset array keys and encode
                                    } else {
                                        throw new Exception("All custom scale options are required");
                                    }
                                }
                                break;
                        }
                        
                        // Ensure options are set for Likert scale
                        if ($options === null) {
                            throw new Exception("Invalid Likert scale options");
                        }
                    } elseif (in_array($questionType, ['drop_down', 'checkbox']) && !empty($_POST['options'])) {
                        $options = $_POST['options'];
                    }
                    
                    // Insert the question
                    $stmt = $pdo->prepare("
                        INSERT INTO questions (category_id, question_text, question_type, options, sort_order, grade_level, feedback_type) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_POST['category_id'],
                        $_POST['question_text'],
                        $questionType,
                        $options,
                        $_POST['sort_order'] ?? 0,
                        $grade_level,
                        $feedback_type
                    ]);
                    $response['message'] = 'Question added successfully';
                    break;

                case 'edit_question':
                    $questionId = $_POST['question_id'] ?? 0;
                    $questionText = $_POST['question_text'] ?? '';
                    $questionType = $_POST['question_type'] ?? '';
                    $categoryId = $_POST['category_id'] ?? 0;
                    
                    echo "<script>console.log('Edit question with ID: " . $questionId . " - Type: " . addslashes($questionType) . "');</script>";
                    
                    // Process options based on question type
                    $options = '';
                    if ($questionType === 'likert_scale') {
                        if (isset($_POST['likert_preset']) && $_POST['likert_preset'] === 'custom') {
                            // Handle custom scale
                            if (isset($_POST['custom_scale']) && is_array($_POST['custom_scale'])) {
                                $options = json_encode(array_values(array_filter($_POST['custom_scale'], function($val) {
                                    return trim($val) !== '';
                                })));
                                echo "<script>console.log('Custom likert options: " . addslashes($options) . "');</script>";
                            }
                        } else {
                            // Handle preset scale
                            $preset = $_POST['likert_preset'] ?? 'agreement';
                            if ($preset === 'agreement') {
                                $options = json_encode(['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree']);
                            } elseif ($preset === 'numeric') {
                                $options = json_encode(['1', '2', '3', '4', '5']);
                            } elseif ($preset === 'frequency') {
                                $options = json_encode(['Never', 'Rarely', 'Sometimes', 'Often', 'Always']);
                            }
                            echo "<script>console.log('Preset likert options: " . addslashes($options) . "');</script>";
                        }
                    } elseif (in_array($questionType, ['drop_down', 'checkbox'])) {
                        // Handle comma-separated options
                        if (isset($_POST['options'])) {
                            $optionsList = explode(',', $_POST['options']);
                            $optionsList = array_map('trim', $optionsList);
                            $optionsList = array_filter($optionsList, function($val) {
                                return $val !== '';
                            });
                            $options = implode(',', $optionsList);
                            echo "<script>console.log('Options list: " . addslashes($options) . "');</script>";
                        }
                    }
                    
                    if ($questionId && $questionText && $questionType && $categoryId) {
                        try {
                            $stmt = $pdo->prepare("
                                UPDATE questions 
                                SET question_text = ?, question_type = ?, options = ?, category_id = ?
                                WHERE id = ?
                            ");
                            $result = $stmt->execute([$questionText, $questionType, $options, $categoryId, $questionId]);
                            
                            if ($result) {
                                echo "<script>console.log('Question updated successfully');</script>";
                                $_SESSION['success'] = "Question updated successfully.";
                            } else {
                                echo "<script>console.log('Failed to update question');</script>";
                                $_SESSION['error'] = "Failed to update question.";
                            }
                        } catch (PDOException $e) {
                            echo "<script>console.log('Database error: " . addslashes($e->getMessage()) . "');</script>";
                            $_SESSION['error'] = "Database error: " . $e->getMessage();
                        }
                    } else {
                        echo "<script>console.log('Invalid question data received');</script>";
                        $_SESSION['error'] = "Invalid question data.";
                    }
                    
                    // Redirect to avoid form resubmission
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();

                case 'delete_question':
                    if (empty($_POST['question_id'])) {
                        throw new Exception('Question ID is required');
                    }
                    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                    $stmt->execute([$_POST['question_id']]);
                    $response['message'] = 'Question deleted successfully';
                    break;

                case 'delete_category':
                    if (empty($_POST['category_id'])) {
                        throw new Exception('Category ID is required');
                    }
                    
                    // Get the category ID
                    $categoryId = $_POST['category_id'];
                    
                    // First delete all questions in this category for the current grade level and question type
                    $stmt = $pdo->prepare("
                        DELETE FROM questions 
                        WHERE category_id = ? AND grade_level = ? AND feedback_type = ?
                    ");
                    $stmt->execute([$categoryId, $currentGradeLevel, $currentQuestionType]);
                    
                    // Check if there are still questions in this category for ANY grade level
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count
                        FROM questions 
                        WHERE category_id = ?
                    ");
                    $stmt->execute([$categoryId]);
                    $count = $stmt->fetchColumn();
                    
                    // Only delete the category if there are no questions left
                    if ($count == 0) {
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        if ($stmt->execute([$categoryId])) {
                            $response['message'] = 'Section completely deleted';
                        } else {
                            throw new Exception('Failed to delete category');
                        }
                    } else {
                        $response['message'] = 'Questions deleted from this grade level but section is still used by other grade levels';
                    }
                    break;
            }
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            // Redirect back to the page if not AJAX
            header('Location: ' . $_SERVER['PHP_SELF'] . '?grade=' . $currentGradeLevel . '&type=' . $currentQuestionType);
            exit;
            
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            $error = $e->getMessage();
        }
    }
}

// Include header and sidebar
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<div class="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <?php if (isset($message)): ?>
                <div class="col-xs-12">
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="col-xs-12">
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grade Level Tabs -->
            <div class="col-xs-12">
                <div class="box-content card white">
                    <h4 class="box-title"><?php echo htmlspecialchars($surveyType); ?></h4>
                    <div class="card-content">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="<?php echo $currentGradeLevel === 'ES' ? 'active' : ''; ?>">
                                <a href="?grade=ES&type=<?php echo $currentQuestionType; ?>">Elementary School (PK3-5)</a>
                            </li>
                            <li role="presentation" class="<?php echo $currentGradeLevel === 'MS' ? 'active' : ''; ?>">
                                <a href="?grade=MS&type=<?php echo $currentQuestionType; ?>">Middle School (6-9)</a>
                            </li>
                            <li role="presentation" class="<?php echo $currentGradeLevel === 'HS' ? 'active' : ''; ?>">
                                <a href="?grade=HS&type=<?php echo $currentQuestionType; ?>">High School (10-12)</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Question Type Tabs -->
            <div class="col-xs-12">
                <div class="box-content card white">
                    <div class="card-content">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="<?php echo $currentQuestionType === 'core' ? 'active' : ''; ?>">
                                <a href="?grade=<?php echo $currentGradeLevel; ?>&type=core">Core Questions</a>
                            </li>
                            <li role="presentation" class="<?php echo $currentQuestionType === 'teacher' ? 'active' : ''; ?>">
                                <a href="?grade=<?php echo $currentGradeLevel; ?>&type=teacher">Teacher Questions</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Add Category Form -->
            <div class="col-xs-12">
                <div class="box-content card white">
                    <div class="card-content">
                        <form method="POST" class="category-form">
                            <input type="hidden" name="action" value="add_category">
                            <div class="input-group margin-bottom-20">
                                <input type="text" name="category_name" class="form-control" required 
                                       placeholder="Enter new section name">
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" style="z-index: 9999;">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="editCategoryModalLabel">Edit Section</h4>
                        </div>
                        <div class="modal-body">
                            <form method="POST" id="edit-category-form">
                                <input type="hidden" name="action" value="edit_category">
                                <input type="hidden" name="category_id" id="edit-category-id">
                                <input type="hidden" name="grade_level" value="<?php echo $currentGradeLevel; ?>">
                                <input type="hidden" name="feedback_type" value="<?php echo $currentQuestionType; ?>">
                                <div class="form-group">
                                    <label for="edit-category-name">Section Name</label>
                                    <input type="text" class="form-control" id="edit-category-name" name="category_name" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Question Modal -->
            <div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog" aria-labelledby="editQuestionModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="editQuestionModalLabel">Edit Question</h4>
                        </div>
                        <div class="modal-body">
                            <form id="edit-question-form" method="POST" action="">
                                <input type="hidden" name="action" value="edit_question">
                                <input type="hidden" name="question_id" value="">
                                <input type="hidden" name="category_id" value="">
                                <input type="hidden" name="grade_level" value="<?php echo $currentGradeLevel; ?>">
                                <input type="hidden" name="feedback_type" value="<?php echo $currentQuestionType; ?>">
                                <input type="hidden" name="sort_order" value="0">
                                
                                <div class="form-group">
                                    <label for="edit-question-text">Question Text</label>
                                    <textarea id="edit-question-text" name="question_text" class="form-control" required rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit-question-type">Question Type</label>
                                    <select id="edit-question-type" name="question_type" class="form-control question-type-select" required>
                                        <option value="">Select Type</option>
                                        <option value="likert_scale">Likert Scale</option>
                                        <option value="drop_down">Drop Down</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="text">Text Input</option>
                                    </select>
                                </div>
                                
                                <!-- Likert Scale Options -->
                                <div class="edit-likert-options" style="display: none;">
                                    <div class="form-group">
                                        <label for="edit-likert-preset">Scale Type</label>
                                        <select id="edit-likert-preset" name="likert_preset" class="form-control">
                                            <option value="agreement">Agreement (Strongly Disagree to Strongly Agree)</option>
                                            <option value="numeric">Numeric (1-5)</option>
                                            <option value="frequency">Frequency (Never to Always)</option>
                                            <option value="custom">Custom Scale</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Custom Likert Options -->
                                    <div id="edit-custom-likert-options" style="display: none;">
                                        <div class="form-group">
                                            <label>Custom Scale Options</label>
                                            <input type="text" name="custom_scale[]" class="form-control margin-bottom-10" placeholder="Option 1">
                                            <input type="text" name="custom_scale[]" class="form-control margin-bottom-10" placeholder="Option 2">
                                            <input type="text" name="custom_scale[]" class="form-control margin-bottom-10" placeholder="Option 3">
                                            <input type="text" name="custom_scale[]" class="form-control margin-bottom-10" placeholder="Option 4">
                                            <input type="text" name="custom_scale[]" class="form-control" placeholder="Option 5">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other Question Options -->
                                <div class="edit-options-group" style="display: none;">
                                    <div class="form-group">
                                        <label for="edit-options">Options</label>
                                        <input type="text" id="edit-options" name="options" class="form-control" 
                                               placeholder="Option 1, Option 2, Option 3">
                                        <small class="text-muted">Separate options with commas</small>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories and Questions for current grade level and question type -->
            <?php foreach ($categories as $category): ?>
            <div class="col-xs-12">
                <div class="box-content card white category-section">
                    <div class="card-content">
                        <div class="category-header">
                            <div class="category-title d-flex align-items-center">
                                <div class="title-content">
                                    <h4 class="box-title mb-0"><?php echo htmlspecialchars($category['category_name']); ?></h4>
                                </div>
                            </div>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-success waves-effect waves-light btn-sm toggle-question-form">
                                    <i class="fa fa-plus"></i> Add Question
                                </button>
                                <button type="button" class="btn btn-info waves-effect waves-light btn-sm edit-category-btn"
                                       data-category-id="<?php echo $category['category_id']; ?>"
                                       data-category-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-danger waves-effect waves-light btn-sm delete-category-btn" 
                                        data-category-id="<?php echo $category['category_id']; ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Question Form -->
                        <div class="question-form-container margin-top-20" style="display: none;">
                            <form method="POST" class="question-form">
                                <input type="hidden" name="action" value="add_question">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <input type="hidden" name="grade_level" value="<?php echo $currentGradeLevel; ?>">
                                <input type="hidden" name="feedback_type" value="<?php echo $currentQuestionType; ?>">
                                <input type="hidden" name="sort_order" value="0">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Question Text</label>
                                            <textarea name="question_text" class="form-control" required rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Question Type</label>
                                            <select name="question_type" class="form-control question-type-select" required>
                                                <option value="">Select Type</option>
                                                <option value="likert_scale">Likert Scale</option>
                                                <option value="drop_down">Drop Down</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="text">Text Response</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group likert-options" style="display: none;">
                                    <label>Scale Type</label>
                                    <select name="likert_preset" class="form-control">
                                        <option value="agreement">Agreement (Strongly Disagree to Strongly Agree)</option>
                                        <option value="numeric">Numeric (1 to 5)</option>
                                        <option value="frequency">Frequency (Never to Always)</option>
                                        <option value="custom">Custom Scale</option>
                                    </select>
                                    <div id="custom_likert_options" style="display: none;" class="mt-3">
                                        <label class="mt-2">Custom Scale Options</label>
                                        <div class="row">
                                            <div class="col-md-2 mb-2">
                                                <input type="text" name="custom_scale[]" class="form-control" placeholder="Option 1">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <input type="text" name="custom_scale[]" class="form-control" placeholder="Option 2">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <input type="text" name="custom_scale[]" class="form-control" placeholder="Option 3">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <input type="text" name="custom_scale[]" class="form-control" placeholder="Option 4">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <input type="text" name="custom_scale[]" class="form-control" placeholder="Option 5">
                                            </div>
                                        </div>
                                        <small class="text-muted">Enter your custom scale options (all fields required)</small>
                                    </div>
                                </div>

                                <div class="form-group options-group" style="display: none;">
                                    <label>Options</label>
                                    <input type="text" name="options" class="form-control" 
                                           placeholder="Option 1, Option 2, Option 3">
                                    <small class="text-muted">Separate options with commas</small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">Save Question</button>
                                    <button type="button" class="btn btn-default waves-effect waves-light cancel-question">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <!-- Questions List -->
                        <div class="table-responsive question-list margin-top-20">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Question</th>
                                        <th style="width: 120px;">Type</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Get questions for this category, current grade level and question type
                                    $questionStmt = $pdo->prepare("
                                        SELECT 
                                            id as question_id,
                                            question_text,
                                            question_type,
                                            options,
                                            sort_order
                                        FROM questions 
                                        WHERE category_id = ? AND grade_level = ? AND feedback_type = ?
                                        ORDER BY sort_order DESC, id DESC
                                    ");
                                    $questionStmt->execute([$category['category_id'], $currentGradeLevel, $currentQuestionType]);
                                    $questions = $questionStmt->fetchAll();
                                    
                                    foreach ($questions as $question): 
                                    ?>
                                    <tr class="question-item" data-id="<?php echo $question['question_id']; ?>">
                                        <td>
                                            <div class="handle">
                                                <i class="fa fa-bars"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="question-content">
                                                <div class="question-text">
                                                    <?php echo htmlspecialchars($question['question_text']); ?>
                                                </div>
                                                <?php if ($question['options']): ?>
                                                <small class="text-muted">
                                                    Options: <?php echo htmlspecialchars($question['options']); ?>
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="question-actions">
                                                <button type="button" class="btn btn-info btn-xs waves-effect waves-light edit-question-btn"
                                                       data-question-id="<?php echo $question['question_id']; ?>"
                                                       data-question-text="<?php echo htmlspecialchars($question['question_text']); ?>"
                                                       data-question-type="<?php echo $question['question_type']; ?>"
                                                       data-options='<?php echo htmlspecialchars($question['options'] ?? ''); ?>'
                                                       data-category-id="<?php echo $category['category_id']; ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-xs waves-effect waves-light delete-question-btn"
                                                        data-question-id="<?php echo $question['question_id']; ?>">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($questions) === 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No questions for this section yet. Click the "+" button to add questions.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (count($categories) === 0): ?>
            <div class="col-xs-12">
                <div class="alert alert-info">
                    No sections found. Add your first section using the form above.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>