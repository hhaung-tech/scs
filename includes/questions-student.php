<?php
// This file should only be included for AJAX requests
if (!isset($pdo)) {
    die('Direct access not allowed');
}

// Grade level mapping
$gradeLevelMap = [
    'ES' => 'elementary',
    'MS' => 'middle',
    'HS' => 'high'
];

// Reverse mapping for database to URL values
$reverseGradeLevelMap = [
    'elementary' => 'ES',
    'middle' => 'MS',
    'high' => 'HS'
];

try {
    header('Content-Type: application/json');
    $response = ['success' => true, 'message' => ''];
    
    if (!isset($_POST['action'])) {
        throw new Exception("Action is required");
    }

    switch ($_POST['action']) {
        case 'add_category':
            if (empty($_POST['category_name'])) {
                throw new Exception("Category name cannot be empty");
            }
            
            // Check if category name already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND type = 'student'");
            $checkStmt->execute([$_POST['category_name']]);
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("A category with this name already exists. Please choose a different name.");
            }
            
            // Add the category
            $stmt = $pdo->prepare("INSERT INTO categories (name, type) VALUES (?, 'student')");
            if (!$stmt->execute([$_POST['category_name']])) {
                $errorInfo = $stmt->errorInfo();
                error_log("Category creation failed: " . print_r($errorInfo, true));
                throw new Exception("Failed to add category: " . $errorInfo[2]);
            }
            $response['message'] = "Category added successfully!";
            $response['category_id'] = $pdo->lastInsertId();
            error_log("Category created successfully with ID: " . $response['category_id']);
            break;

        case 'edit_category':
            if (empty($_POST['category_name']) || empty($_POST['category_id'])) {
                throw new Exception("Category name and ID are required");
            }
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ? AND type = 'student'");
            $stmt->execute([$_POST['category_name'], $_POST['category_id']]);
            $response['message'] = "Category updated successfully!";
            break;

        case 'delete_category':
            if (empty($_POST['category_id'])) {
                throw new Exception("Category ID is required");
            }
            $pdo->beginTransaction();
            try {
                // Delete responses for questions in this category
                $stmt = $pdo->prepare("DELETE FROM responses WHERE question_id IN (SELECT id FROM questions WHERE category_id = ?)");
                $stmt->execute([$_POST['category_id']]);
                
                // Delete questions in this category
                $stmt = $pdo->prepare("DELETE FROM questions WHERE category_id = ?");
                $stmt->execute([$_POST['category_id']]);
                
                // Delete the category
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND type = 'student'");
                $stmt->execute([$_POST['category_id']]);
                
                $pdo->commit();
                $response['message'] = "Category and related data deleted successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'add_question':
            if (empty($_POST['category_id'])) {
                throw new Exception("Category ID is required");
            }
            if (empty($_POST['question_text'])) {
                throw new Exception("Question text is required");
            }
            if (empty($_POST['grade_level'])) {
                throw new Exception("Grade level is required");
            }
            if (empty($_POST['feedback_type'])) {
                throw new Exception("Feedback type is required");
            }

            // Validate grade level - only accept ES, MS, HS
            $validGradeLevels = ['ES', 'MS', 'HS'];
            if (!in_array($_POST['grade_level'], $validGradeLevels)) {
                throw new Exception("Invalid grade level: " . $_POST['grade_level']);
            }

            // Use grade level as is - no mapping needed
            $gradeLevel = $_POST['grade_level'];

            // Validate feedback type
            if (!in_array($_POST['feedback_type'], ['core', 'teacher'])) {
                throw new Exception("Invalid feedback type");
            }

            // Handle options for different question types
            $options = null;
            if ($_POST['question_type'] === 'likert_scale') {
                if (isset($_POST['likert_preset'])) {
                    switch ($_POST['likert_preset']) {
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
                                $customOptions = array_filter($_POST['custom_scale'], function($value) {
                                    return trim($value) !== '';
                                });
                                if (count($customOptions) === 5) {
                                    $options = json_encode(array_values($customOptions));
                                } else {
                                    throw new Exception("Please fill all custom scale options");
                                }
                            }
                            break;
                    }
                }
            } elseif (in_array($_POST['question_type'], ['drop_down', 'checkbox'])) {
                if (!empty($_POST['options'])) {
                    $optionsArray = array_map('trim', explode(',', $_POST['options']));
                    $options = json_encode($optionsArray);
                }
            }

            // Debug info
            error_log("Adding question with grade level: " . $gradeLevel);

            // Add the question
            $stmt = $pdo->prepare("
                INSERT INTO questions (
                    category_id, question_text, question_type, options,
                    grade_level, feedback_type, sort_order
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, 
                    COALESCE((SELECT MAX(sort_order) + 1 FROM questions WHERE category_id = ?), 1)
                )
            ");
            
            if (!$stmt->execute([
                $_POST['category_id'],
                $_POST['question_text'],
                $_POST['question_type'],
                $options,
                $gradeLevel,
                $_POST['feedback_type'],
                $_POST['category_id']
            ])) {
                throw new Exception("Failed to add question");
            }
            
            $response['message'] = "Question added successfully!";
            $response['question_id'] = $pdo->lastInsertId();
            break;

        case 'edit_question':
            if (empty($_POST['question_text']) || empty($_POST['question_id'])) {
                throw new Exception("Question text and ID are required");
            }

            $pdo->beginTransaction();
            try {
                $options = null;
                if ($_POST['question_type'] === 'likert_scale') {
                    switch ($_POST['likert_preset']) {
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
                                $customOptions = array_filter($_POST['custom_scale'], function($value) {
                                    return trim($value) !== '';
                                });
                                if (count($customOptions) === 5) {
                                    $options = json_encode(array_values($customOptions));
                                } else {
                                    throw new Exception("Please fill all custom scale options");
                                }
                            }
                            break;
                    }
                } elseif (in_array($_POST['question_type'], ['drop_down', 'checkbox'])) {
                    if (!empty($_POST['options'])) {
                        $optionsArray = array_map('trim', explode(',', $_POST['options']));
                        $options = json_encode($optionsArray);
                    } else {
                        throw new Exception("Options are required for this question type");
                    }
                }

                $stmt = $pdo->prepare("
                    UPDATE questions 
                    SET question_text = ?, 
                        question_type = ?, 
                        options = ?,
                        grade_level = ?,
                        feedback_type = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $_POST['question_text'],
                    $_POST['question_type'],
                    $options,
                    $_POST['grade_level'],
                    $_POST['feedback_type'],
                    $_POST['question_id']
                ]);
                
                $pdo->commit();
                $response['message'] = "Question updated successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'delete_question':
            if (empty($_POST['question_id'])) {
                throw new Exception("Question ID is required");
            }

            $pdo->beginTransaction();
            try {
                // Delete responses for this question
                $stmt = $pdo->prepare("DELETE FROM responses WHERE question_id = ?");
                $stmt->execute([$_POST['question_id']]);
                
                // Delete the question
                $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                $stmt->execute([$_POST['question_id']]);
                
                $pdo->commit();
                $response['message'] = "Question deleted successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'update_question_order':
            if (!isset($_POST['question_ids']) || !is_array($_POST['question_ids'])) {
                throw new Exception("Invalid question order data");
            }
            if (empty($_POST['category_id'])) {
                throw new Exception("Category ID is required for sorting");
            }

            $questionIds = $_POST['question_ids'];
            $categoryId = $_POST['category_id'];
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE questions SET sort_order = ? WHERE id = ? AND category_id = ?");
                foreach ($questionIds as $index => $questionId) {
                    // Sort order starts from 1
                    $sortOrder = $index + 1;
                    // Ensure questionId is numeric to prevent injection
                    if (!is_numeric($questionId)) {
                        throw new Exception("Invalid question ID found in order");
                    }
                    $stmt->execute([$sortOrder, $questionId, $categoryId]);
                }
                $pdo->commit();
                $response['message'] = "Question order updated successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                // Log the detailed error for debugging
                error_log("Error updating question order: " . $e->getMessage()); 
                throw new Exception("Failed to update question order. " . $e->getMessage());
            }
            break;

        default:
            throw new Exception("Invalid action");
    }
    
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Question</h4>
            </div>
            <div class="modal-body">
                <form id="addQuestionForm">
                    <input type="hidden" name="action" value="add_question">
                    <input type="hidden" name="category_id" id="questionCategoryId">
                    <input type="hidden" name="grade_level" value="<?php echo htmlspecialchars($currentGradeLevel); ?>">
                    <input type="hidden" name="feedback_type" value="<?php echo htmlspecialchars($currentQuestionType); ?>">
                    
                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea name="question_text" class="form-control" required></textarea>
                    </div>
                    
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
                    
                    <div class="form-group likert-options" style="display: none;">
                        <label>Likert Scale Type</label>
                        <select name="likert_preset" class="form-control">
                            <option value="agreement">Agreement (Strongly Disagree - Strongly Agree)</option>
                            <option value="numeric">Numeric (1-5)</option>
                            <option value="frequency">Frequency (Never - Always)</option>
                            <option value="custom">Custom Scale</option>
                        </select>
                        <div id="custom_likert_options" style="display: none;" class="mt-3">
                            <label>Custom Scale Options</label>
                            <div class="row">
                                <div class="col">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 1">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 2">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 3">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 4">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group options-group" style="display: none;">
                        <label>Options</label>
                        <input type="text" name="options" class="form-control" placeholder="Option 1, Option 2, Option 3">
                        <small class="text-muted">Separate options with commas</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveQuestion">Save Question</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Category</h4>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <input type="hidden" name="action" value="add_category">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCategory">Save Category</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Category</h4>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="category_id">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateCategory">Update Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Question</h4>
            </div>
            <div class="modal-body">
                <form id="editQuestionForm">
                    <input type="hidden" name="action" value="edit_question">
                    <input type="hidden" name="question_id">
                    <input type="hidden" name="category_id">
                    <input type="hidden" name="grade_level" value="<?php echo htmlspecialchars($currentGradeLevel); ?>">
                    <input type="hidden" name="feedback_type" value="<?php echo htmlspecialchars($currentQuestionType); ?>">
                    
                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea name="question_text" class="form-control" required></textarea>
                    </div>
                    
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
                    
                    <div class="form-group likert-options" style="display: none;">
                        <label>Likert Scale Type</label>
                        <select name="likert_preset" class="form-control">
                            <option value="agreement">Agreement (Strongly Disagree - Strongly Agree)</option>
                            <option value="numeric">Numeric (1-5)</option>
                            <option value="frequency">Frequency (Never - Always)</option>
                            <option value="custom">Custom Scale</option>
                        </select>
                        <div id="edit_custom_likert_options" style="display: none;" class="mt-3">
                            <label>Custom Scale Options</label>
                            <div class="row">
                                <div class="col">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 1">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 2">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 3">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 4">
                                    <input type="text" name="custom_scale[]" class="form-control mb-2" placeholder="Option 5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group options-group" style="display: none;">
                        <label>Options</label>
                        <input type="text" name="options" class="form-control" placeholder="Option 1, Option 2, Option 3">
                        <small class="text-muted">Separate options with commas</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateQuestion">Update Question</button>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Handle question type selection
    $('.question-type-select').change(function() {
        var selectedType = $(this).val();
        $('.likert-options, .options-group').hide();
        
        if (selectedType === 'likert_scale') {
            $('.likert-options').show();
        } else if (selectedType === 'drop_down' || selectedType === 'checkbox') {
            $('.options-group').show();
        }
    });

    // Handle likert preset selection
    $('select[name="likert_preset"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#custom_likert_options').show();
        } else {
            $('#custom_likert_options').hide();
        }
    });

   // Add Category
