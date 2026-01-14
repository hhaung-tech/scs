<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
requireLogin();

// Handle AJAX requests first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        require_once '../../includes/questions-student.php';
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

$currentPage = 'student-questions';
$surveyType = 'Student Survey Questions';

// Set the current grade level from tab (default to ES if not set)
$currentGradeLevel = isset($_GET['grade_level']) ? $_GET['grade_level'] : 'ES';
$validGradeLevels = ['ES', 'MS', 'HS'];
if (!in_array($currentGradeLevel, $validGradeLevels)) {
    $currentGradeLevel = 'ES';
}

// Use the grade level directly without mapping
$dbGradeLevel = $currentGradeLevel;

// Add another variable for current question type (core or teacher)
$currentQuestionType = isset($_GET['feedback_type']) ? $_GET['feedback_type'] : 'core';
if (!in_array($currentQuestionType, ['core', 'teacher'])) {
    $currentQuestionType = 'core';
}

// Get all categories for student type
$categoryStmt = $pdo->prepare("
    SELECT DISTINCT c.* 
    FROM categories c 
    WHERE c.type = 'student'
    ORDER BY c.id DESC
");
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll();

// Get questions for current grade and type
$stmt = $pdo->prepare("
    SELECT 
        q.category_id,
        q.id as question_id,
        q.question_text,
        q.question_type,
        q.options,
        q.grade_level,
        q.feedback_type,
        q.sort_order
    FROM questions q
    INNER JOIN categories c ON q.category_id = c.id
    WHERE c.type = 'student'
    AND q.feedback_type = ?
    AND q.grade_level = ?
    ORDER BY q.sort_order ASC, q.id ASC
");

// MORE DETAILED DEBUGGING
error_log("---- DEBUG: questions.php ----");
error_log("Target Feedback Type (from URL/Default): " . $currentQuestionType);
error_log("Target Grade Level (from URL/Default): " . $dbGradeLevel);
error_log("SQL Parameters being sent: [" . $currentQuestionType . ", " . $dbGradeLevel . "]");

$stmt->execute([$currentQuestionType, $dbGradeLevel]);
$questions = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

error_log("Query Executed. Questions found (grouped by category): " . json_encode(array_keys($questions)));
error_log("-----------------------------");

// Debug info - after executing query
echo "<!-- Debug Info:
Current Grade Level: " . $currentGradeLevel . "
DB Grade Level: " . $dbGradeLevel . "
Current Question Type: " . $currentQuestionType . "
Questions structure keys: " . json_encode(array_keys($questions)) . "
SQL params: " . json_encode([$currentQuestionType, $dbGradeLevel]) . "
-->";
?>
<div class="main-content">
    <div class="row small-spacing">
        <div class="col-xs-12">
            <div class="box-content">
                <h4 class="box-title">Student Survey Questions</h4>
                
                <!-- Grade Level Tabs -->
                <div class="nav-tabs-horizontal primary-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <?php 
                        $gradeLevelLabels = [
                            'ES' => ['Elementary School', '(PK3, PK4, K-5)'],
                            'MS' => ['Middle School', '(Grades 6-9)'],
                            'HS' => ['High School', '(Grades 10-12)']
                        ];
                        foreach ($validGradeLevels as $grade): 
                        ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentGradeLevel === $grade ? 'active' : ''; ?>" 
                                   href="?grade_level=<?php echo $grade; ?>&feedback_type=<?php echo $currentQuestionType; ?>">
                                    <?php echo $gradeLevelLabels[$grade][0]; ?><br>
                                    <small><?php echo $gradeLevelLabels[$grade][1]; ?></small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Question Type Tabs -->
                <div class="nav-tabs-horizontal secondary-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentQuestionType === 'core' ? 'active' : ''; ?>" 
                               href="?grade_level=<?php echo $currentGradeLevel; ?>&feedback_type=core">
                                Core Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentQuestionType === 'teacher' ? 'active' : ''; ?>" 
                               href="?grade_level=<?php echo $currentGradeLevel; ?>&feedback_type=teacher">
                                Teacher Questions
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Questions Content -->
                <div id="questionsContainer" class="tab-content padding-20">
                    <div class="row mb-4">
                        <div class="col">
                            <button type="button" class="btn btn-primary" onclick="showAddCategoryModal()">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </div>
                    </div>

                    <?php if (empty($categories)): ?>
                        <div class="alert alert-info">
                            No categories found. Please add a category first.
                        </div>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <?php 
                            // Check if there are any questions for this category matching the CURRENT filters
                            $categoryQuestions = isset($questions[$category['id']]) ? $questions[$category['id']] : [];
                            ?>
                            <?php // Always show categories, even if empty - this allows adding questions to new categories ?>
                                <div class="category-section" data-category-id="<?php echo $category['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="section-title mb-0"><?php echo htmlspecialchars($category['name']); ?></h4>
                                        <div class="category-actions">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="showAddQuestionModal(<?php echo $category['id']; ?>)">
                                                <i class="fas fa-plus"></i> Add Question
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="showEditCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>

                                    <?php if (!empty($categoryQuestions)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Handle</th>
                                                    <th>Question</th>
                                                    <th>Type</th>
                                                    <th>Options</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="sortable-questions" data-category-id="<?php echo $category['id']; ?>">
                                                <?php foreach ($categoryQuestions as $question): ?>
                                                    <tr data-question-id="<?php echo $question['question_id']; ?>">
                                                        <td class="handle"><i class="fas fa-bars"></i></td>
                                                        <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                                        <td>
                                                            <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $question['question_type']))); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($question['options'])): ?>
                                                                <small><?php echo htmlspecialchars(implode(', ', json_decode($question['options'], true) ?: [])); ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-secondary btn-sm" onclick='showEditQuestionModal(<?php echo json_encode([
                                                                "id" => $question['question_id'],
                                                                "text" => $question['question_text'],
                                                                "type" => $question['question_type'],
                                                                "options" => $question['options'],
                                                                "grade_level" => $question['grade_level'],
                                                                "feedback_type" => $question['feedback_type']
                                                            ]); ?>)'>
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteQuestion(<?php echo $question['question_id']; ?>)">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No questions found for <?php echo htmlspecialchars($category['name']); ?> in <?php echo $currentGradeLevel; ?> <?php echo ucfirst($currentQuestionType); ?> Questions. 
                                        <strong>Click "Add Question" to create the first question.</strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <label for="add_category_name">Category Name</label>
                        <input type="text" class="form-control" id="add_category_name" name="category_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCategory">Save Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="category_id" id="editCategoryId">
                    <div class="form-group">
                        <label for="edit_category_name">Category Name</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditCategory">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog" aria-labelledby="addQuestionModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuestionModalLabel">Add Question</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addQuestionForm">
                    <input type="hidden" name="action" value="add_question">
                    <input type="hidden" name="category_id" id="questionCategoryId">
                    
                    <div class="form-group">
                        <label for="add_question_text">Question Text</label>
                        <textarea class="form-control" id="add_question_text" name="question_text" required rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="add_question_type">Question Type</label>
                        <select class="form-control question-type-select" id="add_question_type" name="question_type" required>
                            <option value="">Select Type</option>
                            <option value="likert_scale">Likert Scale</option>
                            <option value="drop_down">Drop Down</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="text">Text</option>
                        </select>
                    </div>

                    <div class="form-group likert-options" style="display: none;">
                        <label for="add_likert_preset">Likert Scale Type</label>
                        <select class="form-control" id="add_likert_preset" name="likert_preset">
                            <option value="agreement">Agreement (Strongly Disagree to Strongly Agree)</option>
                            <option value="numeric">Numeric (1 to 5)</option>
                            <option value="frequency">Frequency (Never to Always)</option>
                            <option value="custom">Custom Scale</option>
                        </select>
                        
                        <div class="custom-likert-options mt-3" style="display: none;">
                            <label>Custom Scale Options (5 required)</label>
                            <?php for($i = 0; $i < 5; $i++): ?>
                            <input type="text" class="form-control mb-2" id="add_custom_scale_<?php echo $i; ?>" 
                                   name="custom_scale[]" placeholder="Option <?= $i + 1 ?>">
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group options-group" style="display: none;">
                        <label for="add_options">Options (comma-separated)</label>
                        <input type="text" class="form-control" id="add_options" name="options" 
                               placeholder="Option 1, Option 2, Option 3">
                        <small class="form-text text-muted">Enter options separated by commas</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveQuestion">Save Question</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog" aria-labelledby="editQuestionModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editQuestionForm">
                    <input type="hidden" name="action" value="edit_question">
                    <input type="hidden" name="question_id" id="editQuestionId">
                    
                    <div class="form-group">
                        <label for="edit_question_text">Question Text</label>
                        <input type="text" class="form-control" id="edit_question_text" name="question_text" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_question_type">Question Type</label>
                        <select class="form-control question-type-select" id="edit_question_type" name="question_type" required>
                            <option value="likert_scale">Likert Scale</option>
                            <option value="drop_down">Drop Down</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="text">Text</option>
                        </select>
                    </div>

                    <div class="form-group likert-options">
                        <label for="edit_likert_preset">Likert Scale Type</label>
                        <select class="form-control" id="edit_likert_preset" name="likert_preset">
                            <option value="agreement">Agreement (Strongly Disagree to Strongly Agree)</option>
                            <option value="numeric">Numeric (1 to 5)</option>
                            <option value="frequency">Frequency (Never to Always)</option>
                            <option value="custom">Custom Scale</option>
                        </select>
                        
                        <div class="custom-likert-options mt-3" style="display: none;">
                            <label>Custom Scale Options (5 required)</label>
                            <?php for($i = 0; $i < 5; $i++): ?>
                            <input type="text" class="form-control mb-2" id="edit_custom_scale_<?php echo $i; ?>" 
                                   name="custom_scale[]" placeholder="Option <?= $i + 1 ?>">
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group options-group" style="display: none;">
                        <label for="edit_options">Options (comma-separated)</label>
                        <input type="text" class="form-control" id="edit_options" name="options" 
                               placeholder="Option 1, Option 2, Option 3">
                        <small class="form-text text-muted">Enter options separated by commas</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditQuestion">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
