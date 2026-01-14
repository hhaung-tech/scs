<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

// Get survey type from URL parameter first
$surveyType = $_GET['type'] ?? 'student';
$validTypes = ['student', 'board', 'staff', 'alumni', 'guardian'];
if (!in_array($surveyType, $validTypes)) {
    $surveyType = 'student';
}

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
                header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . $surveyType . '&success=section_created');
                exit;
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
                header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . $surveyType . '&success=section_updated');
                exit;
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
                header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . $surveyType . '&success=section_deleted');
                exit;
                break;
                
            case 'add_question':
                $categoryId = $_POST['category_id'] ?? '';
                $questionText = trim($_POST['question_text'] ?? '');
                $questionType = $_POST['question_type'] ?? '';
                $options = $_POST['options'] ?? '';
                
                if (empty($questionText) || empty($categoryId) || empty($questionType)) {
                    throw new Exception("All fields are required");
                }
                
                // Convert options to JSON format for frontend compatibility
                if (!empty($options) && in_array($questionType, ['likert_scale', 'multiple_choice', 'drop_down', 'checkbox'])) {
                    // Check if it's already JSON
                    $decoded = json_decode($options, true);
                    if ($decoded === null) {
                        // Convert comma-separated to JSON array
                        $optionsArray = array_map('trim', explode(',', $options));
                        $options = json_encode($optionsArray);
                    }
                }
                
                $stmt = $pdo->prepare("INSERT INTO questions (category_id, question_text, question_type, options) VALUES (?, ?, ?, ?)");
                $stmt->execute([$categoryId, $questionText, $questionType, $options]);
                
                $success = "Question added successfully!";
                header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . $surveyType . '&success=question_added');
                exit;
                break;
                
            case 'edit_question':
                $questionId = $_POST['question_id'] ?? '';
                $questionText = trim($_POST['question_text'] ?? '');
                $questionType = $_POST['question_type'] ?? '';
                $options = $_POST['options'] ?? '';
                
                if (empty($questionText) || empty($questionId) || empty($questionType)) {
                    throw new Exception("All fields are required");
                }
                
                // Convert options to JSON format for frontend compatibility
                if (!empty($options) && in_array($questionType, ['likert_scale', 'multiple_choice', 'drop_down', 'checkbox'])) {
                    // Check if it's already JSON
                    $decoded = json_decode($options, true);
                    if ($decoded === null) {
                        // Convert comma-separated string to JSON array
                        $optionsArray = array_map('trim', explode(',', $options));
                        $options = json_encode($optionsArray);
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, question_type = ?, options = ? WHERE id = ?");
                $stmt->execute([$questionText, $questionType, $options, $questionId]);
                
                $success = "Question updated successfully!";
                // Redirect to prevent form resubmission and ensure fresh data
                header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . $surveyType . '&success=question_updated');
                exit;
                break;
                
            case 'delete_question':
                $questionId = $_POST['question_id'] ?? '';
                if (empty($questionId)) {
                    throw new Exception("Question ID is required");
                }
                
                $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                $stmt->execute([$questionId]);
                
                $success = "Question deleted successfully!";
                header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . $surveyType . '&success=question_deleted');
                exit;
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'section_created':
            $success = "Section created successfully!";
            break;
        case 'section_updated':
            $success = "Section updated successfully!";
            break;
        case 'section_deleted':
            $success = "Section deleted successfully!";
            break;
        case 'question_added':
            $success = "Question added successfully!";
            break;
        case 'question_updated':
            $success = "Question updated successfully!";
            break;
        case 'question_deleted':
            $success = "Question deleted successfully!";
            break;
    }
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

<!-- Include responsive fixes for production -->
<link rel="stylesheet" href="/isy_scs_ai/assets/styles/responsive-fixes.css">

<!-- ISY admin theme (navy + gold) -->
<link rel="stylesheet" href="/isy_scs_ai/assets/styles/isy-admin-theme.css">