$('#saveCategory').click(function() {
    var form = $('#addCategoryForm');
    if (!form.find('[name="category_name"]').val().trim()) {
        alert('Please enter category name');
        return;
    }

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: {
            action: 'add_category',
            category_name: form.find('[name="category_name"]').val().trim()
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
            console.error('Error:', xhr.responseText);
            alert('Error saving category. Please try again.');
        }
    });
});

// Delete Category
$('.delete-category-btn').click(function() {
    if (confirm('Are you sure you want to delete this category and all its questions?')) {
        var categoryId = $(this).data('category-id');
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
                console.error('Error:', xhr.responseText);
                alert('Error deleting category. Please try again.');
            }
        });
    }
});

// Add Question
$('#saveQuestion').click(function() {
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

    var formData = {
        action: 'add_question',
        category_id: form.find('[name="category_id"]').val(),
        question_text: form.find('[name="question_text"]').val().trim(),
        question_type: questionType,
        grade_level: form.find('[name="grade_level"]').val(),
        feedback_type: form.find('[name="feedback_type"]').val()
    };

    if (questionType === 'likert_scale') {
        formData.likert_preset = form.find('[name="likert_preset"]').val();
        if (formData.likert_preset === 'custom') {
            var customOptions = [];
            var hasEmptyFields = false;
            form.find('[name="custom_scale[]"]').each(function() {
                var value = $(this).val().trim();
                if (!value) hasEmptyFields = true;
                customOptions.push(value);
            });
            if (hasEmptyFields) {
                alert('Please fill all custom scale options');
                return;
            }
            formData.custom_scale = customOptions;
        }
    } else if (questionType === 'drop_down' || questionType === 'checkbox') {
        var options = form.find('[name="options"]').val().trim();
        if (!options) {
            alert('Please enter options');
            return;
        }
        formData.options = options;
    }

    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addQuestionModal').modal('hide');
                location.reload();
            } else {
                alert(response.message || 'Error saving question');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', xhr.responseText);
            alert('Error saving question. Please try again.');
        }
    });
});

