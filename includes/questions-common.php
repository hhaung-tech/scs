<?php
$currentPath = $_SERVER['PHP_SELF'];
$surveyType = '';
if (strpos($currentPath, '/admin/guardian/') !== false) {
    $surveyType = 'Guardian Survey Questions';
} elseif (strpos($currentPath, '/admin/student/') !== false) {
    $surveyType = 'Student Survey Questions';
} elseif (strpos($currentPath, '/admin/staff/') !== false) {
    $surveyType = 'Staff Survey Questions';
}elseif (strpos($currentPath, '/admin/alumni/') !== false) {
    $surveyType = 'Alumni Survey Questions';
}elseif (strpos($currentPath, '/admin/board/') !== false) {
    $surveyType = 'Board Survey Questions';
}

// Get survey type from URL
$type = '';
if (strpos($currentPath, '/admin/guardian/') !== false) {
    $type = 'guardian';
} elseif (strpos($currentPath, '/admin/staff/') !== false) {
    $type = 'staff';
} elseif (strpos($currentPath, '/admin/student/') !== false) {
    $type = 'student';
} elseif (strpos($currentPath, '/admin/alumni/') !== false) {  
    $type = 'alumni';
}elseif (strpos($currentPath, '/admin/board/') !== false) {
    $type = 'board';
}

// Get grade level and feedback type from URL parameters
$currentGradeLevel = isset($_GET['grade_level']) ? $_GET['grade_level'] : 'ES';
$validGradeLevels = ['ES', 'MS', 'HS'];
if (!in_array($currentGradeLevel, $validGradeLevels)) {
    $currentGradeLevel = 'ES';
}

$currentQuestionType = isset($_GET['feedback_type']) ? $_GET['feedback_type'] : 'core';
if (!in_array($currentQuestionType, ['core', 'teacher'])) {
    $currentQuestionType = 'core';
}

// Fetch categories and questions (only if not already fetched)
if (!isset($results)) {
    $stmt = $pdo->prepare("
        SELECT c.id as category_id, c.name as category_name, 
               q.id as question_id, q.question_text, q.question_type, q.options, q.sort_order
        FROM categories c
        LEFT JOIN questions q ON c.id = q.category_id
        WHERE c.type = ?
        ORDER BY c.id ASC, q.sort_order ASC, q.id ASC
    ");
    $stmt->execute([$type]);
    $results = $stmt->fetchAll();
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            $response = ['success' => true, 'message' => ''];
            
            switch ($_POST['action']) {
                case 'add_category':
                    if (empty($_POST['category_name'])) {
                        $error = "Category name cannot be empty";
                    } else {
                        $currentPath = $_SERVER['PHP_SELF'];
                        $type = '';
                        if (strpos($currentPath, '/admin/guardian/') !== false) {
                            $type = 'guardian';
                        } elseif (strpos($currentPath, '/admin/staff/') !== false) {
                            $type = 'staff';  
                        } elseif (strpos($currentPath, '/admin/student/') !== false) {
                            $type = 'student';
                        }elseif (strpos($currentPath, '/admin/alumni/') !== false) {
                            $type = 'alumni';
                        }elseif (strpos($currentPath, '/admin/board/') !== false) {
    				    $type = 'board';
			            }
                        $stmt = $pdo->prepare("INSERT INTO categories (name, type) VALUES (?, ?)");
                        try {
                            $stmt->execute([$_POST['category_name'], $type]);
                            $message = "Category added successfully!";
                            // Redirect to prevent form resubmission
                            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                            exit;
                        } catch (PDOException $e) {
                            error_log("Full error: " . $e->getMessage());
                            $error = "Database error: " . $e->getMessage();
                        }
                    }
                break;

                case 'edit_category':
                    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                    $stmt->execute([$_POST['category_name'], $_POST['category_id']]);
                    $response['message'] = "Category updated successfully!";
                    break;

                case 'delete_category':
                    $pdo->beginTransaction();
                    try {
                        // Delete responses for all questions in this category
                        $stmt = $pdo->prepare("DELETE FROM responses WHERE question_id IN (SELECT id FROM questions WHERE category_id = ?)");
                        $stmt->execute([$_POST['category_id']]);
                        
                        // Delete questions
                        $stmt = $pdo->prepare("DELETE FROM questions WHERE category_id = ?");
                        $stmt->execute([$_POST['category_id']]);
                        
                        // Delete category
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$_POST['category_id']]);
                        
                        $pdo->commit();
                        $response['message'] = "Category and all related data deleted successfully!";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
                    break;

                    case 'add_question':
                    case 'edit_question':
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

                    if ($_POST['action'] === 'add_question') {
                        $stmt = $pdo->prepare("INSERT INTO questions (category_id, question_text, question_type, options, grade_level, feedback_type) VALUES (?, ?, ?, ?, ?, ?)");
                        try {
                            $stmt->execute([
                                $_POST['category_id'], 
                                $_POST['question_text'], 
                                $questionType, 
                                $options,
                                $_POST['grade_level'],
                                $_POST['feedback_type']
                            ]);
                            $response = ['success' => true, 'message' => "Question added successfully!"];
                        } catch (PDOException $e) {
                            $response = ['success' => false, 'message' => "Database error: " . $e->getMessage()];
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, question_type = ?, options = ?, grade_level = ?, feedback_type = ? WHERE id = ?");
                        try {
                            $stmt->execute([
                                $_POST['question_text'], 
                                $questionType, 
                                $options, 
                                $_POST['grade_level'],
                                $_POST['feedback_type'],
                                $_POST['question_id']
                            ]);
                            $response = ['success' => true, 'message' => "Question updated successfully!"];
                        } catch (PDOException $e) {
                            $response = ['success' => false, 'message' => "Database error: " . $e->getMessage()];
                        }
                    }

                    // Always return JSON for AJAX requests
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    }
                    break;

                case 'delete_question':
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare("DELETE FROM responses WHERE question_id = ?");
                        $stmt->execute([$_POST['question_id']]);
                        
                        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                        $stmt->execute([$_POST['question_id']]);
                        
                        $pdo->commit();
                        $response['message'] = "Question deleted successfully!";
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
                    break;

                case 'reorder_questions':
                    $orders = json_decode($_POST['orders'], true);
                    foreach ($orders as $id => $order) {
                        $stmt = $pdo->prepare("UPDATE questions SET sort_order = ? WHERE id = ?");
                        $stmt->execute([$order, $id]);
                    }
                    exit('Success');
                    break;
            }

            $message = $response['message'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;

        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
    }
}