<style>
.modern-container {
    background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%);
    min-height: 100vh;
    padding: 20px 0;
}
@media (max-width: 768px) {
    .modern-container {
        padding: 10px 0;
    }
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
}
.modern-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: none;
    transition: all 0.3s ease;
    overflow: hidden;
}
.modern-card:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}
.section-header {
    background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%);
    color: white;
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.section-actions {
    display: flex;
    gap: 12px;
}
.btn-modern {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}
.btn-primary-modern {
    background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%);
    color: #fff;
}
.btn-success-modern {
    background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%);
    color: #fff;
}
.btn-warning-modern {
    background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%);
    color: white;
}
.btn-danger-modern {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: #fff;
}
.question-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}
.question-card:hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border-color: #dcb41e;
    transform: translateX(5px);
}
.form-modern {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 12px;
    padding: 24px;
    border: 2px solid #e2e8f0;
    margin-bottom: 24px;
}
/* Reset all select styling */
select.input-modern {
    border-radius: 10px;
    border: 2px solid #d1d5db;
    padding: 14px 40px 14px 14px;
    transition: all 0.3s ease;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-color: white;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    cursor: pointer;
}

/* Input styling for non-select elements */
input.input-modern, textarea.input-modern {
    border-radius: 10px;
    border: 2px solid #d1d5db;
    padding: 14px;
    transition: all 0.3s ease;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
    background-color: white;
}