// Remove grade level mapping since we're using ES, MS, HS directly
function showAddCategoryModal() {
    $('#addCategoryForm')[0].reset();
    $('#addCategoryModal').modal('show');
}

function showEditCategoryModal(categoryId, categoryName) {
    $('#editCategoryForm')[0].reset();
    $('#editCategoryId').val(categoryId);
    $('#edit_category_name').val(categoryName);
    $('#editCategoryModal').modal('show');
}

function showAddQuestionModal(categoryId) {
    $('#addQuestionForm')[0].reset();
    $('#questionCategoryId').val(categoryId);
    $('#addQuestionModal').modal('show');
}

function showEditQuestionModal(questionData) {
    $('#editQuestionForm')[0].reset();
    $('#editQuestionId').val(questionData.id);
    $('#edit_question_text').val(questionData.text);
    $('#edit_question_type').val(questionData.type).trigger('change');
    
    // Add hidden fields for grade level and feedback type
    if (!$('#edit_grade_level').length) {
        $('#editQuestionForm').append(`
            <input type="hidden" name="grade_level" id="edit_grade_level" value="${questionData.grade_level}">
            <input type="hidden" name="feedback_type" id="edit_feedback_type" value="${questionData.feedback_type}">
        `);
    } else {
        $('#edit_grade_level').val(questionData.grade_level);
        $('#edit_feedback_type').val(questionData.feedback_type);
    }
    
    if (questionData.type === 'likert_scale' && questionData.options) {
        let options = JSON.parse(questionData.options);
        // Try to detect preset
        if (JSON.stringify(options) === JSON.stringify(['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'])) {
            $('#edit_likert_preset').val('agreement');
        } else if (JSON.stringify(options) === JSON.stringify(['1', '2', '3', '4', '5'])) {
            $('#edit_likert_preset').val('numeric');
        } else if (JSON.stringify(options) === JSON.stringify(['Never', 'Rarely', 'Sometimes', 'Often', 'Always'])) {
            $('#edit_likert_preset').val('frequency');
        } else {
            $('#edit_likert_preset').val('custom');
            $('#edit_custom_likert_options').show();
            options.forEach((option, index) => {
                $(`#editQuestionForm [name="custom_scale[]"]`).eq(index).val(option);
            });
        }
    } else if ((questionData.type === 'drop_down' || questionData.type === 'checkbox') && questionData.options) {
        let options = JSON.parse(questionData.options);
        $('#edit_options').val(options.join(', '));
    }
    
    $('#editQuestionModal').modal('show');
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category? This will also delete all questions in this category.')) {
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'delete_category',
                category_id: categoryId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Error deleting category');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error deleting category: ' + error);
            }
        });
    }
}

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question?')) {
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'delete_question',
                question_id: questionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Error deleting question');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error deleting question: ' + error);
            }
        });
    }
}

