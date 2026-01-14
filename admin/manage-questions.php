<?php
// Redirect to new modern Question Manager
$type = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
header('Location: /isy_scs_ai/admin/manage-questions-modern.php' . ($type !== '' ? '?type=' . urlencode($type) : ''));
exit;
?>

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_section':
                $surveyType = $_POST['survey_type'] ?? '';
                $sectionName = trim($_POST['section_name'] ?? '');
                
                if (empty($sectionName) || empty($surveyType)) {
                    throw new Exception("Section name and survey type are required");
                }
                
                $stmt = $pdo->prepare("INSERT INTO categories (name, type) VALUES (?, ?)");
                $stmt->execute([$sectionName, $surveyType]);
                
                $success = "Section '{$sectionName}' created successfully!";
                break;
                
            case 'edit_section':
                $categoryId = $_POST['category_id'] ?? '';
                $sectionName = trim($_POST['section_name'] ?? '');
                
                if (empty($sectionName) || empty($categoryId)) {
                    throw new Exception("Section name and ID are required");
                }
                
                $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                $stmt->execute([$sectionName, $categoryId]);
                
                $success = "Section updated successfully!";
                break;
                
            case 'delete_section':
                $categoryId = $_POST['category_id'] ?? '';
                if (empty($categoryId)) {
                    throw new Exception("Section ID is required");
                }
                
                // Delete questions first, then section
                $pdo->prepare("DELETE FROM questions WHERE category_id = ?")->execute([$categoryId]);
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$categoryId]);
                
                $success = "Section and all its questions deleted successfully!";
                break;
                
            case 'add_question':
                $categoryId = $_POST['category_id'] ?? '';
                $questionText = trim($_POST['question_text'] ?? '');
                $questionType = $_POST['question_type'] ?? '';
                $options = $_POST['options'] ?? '';
                
                if (empty($questionText) || empty($categoryId) || empty($questionType)) {
                    throw new Exception("All fields are required");
                }
                
                $stmt = $pdo->prepare("INSERT INTO questions (category_id, question_text, question_type, options) VALUES (?, ?, ?, ?)");
                $stmt->execute([$categoryId, $questionText, $questionType, $options]);
                
                $success = "Question added successfully!";
                break;
                
            case 'edit_question':
                $questionId = $_POST['question_id'] ?? '';
                $questionText = trim($_POST['question_text'] ?? '');
                $questionType = $_POST['question_type'] ?? '';
                $options = $_POST['options'] ?? '';
                
                if (empty($questionText) || empty($questionId) || empty($questionType)) {
                    throw new Exception("All fields are required");
                }
                
                $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, question_type = ?, options = ? WHERE id = ?");
                $stmt->execute([$questionText, $questionType, $options, $questionId]);
                
                $success = "Question updated successfully!";
                break;
                
            case 'delete_question':
                $questionId = $_POST['question_id'] ?? '';
                if (empty($questionId)) {
                    throw new Exception("Question ID is required");
                }
                
                $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                $stmt->execute([$questionId]);
                
                $success = "Question deleted successfully!";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get survey type from URL parameter
$surveyType = $_GET['type'] ?? 'student';
$validTypes = ['student', 'board', 'staff', 'alumni', 'guardian'];
if (!in_array($surveyType, $validTypes)) {
    $surveyType = 'student';
}

// Get sections and questions for this survey type
$stmt = $pdo->prepare("
    SELECT 
        c.id as category_id,
        c.name as category_name,
        q.id as question_id,
        q.question_text,
        q.question_type,
        q.options
    FROM categories c
    LEFT JOIN questions q ON c.id = q.category_id
    WHERE c.type = ?
    ORDER BY c.id ASC, q.id ASC
");
$stmt->execute([$surveyType]);
$results = $stmt->fetchAll();

// Organize data by category
$sections = [];
foreach ($results as $row) {
    $categoryId = $row['category_id'];
    if (!isset($sections[$categoryId])) {
        $sections[$categoryId] = [
            'name' => $row['category_name'],
            'questions' => []
        ];
    }
    
    if ($row['question_id']) {
        $sections[$categoryId]['questions'][] = [
            'id' => $row['question_id'],
            'text' => $row['question_text'],
            'type' => $row['question_type'],
            'options' => $row['options']
        ];
    }
}

$currentPage = 'manage-questions';
require_once '../includes/header.php';
require_once '../includes/sidebar-dynamic.php';
?>

<style>
.modern-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}
.modern-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}
.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.section-actions {
    display: flex;
    gap: 8px;
}
.btn-modern {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}
.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.btn-primary-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-success-modern {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}
.btn-warning-modern {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}
.btn-danger-modern {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
}
.question-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}
.question-card:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.form-modern {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e2e8f0;
}
.input-modern {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 12px;
    transition: all 0.2s ease;
}
.input-modern:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}
.badge-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.survey-selector {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    margin-bottom: 24px;
}
.add-section-form {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}
</style>