/* Focus states */
.input-modern:focus {
    border-color: #dcb41e;
    box-shadow: 0 0 0 4px rgba(220, 180, 30, 0.18);
    outline: none;
    transform: scale(1.02);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .input-modern {
        font-size: 16px;
        padding: 12px;
    }
    select.input-modern {
        padding: 12px 40px 12px 12px;
    }
    .input-modern:focus {
        transform: none;
    }
}
.badge-modern {
    background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.survey-selector {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 32px;
}
@media (max-width: 768px) {
    .survey-selector {
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
    }
}
.add-section-form {
    background: linear-gradient(135deg, #dcb41e 0%, #caa61b 100%);
    color: #1a365d;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 32px;
    box-shadow: 0 10px 30px rgba(220, 180, 30, 0.28);
}
@media (max-width: 768px) {
    .add-section-form {
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 12px;
    }
}
.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #dcb41e 0%, #fff3b0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}
@media (max-width: 768px) {
    .hero-title {
        font-size: 1.8rem;
    }
}
.hero-subtitle {
    color: #6b7280;
    font-size: 1.1rem;
    margin-bottom: 0;
}
@media (max-width: 768px) {
    .hero-subtitle {
        font-size: 1rem;
    }
}
.section-header {
    flex-direction: column;
    gap: 16px;
}
@media (min-width: 768px) {
    .section-header {
        flex-direction: row;
        gap: 0;
    }
}
.section-actions {
    width: 100%;
    justify-content: center;
}
@media (min-width: 768px) {
    .section-actions {
        width: auto;
        justify-content: flex-end;
    }
}
.form-modern {
    padding: 20px;
}
@media (min-width: 768px) {
    .form-modern {
        padding: 24px;
    }
}
.question-card {
    padding: 16px;
}
@media (min-width: 768px) {
    .question-card {
        padding: 20px;
    }
}
</style>

<div class="modern-container">
<div class="container">
    <div class="row">
        <!-- Modern Header -->
        <div class="col-xs-12">
            <div class="survey-selector">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h1 class="hero-title">
                            <i class="fa fa-cogs"></i> Survey Manager
                        </h1>
                        <p class="hero-subtitle">Create, edit, and organize questions for your surveys with modern CRUD operations</p>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 700; color: #374151; font-size: 16px;">Survey Type:</label>
                            <select class="input-modern" style="margin-top: 8px;" onchange="window.location.href='?type='+this.value">
                                <option value="student" <?php echo $surveyType === 'student' ? 'selected' : ''; ?>>🎓 Student Survey</option>
                                <option value="board" <?php echo $surveyType === 'board' ? 'selected' : ''; ?>>🏛️ Board Survey</option>
                                <option value="staff" <?php echo $surveyType === 'staff' ? 'selected' : ''; ?>>👥 Staff Survey</option>
                                <option value="alumni" <?php echo $surveyType === 'alumni' ? 'selected' : ''; ?>>🎯 Alumni Survey</option>
                                <option value="guardian" <?php echo $surveyType === 'guardian' ? 'selected' : ''; ?>>👨‍👩‍👧‍👦 Guardian Survey</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success" style="margin-top: 24px; border-radius: 12px; border: none; background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%); color: white;">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" style="margin-top: 24px; border-radius: 12px; border: none; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white;">
                        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Add New Section -->
        <div class="col-xs-12">
            <div class="add-section-form">
                <h3 style="margin: 0 0 20px 0; font-weight: 700;"><i class="fa fa-plus-circle"></i> Create New Section</h3>
                <form method="POST" class="row">
                    <input type="hidden" name="action" value="add_section">
                    <input type="hidden" name="survey_type" value="<?php echo $surveyType; ?>">
                    <div class="col-md-8 col-sm-12" style="margin-bottom: 15px;">
                        <input type="text" name="section_name" class="input-modern" 
                               placeholder="Enter section name (e.g., School Environment, Facilities)" 
                               required style="background: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.3); color: #333;">
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <button type="submit" class="btn btn-modern" style="background: rgba(26,54,93,0.92); color: white; width: 100%; border: 2px solid rgba(26,54,93,0.35);">
                            <i class="fa fa-plus"></i> Create Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sections and Questions -->
        <?php if (empty($sections)): ?>
        <div class="col-xs-12">
            <div class="modern-card" style="text-align: center; padding: 80px 40px;">
                <i class="fa fa-folder-open" style="font-size: 80px; color: #d1d5db; margin-bottom: 32px;"></i>
                <h2 style="color: #374151; margin-bottom: 16px; font-weight: 700;">No sections found</h2>
                <p style="color: #6b7280; margin-bottom: 32px; font-size: 18px;">Create your first section using the form above to get started.</p>
                <div style="background: linear-gradient(135deg, #1a365d 0%, #0f2a4a 100%); color: white; padding: 20px; border-radius: 12px; display: inline-block; max-width: 500px;">
                    <i class="fa fa-lightbulb-o" style="font-size: 24px; margin-bottom: 12px;"></i>
                    <p style="margin: 0; font-weight: 600;">💡 Pro Tip</p>
                    <p style="margin: 8px 0 0 0;">Organize your survey into logical sections like "Environment", "Teaching Quality", "Facilities", "Communication"</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <?php foreach ($sections as $categoryId => $section): ?>
        <div class="col-xs-12">
            <div class="modern-card" style="margin-bottom: 32px;">
                <div class="section-header">
                    <div>
                        <h2 style="margin: 0; font-weight: 700; font-size: 24px;">
                            <i class="fa fa-folder"></i> <?php echo htmlspecialchars($section['name']); ?>
                        </h2>
                        <span class="badge-modern" style="margin-top: 8px; display: inline-block;">
                            <?php echo count($section['questions']); ?> questions
                        </span>
                    </div>
                    <div class="section-actions">
                        <button class="btn btn-modern" style="background: rgba(255,255,255,0.2); color: white;" 
                                onclick="toggleEditSection(<?php echo $categoryId; ?>)">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-modern" style="background: rgba(255,255,255,0.2); color: white;" 
                                onclick="deleteSection(<?php echo $categoryId; ?>, '<?php echo htmlspecialchars($section['name']); ?>')">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                
                <!-- Edit Section Form (Hidden by default) -->
                <div id="edit-section-<?php echo $categoryId; ?>" class="form-modern" style="display: none; margin: 24px;">
                    <h4 style="color: #374151; font-weight: 700;"><i class="fa fa-edit"></i> Edit Section Name</h4>
                    <form method="POST" class="row">
                        <input type="hidden" name="action" value="edit_section">
                        <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                        <div class="col-md-8">
                            <input type="text" name="section_name" class="input-modern" 
                                   value="<?php echo htmlspecialchars($section['name']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-modern btn-success-modern" style="margin-right: 12px;">
                                <i class="fa fa-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-modern" style="background: #6b7280; color: white;" 
                                    onclick="toggleEditSection(<?php echo $categoryId; ?>)">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
                
                <div style="padding: 24px;">
                    <!-- Add Question Form -->
                    <div class="form-modern">
                        <h4 style="color: #374151; margin-bottom: 20px; font-weight: 700;"><i class="fa fa-plus-circle" style="color: #dcb41e;"></i> Add New Question</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_question">
                            <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                            
                            <div class="row">
                                <div class="col-md-12 col-sm-12" style="margin-bottom: 15px;">
                                    <div class="form-group">
                                        <label style="font-weight: 700; color: #374151;">Question Text</label>
                                        <textarea name="question_text" class="input-modern" rows="3" required 
                                                placeholder="Enter your question here..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12" style="margin-bottom: 15px;">
                                    <div class="form-group">
                                        <label style="font-weight: 700; color: #374151;">Question Type</label>
                                        <select name="question_type" class="input-modern" required>
                                            <option value="">Select Type</option>
                                            <option value="multiple_choice">🔘 Multiple Choice (Radio)</option>
                                            <option value="drop_down">🔽 Dropdown</option>
                                            <option value="likert_scale">📊 Likert Scale</option>
                                            <option value="text">📝 Text Response</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12" style="margin-bottom: 15px;">
                                    <div class="form-group">
                                        <label style="font-weight: 700; color: #374151;">Options</label>
                                        <div id="options-container">
                                            <input type="text" name="options" class="input-modern" id="options-input"
                                                   placeholder="For Likert: 1,2,3,4,5 or Strongly Disagree,Disagree,Neutral,Agree,Strongly Agree">
                                            <small style="color: #6b7280; margin-top: 4px; display: block;">
                                                <strong>Likert Scale:</strong> Use 1,2,3,4,5 or custom labels<br>
                                                <strong>Multiple Choice/Checkbox:</strong> Option1,Option2,Option3
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-modern btn-success-modern">
                                <i class="fa fa-plus"></i> Add Question
                            </button>
                        </form>
                    </div>
                    
                    <!-- Existing Questions -->
                    <?php if (empty($section['questions'])): ?>
                    <div style="text-align: center; padding: 60px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 16px; border: 2px dashed #d1d5db;">
                        <i class="fa fa-question-circle" style="font-size: 48px; color: #9ca3af; margin-bottom: 16px;"></i>
                        <h4 style="color: #374151; margin-bottom: 8px;">No questions in this section yet</h4>
                        <p style="color: #6b7280; margin: 0;">Use the form above to add your first question</p>
                    </div>
                    <?php else: ?>
                    <div class="questions-list">
                        <h4 style="color: #374151; margin-bottom: 20px; font-weight: 700;"><i class="fa fa-list" style="color: #dcb41e;"></i> Questions (<?php echo count($section['questions']); ?>)</h4>
                        <?php foreach ($section['questions'] as $index => $question): ?>
                        <div class="question-card" id="question-<?php echo $question['id']; ?>">
                            <div class="row" style="align-items: center;">
                                <div class="col-md-8">
                                    <div class="question-display-<?php echo $question['id']; ?>">
                                        <h5 style="color: #1f2937; font-weight: 600; margin-bottom: 12px;"><?php echo ($index + 1); ?>. <?php echo htmlspecialchars($question['text']); ?></h5>
                                        <div style="margin-top: 12px;">
                                            <span class="badge" style="background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); color: #374151; padding: 6px 12px; border-radius: 6px; font-size: 12px; margin-right: 12px; font-weight: 600;">
                                                <?php echo ucfirst(str_replace('_', ' ', $question['type'])); ?>
                                            </span>
                                            <?php if ($question['options']): ?>
                                            <span class="badge" style="background: linear-gradient(135deg, #dcb41e 0%, #caa61b 100%); color: #1a365d; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                                                <?php 
                                                // Decode JSON options for display
                                                $decodedOptions = json_decode($question['options'], true);
                                                if ($decodedOptions && is_array($decodedOptions)) {
                                                    if ($question['type'] === 'likert_scale') {
                                                        echo "Likert: " . implode(',', $decodedOptions);
                                                    } else {
                                                        echo "Options: " . implode(',', $decodedOptions);
                                                    }
                                                } else {
                                                    // Fallback for non-JSON options
                                                    if ($question['type'] === 'likert_scale') {
                                                        echo "Likert: " . htmlspecialchars($question['options']);
                                                    } else {
                                                        echo "Options: " . htmlspecialchars($question['options']);
                                                    }
                                                }
                                                ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Question Form (Hidden by default) -->
                                    <div class="question-edit-<?php echo $question['id']; ?>" style="display: none;">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="edit_question">
                                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <textarea name="question_text" class="input-modern" rows="2" required 
                                                            style="margin-bottom: 12px;"><?php echo htmlspecialchars($question['text']); ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <select name="question_type" class="input-modern" required style="margin-bottom: 12px;">
                                                        <option value="multiple_choice" <?php echo $question['type'] === 'multiple_choice' ? 'selected' : ''; ?>>🔘 Multiple Choice (Radio)</option>
                                                        <option value="drop_down" <?php echo $question['type'] === 'drop_down' ? 'selected' : ''; ?>>🔽 Dropdown</option>
                                                        <option value="likert_scale" <?php echo $question['type'] === 'likert_scale' ? 'selected' : ''; ?>>📊 Likert Scale</option>
                                                        <?php if ($question['type'] === 'checkbox'): ?>
                                                        <option value="checkbox" selected>☑️ Checkbox (Legacy)</option>
                                                        <?php endif; ?>
                                                        <option value="text" <?php echo $question['type'] === 'text' ? 'selected' : ''; ?>>📝 Text Response</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" name="options" class="input-modern" 
                                                           value="<?php 
                                                           // Convert JSON back to comma-separated for editing
                                                           $decodedOptions = json_decode($question['options'], true);
                                                           if ($decodedOptions && is_array($decodedOptions)) {
                                                               echo htmlspecialchars(implode(',', $decodedOptions));
                                                           } else {
                                                               echo htmlspecialchars($question['options']);
                                                           }
                                                           ?>" 
                                                           placeholder="For Likert: 1,2,3,4,5 or custom labels" style="margin-bottom: 12px;">
                                                    <small style="color: #6b7280; font-size: 11px;">
                                                        Likert: 1,2,3,4,5 or Strongly Disagree,Disagree,Neutral,Agree,Strongly Agree
                                                    </small>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-modern btn-success-modern" style="margin-right: 12px;">
                                                <i class="fa fa-save"></i> Save Changes
                                            </button>
                                            <button type="button" class="btn btn-modern" style="background: #6b7280; color: white;" 
                                                    onclick="toggleEditQuestion(<?php echo $question['id']; ?>)">
                                                <i class="fa fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-modern btn-warning-modern" style="margin-right: 12px;" 
                                            onclick="toggleEditQuestion(<?php echo $question['id']; ?>)">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this question?')">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <button type="submit" class="btn btn-modern btn-danger-modern">
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

<script>
function toggleEditSection(categoryId) {
    const editForm = document.getElementById('edit-section-' + categoryId);
    if (editForm.style.display === 'none') {
        editForm.style.display = 'block';
    } else {
        editForm.style.display = 'none';
    }
}

function toggleEditQuestion(questionId) {
    const displayDiv = document.querySelector('.question-display-' + questionId);
    const editDiv = document.querySelector('.question-edit-' + questionId);
    
    if (editDiv.style.display === 'none') {
        displayDiv.style.display = 'none';
        editDiv.style.display = 'block';
    } else {
        displayDiv.style.display = 'block';
        editDiv.style.display = 'none';
    }
}

function deleteSection(categoryId, sectionName) {
    if (confirm('⚠️ Are you sure you want to delete the section "' + sectionName + '" and all its questions?\n\nThis action cannot be undone!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_section">
            <input type="hidden" name="category_id" value="${categoryId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Dynamic form updates based on question type
function updateOptionsField(selectElement) {
    const optionsInput = selectElement.closest('form').querySelector('input[name="options"]');
    const helpText = selectElement.closest('form').querySelector('small');
    
    if (selectElement.value === 'likert_scale') {
        optionsInput.placeholder = 'e.g., 1,2,3,4,5 or Strongly Disagree,Disagree,Neutral,Agree,Strongly Agree';
        if (helpText) {
            helpText.innerHTML = '<strong>Likert Scale Examples:</strong><br>• Numeric: 1,2,3,4,5<br>• Labels: Strongly Disagree,Disagree,Neutral,Agree,Strongly Agree<br>• Custom: Poor,Fair,Good,Very Good,Excellent';
        }
    } else if (selectElement.value === 'multiple_choice' || selectElement.value === 'drop_down' || selectElement.value === 'checkbox') {
        optionsInput.placeholder = 'e.g., Option A,Option B,Option C,Option D';
        if (helpText) {
            helpText.innerHTML = '<strong>Multiple Choice/Dropdown:</strong> Separate options with commas<br>Example: Yes,No,Maybe or Red,Blue,Green,Yellow';
        }
    } else if (selectElement.value === 'text') {
        optionsInput.placeholder = 'Leave empty for text responses';
        if (helpText) {
            helpText.innerHTML = '<strong>Text Response:</strong> No options needed - users can type their answer';
        }
    }
}

// Add event listeners to question type selects
document.addEventListener('DOMContentLoaded', function() {
    // Animation for cards
    const cards = document.querySelectorAll('.modern-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add change listeners to question type selects
    document.querySelectorAll('select[name="question_type"]').forEach(select => {
        select.addEventListener('change', function() {
            updateOptionsField(this);
        });
        // Initialize on page load
        updateOptionsField(select);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