// Delete Question
$('.delete-question-btn').click(function() {
    if (confirm('Are you sure you want to delete this question?')) {
        var questionId = $(this).closest('.question-item').data('id');
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
                console.error('Error:', xhr.responseText);
                alert('Error deleting question. Please try again.');
            }
        });
    }
});
// Edit Category
$('.edit-category-btn').click(function() {
    var categoryId = $(this).data('category-id');
    var categoryName = $(this).closest('.category-item').find('.category-name').text();
    
    var form = $('#editCategoryForm');
    form.find('[name="category_id"]').val(categoryId);
    form.find('[name="category_name"]').val(categoryName);
    
    $('#editCategoryModal').modal('show');
});

$('#updateCategory').click(function() {
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
            console.error('Error:', xhr.responseText);
            alert('Error updating category. Please try again.');
        }
    });
});

// Edit Question
$('.edit-question-btn').click(function() {
    var questionItem = $(this).closest('.question-item');
    var questionId = questionItem.data('id');
    var questionText = questionItem.find('.question-text').text();
    var questionType = questionItem.data('type');
    var options = questionItem.data('options');
    
    var form = $('#editQuestionForm');
    form.find('[name="question_id"]').val(questionId);
    form.find('[name="question_text"]').val(questionText);
    form.find('[name="question_type"]').val(questionType).trigger('change');
    
    if (questionType === 'likert_scale') {
        if (options) {
            var optionsArray = JSON.parse(options);
            // Determine if it's a custom scale
            var isCustom = true;
            var presetOptions = {
                agreement: ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'],
                numeric: ['1', '2', '3', '4', '5'],
                frequency: ['Never', 'Rarely', 'Sometimes', 'Often', 'Always']
            };
            
            for (var preset in presetOptions) {
                if (JSON.stringify(optionsArray) === JSON.stringify(presetOptions[preset])) {
                    form.find('[name="likert_preset"]').val(preset).trigger('change');
                    isCustom = false;
                    break;
                }
            }
            
            if (isCustom) {
                form.find('[name="likert_preset"]').val('custom').trigger('change');
                form.find('[name="custom_scale[]"]').each(function(index) {
                    $(this).val(optionsArray[index] || '');
                });
            }
        }
    } else if (questionType === 'drop_down' || questionType === 'checkbox') {
        if (options) {
            var optionsArray = JSON.parse(options);
            form.find('[name="options"]').val(optionsArray.join(', '));
        }
    }
    
    $('#editQuestionModal').modal('show');
});

$('#updateQuestion').click(function() {
    var form = $('#editQuestionForm');
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
            console.error('Error:', xhr.responseText);
            alert('Error updating question. Please try again.');
        }
    });
});
});
</script>