<div class="wrapper">
<div class="main-content">
    <div class="row small-spacing">
        <!-- Modern Header -->
        <div class="col-xs-12">
            <div class="survey-selector">
                <div class="row">
                    <div class="col-md-6">
                        <h2 style="margin: 0; color: #1f2937; font-weight: 600;">
                            <i class="fa fa-cogs" style="color: #667eea;"></i> 
                            Survey Question Manager
                        </h2>
                        <p style="color: #6b7280; margin: 8px 0 0 0;">Create, edit, and organize questions for your surveys</p>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; color: #374151;">Survey Type:</label>
                            <select class="form-control input-modern" style="margin-top: 8px;" onchange="window.location.href='?type='+this.value">
                                <option value="student" <?php echo $surveyType === 'student' ? 'selected' : ''; ?>>Student Survey</option>
                                <option value="board" <?php echo $surveyType === 'board' ? 'selected' : ''; ?>>Board Survey</option>
                                <option value="staff" <?php echo $surveyType === 'staff' ? 'selected' : ''; ?>>Staff Survey</option>
                                <option value="alumni" <?php echo $surveyType === 'alumni' ? 'selected' : ''; ?>>Alumni Survey</option>
                                <option value="guardian" <?php echo $surveyType === 'guardian' ? 'selected' : ''; ?>>Guardian Survey</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success" style="margin-top: 20px; border-radius: 8px;">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="margin-top: 20px; border-radius: 8px;">
                        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add New Section -->
        <div class="col-xs-12">
            <div class="add-section-form">
                <h4 style="margin: 0 0 16px 0;"><i class="fa fa-plus-circle"></i> Create New Section</h4>
                <form method="POST" class="row">
                    <input type="hidden" name="action" value="add_section">
                    <input type="hidden" name="survey_type" value="<?php echo $surveyType; ?>">
                    <div class="col-md-8">
                        <input type="text" name="section_name" class="form-control input-modern" 
                               placeholder="Enter section name (e.g., School Environment, Facilities)" 
                               required style="background: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.3);">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-modern" style="background: rgba(255,255,255,0.2); color: white; width: 100%; border: 1px solid rgba(255,255,255,0.3);">
                            <i class="fa fa-plus"></i> Create Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sections and Questions -->
        <?php if (empty($sections)): ?>
        <div class="col-xs-12">
            <div class="modern-card" style="text-align: center; padding: 60px 40px;">
                <i class="fa fa-folder-open" style="font-size: 64px; color: #d1d5db; margin-bottom: 24px;"></i>
                <h3 style="color: #374151; margin-bottom: 12px;">No sections found</h3>
                <p style="color: #6b7280; margin-bottom: 24px;">Create your first section using the form above to get started.</p>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px; border-radius: 8px; display: inline-block;">
                    <i class="fa fa-lightbulb-o"></i> Tip: Organize your survey into logical sections like "Environment", "Teaching", "Facilities"
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <?php foreach ($sections as $categoryId => $section): ?>
        <div class="col-xs-12">
            <div class="box-content card white">
                <div class="card-content">
                    <div class="section-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                        <h4 style="margin: 0; color: #333;">
                            <i class="fa fa-folder"></i> <?php echo htmlspecialchars($section['name']); ?>
                            <span class="badge" style="background: #5cb85c; margin-left: 10px;">
                                <?php echo count($section['questions']); ?> questions
                            </span>
                        </h4>
                    </div>
                    
                    <!-- Add Question Form -->
                    <div class="add-question-form" style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <h5><i class="fa fa-plus"></i> Add New Question</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_question">
                            <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Question Text</label>
                                        <textarea name="question_text" class="form-control" rows="3" required 
                                                placeholder="Enter your question here..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Question Type</label>
                                        <select name="question_type" class="form-control" required>
                                            <option value="">Select Type</option>
                                            <option value="likert_scale">Likert Scale</option>
                                            <option value="multiple_choice">Multiple Choice</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="text">Text Response</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Options (if applicable)</label>
                                        <input type="text" name="options" class="form-control" 
                                               placeholder="e.g., Option1,Option2,Option3">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-plus"></i> Add Question
                            </button>
                        </form>
                    </div>
                    
                    <!-- Existing Questions -->
                    <?php if (empty($section['questions'])): ?>
                    <div class="text-center" style="padding: 20px; color: #999;">
                        <i class="fa fa-question-circle" style="font-size: 24px;"></i>
                        <p>No questions in this section yet</p>
                    </div>
                    <?php else: ?>
                    <div class="questions-list">
                        <h5><i class="fa fa-list"></i> Questions (<?php echo count($section['questions']); ?>)</h5>
                        <?php foreach ($section['questions'] as $index => $question): ?>
                        <div class="question-item" style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 10px; background: white;">
                            <div class="row">
                                <div class="col-md-8">
                                    <strong><?php echo ($index + 1); ?>. <?php echo htmlspecialchars($question['text']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Type: <?php echo ucfirst(str_replace('_', ' ', $question['type'])); ?>
                                        <?php if ($question['options']): ?>
                                        | Options: <?php echo htmlspecialchars($question['options']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this question?')">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
    </div>
</div>
</div>

<?php require_once '../includes/footer.php'; ?>