$(document).ready(function() {
    // Fix modal focus management
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('[autofocus]').focus();
    });

    // Ensure modals are properly cleaned up when hidden
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('.custom-likert-options, .options-group').hide();
    });

    // Add autofocus to the first input in modals
    $('#addQuestionModal, #editQuestionModal').each(function() {
        $(this).find('input:text, textarea').first().attr('autofocus', true);
    });

    // Add Category
    $('#saveCategory').click(function(e) {
        e.preventDefault();
        var form = $('#addCategoryForm');
        var categoryName = form.find('[name="category_name"]').val().trim();
        
        if (!categoryName) {
            alert('Please enter category name');
            return;
        }

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'add_category',
                category_name: categoryName
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#addCategoryModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error saving category');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error saving category: ' + error);
            }
        });
    });

    // Edit Category
    $('#saveEditCategory').click(function(e) {
        e.preventDefault();
        var form = $('#editCategoryForm');
        if (!form.find('[name="category_name"]').val().trim()) {
            alert('Please enter category name');
            return;
        }

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editCategoryModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error updating category');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error updating category: ' + error);
            }
        });
    });

    // Add Question
    $('#saveQuestion').click(function(e) {
        e.preventDefault();
        var form = $('#addQuestionForm');
        var questionType = form.find('[name="question_type"]').val();

        if (!form.find('[name="question_text"]').val().trim()) {
            alert('Please enter question text');
            return;
        }

        if (!questionType) {
            alert('Please select a question type');
            return;
        }

        var formData = new FormData(form[0]);
        formData.append('grade_level', '<?php echo $dbGradeLevel; ?>');
        formData.append('feedback_type', '<?php echo $currentQuestionType; ?>');

        if (questionType === 'likert_scale') {
            var preset = form.find('[name="likert_preset"]').val();
            if (preset === 'custom') {
                var hasEmptyFields = false;
                form.find('[name="custom_scale[]"]').each(function() {
                    if (!$(this).val().trim()) hasEmptyFields = true;
                });
                if (hasEmptyFields) {
                    alert('Please fill all custom scale options');
                    return;
                }
            }
        } else if ((questionType === 'drop_down' || questionType === 'checkbox') && !form.find('[name="options"]').val().trim()) {
            alert('Please enter options');
            return;
        }

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addQuestionModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error saving question');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error saving question: ' + error);
            }
        });
    });

    // Edit Question
    $('#saveEditQuestion').click(function() {
        var form = $('#editQuestionForm');
        var questionType = form.find('[name="question_type"]').val();
        
        // Validate form data
        if (!form.find('[name="question_text"]').val().trim()) {
            alert('Please enter question text');
            return;
        }

        if (questionType === 'likert_scale') {
            var preset = form.find('[name="likert_preset"]').val();
            if (preset === 'custom') {
                var customOptions = [];
                var hasEmptyFields = false;
                form.find('[name="custom_scale[]"]').each(function() {
                    var value = $(this).val().trim();
                    if (!value) {
                        hasEmptyFields = true;
                    }
                    customOptions.push(value);
                });
                if (hasEmptyFields) {
                    alert('Please fill all custom scale options');
                    return;
                }
            }
        } else if ((questionType === 'drop_down' || questionType === 'checkbox') && !form.find('[name="options"]').val().trim()) {
            alert('Please enter options');
            return;
        }

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#editQuestionModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error updating question');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error updating question: ' + error);
            }
        });
    });

    // Handle question type selection
    $('.question-type-select').change(function() {
        var selectedType = $(this).val();
        var form = $(this).closest('form');
        form.find('.likert-options, .options-group').hide();
        
        if (selectedType === 'likert_scale') {
            form.find('.likert-options').show();
        } else if (selectedType === 'drop_down' || selectedType === 'checkbox') {
            form.find('.options-group').show();
        }
    });

    // Handle likert preset selection
    $('select[name="likert_preset"]').change(function() {
        var form = $(this).closest('form');
        if ($(this).val() === 'custom') {
            form.find('.custom-likert-options').show();
        } else {
            form.find('.custom-likert-options').hide();
        }
    });

    // Initialize Sortable Questions
    $(".sortable-questions").sortable({
        handle: ".handle", // Specify the drag handle element
        axis: "y", // Allow dragging only vertically
        update: function(event, ui) {
            var categoryId = $(this).data('category-id');
            var questionOrder = $(this).sortable("toArray", { attribute: "data-question-id" });
            
            // Send AJAX request to update order
            $.ajax({
                url: window.location.href,
                type: "POST",
                data: {
                    action: "update_question_order",
                    category_id: categoryId,
                    question_ids: questionOrder // Send array of question IDs
                },
                dataType: "json",
                success: function(response) {
                    if (!response.success) {
                        alert("Error updating question order: " + (response.message || 'Unknown error'));
                        // Optionally revert the sortable list
                        $(ui.sender).sortable('cancel'); 
                    }
                    // No reload on success, optimistic update
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("Failed to send order update to server.");
                    $(ui.sender).sortable('cancel'); // Revert on error
                }
            });
        }
    }).disableSelection(); // Prevent text selection while dragging

});
</script>