// Organize questions by category
$categories = [];

// First, get all categories to ensure we don't miss any
$categoryOnlyStmt = $pdo->prepare("SELECT id, name FROM categories WHERE type = ? ORDER BY id ASC");
$categoryOnlyStmt->execute([$type]);
$allCategories = $categoryOnlyStmt->fetchAll();

// Initialize all categories
foreach ($allCategories as $cat) {
    $categories[$cat['id']] = [
        'name' => $cat['name'],
        'questions' => []
    ];
}

// Now add questions to their respective categories
foreach ($results as $row) {
    $categoryId = $row['category_id'];
    
    // Ensure category exists (in case of data inconsistency)
    if (!isset($categories[$categoryId])) {
        $categories[$categoryId] = [
            'name' => $row['category_name'],
            'questions' => []
        ];
    }
    
    // Add question if it exists
    if ($row['question_id']) {
        $categories[$categoryId]['questions'][] = [
            'id' => $row['question_id'],
            'text' => $row['question_text'],
            'type' => $row['question_type'],
            'options' => $row['options'],
            'sort_order' => $row['sort_order'] ?? 0
        ];
    }
}

// Sort questions within each category by sort_order
foreach ($categories as $categoryId => $categoryData) {
    usort($categories[$categoryId]['questions'], function($a, $b) {
        return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
    });
}


require_once '../../includes/header.php';
require_once '../../includes/sidebar-dynamic.php';
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

        <!-- Section Management -->
        <div class="col-xs-12">
            <div class="box-content card white">
                <h4 class="box-title"><?php echo htmlspecialchars($surveyType); ?></h4>
                <div class="card-content">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        Manage sections and questions using the <strong>Question Manager</strong>.
                        <a href="/isy_scs_ai/admin/manage-questions-modern.php" class="btn btn-sm btn-primary pull-right">
                            <i class="fa fa-list"></i> Open Question Manager
                        </a>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Categories and Questions -->
        <?php if (count($categories) === 0): ?>
        <div class="col-xs-12">
            <div class="box-content card white">
                <div class="card-content text-center" style="padding: 40px;">
                    <i class="fa fa-folder-open" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h4>No sections created yet</h4>
                    <p class="text-muted">Use the form above to create your first section for this survey.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($categories as $categoryId => $category): ?>
        <div class="col-xs-12">
            <div class="box-content card white category-section">
                <div class="card-content">
                <div class="category-header">
                        <div class="category-title d-flex align-items-center">
                            <div class="title-content">
                                <h4 class="box-title mb-0"><?php echo htmlspecialchars($category['name']); ?></h4>
                            </div>
                            <form method="POST" class="edit-category-form w-100" style="display: none;">
                                <input type="hidden" name="action" value="edit_category">
                                <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-0">
                                            <input type="text" name="category_name" class="form-control" 
                                                   value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="btn-group">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                <i class="fa fa-check"></i> Save
                                            </button>
                                            <button type="button" class="btn btn-default waves-effect waves-light cancel-edit-category">
                                                <i class="fa fa-times"></i> Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm toggle-question-form">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-info waves-effect waves-light btn-sm edit-category-btn">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-danger waves-effect waves-light btn-sm delete-category-btn" 
                                    data-category-id="<?php echo $categoryId; ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Question Form -->
                    <div class="question-form-container margin-top-20" style="display: none;">
                        <form method="POST" class="question-form">
                            <input type="hidden" name="action" value="add_question">
                            <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                            <input type="hidden" name="grade_level" value="<?php echo htmlspecialchars($currentGradeLevel); ?>">
                            <input type="hidden" name="feedback_type" value="<?php echo htmlspecialchars($currentQuestionType); ?>">
                            
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
                                    <label>Custom Scale Options</label>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 1">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 2">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 3">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 4">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 5">
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
                    <div class="table-responsive question-list margin-top-20 col-xs-12" data-category-id="<?php echo $categoryId; ?>">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>Question</th>
                                    <th style="width: 150px;">Type</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category['questions'] as $question): ?>
                                <tr class="question-item" data-id="<?php echo $question['id']; ?>">
                                    <td>
                                        <div class="handle">
                                            <i class="fa fa-bars"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="question-content">
                                            <div class="question-text">
                                                <?php echo htmlspecialchars($question['text']); ?>
                                            </div>
                                            <?php if ($question['options']): ?>
                                            <small class="text-muted">
                                                Options: <?php echo htmlspecialchars($question['options']); ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success badge-info">
                                            <?php echo ucfirst(str_replace('_', ' ', $question['type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="question-actions">
                                            <button type="button" class="btn btn-info btn-xs waves-effect waves-light edit-question-btn">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-xs waves-effect waves-light delete-question-btn">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="question-preview" style="display: none;"></div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>
</div>

<?php require_once '../../includes/footer.php'; ?>