<?php require_once '../../includes/footer.php'; ?>

<style>
.nav-tabs-horizontal {
    margin-bottom: 20px;
}
.nav-tabs {
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
}
.nav-item {
    margin-bottom: -1px;
}
.nav-link {
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    color: #555;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    margin-right: 5px;
    border-radius: 4px 4px 0 0;
    transition: all 0.3s ease;
}
.nav-link:hover {
    background: #fff;
    border-bottom-color: transparent;
    color: #2196F3;
    text-decoration: none;
}
.nav-link.active {
    background: #2196F3;
    color: #fff;
    border-color: #2196F3;
    border-bottom-color: transparent;
}
.nav-link.active small {
    color: rgba(255, 255, 255, 0.8);
}
.nav-link small {
    display: block;
    font-size: 12px;
    color: #777;
    margin-top: 4px;
}
.tab-content {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: none;
    padding: 20px;
    border-radius: 0 0 4px 4px;
    margin-top: -1px;
}
.question-item {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}
.question-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}
.category-section {
    margin-bottom: 30px;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
}
.section-title {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #2196F3;
    color: #333;
}
.questions-list {
    padding-left: 20px;
}
.options {
    color: #666;
    margin-top: 5px;
}
.action-buttons {
margin-top: 20px;
padding: 15px;
border-top: 1px solid #eee;
}
.action-buttons button {
margin-right: 10px;
}

/* Add styles for modal focus management */
.modal.fade.in:focus {
    outline: none;
}

.modal-dialog:focus {
    outline: none;
}

.handle {
    cursor: move;
    padding: 0 10px;
    color: #ccc;
}
.ui-sortable-helper {
    background-color: #f0f0f0; /* Style for the row being dragged */
    border: 1px dashed #ccc;
}
</style>