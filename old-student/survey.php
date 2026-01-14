<?php
 echo "<!-- TEMPLATE RENDER - Q ID: " . ($questionData['id'] ?? 'N/A') . " | Type: " . ($questionData['feedback_type'] ?? 'N/A') . " | Context: " . ($questionData['teacher_context'] ?? 'core') . " -->";
session_start();
require_once '../config/database.php';

$currentPage = 'student-survey'; // Define for footer include

// --- Function copied from survey-common.php ---
function organizeSurveyData($results) {
    $categories = [];
    
    if (!is_array($results)) { 
        
        error_log("organizeSurveyData received invalid results.");
        return $categories;
    }
    
    echo "<!-- organizeSurveyData called with " . count($results) . " rows -->";
    
    foreach ($results as $row) {
        if (!isset($row['category_id']) || !isset($row['category_name'])) {
            error_log("Skipping row due to missing category data: " . print_r($row, true));
            continue;
        }
        
        $categoryId = $row['category_id'];
        if (!isset($categories[$categoryId])) {
            $categories[$categoryId] = [
                'id' => $categoryId,
                'name' => $row['category_name'],
                'questions' => []
            ];
        }
        
        if (isset($row['id']) && $row['id'] !== null) { 
            $feedbackType = $row['feedback_type'] ?? 'unknown';
            echo "<!-- Adding question ID " . $row['id'] . " with feedback_type=" . $feedbackType . " to category " . $categoryId . " -->";
            
            $categories[$categoryId]['questions'][] = [
                'id'            => $row['id'], 
                'text'          => $row['question_text'] ?? '', 
                'type'          => $row['question_type'] ?? 'text',
                'options'       => $row['options'] ?? null,
                'category_id'   => $row['category_id'], 
                'grade_level'   => $row['grade_level'] ?? null,
                'feedback_type' => $feedbackType,
                'sort_order'    => $row['sort_order'] ?? null
            ];
        } 
    }
    
    // Count questions in each category
    foreach ($categories as $catId => $category) {
        echo "<!-- Category " . $catId . " has " . count($category['questions']) . " questions -->";
    }
    
    return $categories;
}
// --- End of copied function ---

// --- Handle Login Form Submission (Or check if already logged in) ---
$login_error = null;
$submission_error = null;

// Check for submission error
if (isset($_GET['error']) && $_GET['error'] === 'submission_failed') {
    $submission_error = "There was an error submitting your survey. Please try again.";
    
    // Add error details for debugging if available
    if (isset($_GET['details'])) {
        $error_details = urldecode($_GET['details']);
        error_log("Survey submission failed with details: " . $error_details);
        
        // Only show error details in development environment
        if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
            $submission_error .= "<br><small class='text-muted'>Details: " . htmlspecialchars($error_details) . "</small>";
        }
    }
}

$is_logged_in = isset($_SESSION['survey_submission_id']);


if (!$is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_number'])) {
    // --- LOGIN LOGIC START ---
    $student_number = trim($_POST['student_number']);
    if (empty($student_number)) {
        $login_error = "Please enter your student number.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT student_id, student_psid, student_grade_level FROM students WHERE student_number = ? LIMIT 1");
            $stmt->execute([$student_number]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $login_error = "Invalid student number. Please try again.";
            } else {
                $gradeLevelInt = $student['student_grade_level'];
                $gradeLevelString = 'UNKNOWN';
                if ($gradeLevelInt >= 10 && $gradeLevelInt <= 12) { $gradeLevelString = 'HS'; }
                elseif ($gradeLevelInt >= 6 && $gradeLevelInt <= 9) { $gradeLevelString = 'MS'; }
                elseif (($gradeLevelInt >= -2 && $gradeLevelInt <= 5)) { $gradeLevelString = 'ES'; }

                if ($gradeLevelString === 'UNKNOWN') {
                    error_log("Could not map grade: student_id=".$student['student_id'].", grade=".$gradeLevelInt);
                    $login_error = "Could not determine survey level from grade.";
                } else {
                    $_SESSION['survey_student_id'] = $student['student_id'];
                    $_SESSION['survey_student_psid'] = $student['student_psid'];
                    $_SESSION['survey_grade_level'] = $gradeLevelString;
                    $_SESSION['survey_submission_id'] = uniqid('sub_', true);

                    $teacherStmt = $pdo->prepare("
                        SELECT DISTINCT t.teacher_user_dcid, t.teacher_first_name, t.teacher_last_name 
                        FROM schedules s 
                        JOIN teachers t ON CAST(s.teacher_user_dcid AS VARCHAR) = t.teacher_user_dcid 
                        WHERE s.student_id = ?");
                    $teacherStmt->execute([$student['student_psid']]);
                    $_SESSION['survey_teachers'] = $teacherStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Debugging after fetch:
                    error_log("LOGIN SUCCESS DEBUG: Teachers found: " . count($_SESSION['survey_teachers']));

                    header("Location: survey.php"); // Redirect after successful POST
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log("Survey Login Error: " . $e->getMessage());
            $login_error = "An error occurred during login. Please check logs.";
        }
    }
    // --- LOGIN LOGIC END ---
} elseif (!$is_logged_in) {
    // Not logged in, and not a login attempt - Show login form
    include '../includes/header.php'; // Basic header
    ?>
    <div class="container"> 
        <div class="login-container" style="max-width: 400px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"> 
            <h2>Student Survey Login</h2>
            <form action="survey.php" method="POST">
                <div class="form-group">
                    <label for="student_number">Enter Your Student Number:</label>
                    <input type="text" class="form-control" id="student_number" name="student_number" required autofocus>
                </div>
                <?php if ($login_error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-block">Start Survey</button>
            </form>
        </div>
    </div>
    <?php 
    include '../includes/footer.php'; // Basic footer
    exit; // Stop script execution if not logged in
}

// --- If we reach here, student IS logged in --- 

// --- Fetch Questions Based on Session Data ---
$gradeLevel = $_SESSION['survey_grade_level'];
$teachers = $_SESSION['survey_teachers'] ?? []; // Default to empty array if session key somehow missing
$submission_id = $_SESSION['survey_submission_id'];

// 1. Fetch Core Questions
try { // Add try-catch for debugging
    $coreStmt = $pdo->prepare("SELECT q.*, c.name as category_name FROM questions q JOIN categories c ON q.category_id = c.id WHERE c.type = 'student' AND q.feedback_type = 'core' AND q.grade_level = ? ORDER BY c.id, q.sort_order");
    $coreStmt->execute([$gradeLevel]);
    $coreResults = $coreStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug raw query results
    echo "<!-- DEBUG: Raw core query returned " . count($coreResults) . " rows -->";
    foreach ($coreResults as $row) {
        echo "<!-- DEBUG CORE ROW: ID=" . $row['id'] . ", feedback_type=" . $row['feedback_type'] . ", text=" . substr($row['question_text'] ?? '', 0, 30) . "... -->";
    }
    
    $coreCategories = organizeSurveyData($coreResults);
    // echo "<!-- Core Fetch OK -->"; // Optional debug
} catch (PDOException $e) {
    error_log("ERROR Fetching Core Questions: " . $e->getMessage());
    die("Error fetching core questions. Check PHP error log."); // Stop on error
}

// Create a consistent mapping of teachers to questions so each teacher gets different questions
$teacherAssignments = [];
$teacherCount = count($teachers);
if ($teacherCount > 0) {
    foreach ($teachers as $index => $teacher) {
        $teacherAssignments[$teacher['teacher_user_dcid']] = $index;
    }
}

// *** ADDED BACK: Fetch Teacher Questions ***
$teacherCategories = []; // Initialize
try { 
    // Make sure we're only getting teacher feedback type questions
    $teacherStmt = $pdo->prepare("
        SELECT q.*, c.name as category_name 
        FROM questions q 
        JOIN categories c ON q.category_id = c.id 
        WHERE c.type = 'student' 
        AND q.feedback_type = 'teacher' 
        AND q.grade_level = ? 
        ORDER BY c.id, q.sort_order
    ");
    $teacherStmt->execute([$gradeLevel]);
    $teacherResults = $teacherStmt->fetchAll(PDO::FETCH_ASSOC);
    $teacherCategories = organizeSurveyData($teacherResults);
    // Debug teacher categories
    echo "<!-- DEBUG: Teacher Categories Count: " . count($teacherCategories) . " -->";
    echo "<!-- DEBUG: Teacher Questions Count: " . count($teacherResults) . " -->";
    if (count($teacherCategories) === 0) {
        error_log("WARNING: No teacher categories found for grade level: " . $gradeLevel);
    }
} catch (PDOException $e) {
    error_log("ERROR Fetching Teacher Questions: " . $e->getMessage());
    die("Error fetching teacher questions. Check PHP error log.");
}

// No need for $questionNumber here, handled in template
?>
<?php include '../includes/header.php'; // Include header for logged-in view ?>
<div class="container survey-container">
    <div class="survey-header">
        <h1>Student Climate Survey</h1>
        <p class="text-muted">Help us understand your experience and improve our school environment</p>
        
        <?php if ($submission_error): ?>
        <div class="alert alert-danger mt-3">
            <strong>Error:</strong> <?php echo htmlspecialchars($submission_error); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Survey Progress -->
    <div class="survey-progress">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="progress-text">
            <span id="currentStep">Core Questions</span>
            <span id="stepCount">Step 1 of <?php echo count($teachers) + 1; ?></span>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="surveyTab" role="tablist">
        <!-- Restore Core Questions tab -->
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="core-tab" data-toggle="tab" href="#core" role="tab" aria-controls="core" aria-selected="true">Core Questions</a>
        </li>
        <?php 
        // Now add teacher tabs
        foreach ($teachers as $index => $teacher): 
            $teacherTabId = 'teacher-' . $teacher['teacher_user_dcid']; 
            $teacherName = htmlspecialchars($teacher['teacher_first_name'] . ' ' . $teacher['teacher_last_name']);
            echo "<!-- DEBUG: Creating tab pane for teacher: " . $teacherName . " (ID: " . $teacherTabId . ") -->";
        ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="<?php echo $teacherTabId; ?>-tab" data-toggle="tab" href="#<?php echo $teacherTabId; ?>" role="tab" aria-controls="<?php echo $teacherTabId; ?>" aria-selected="false">About <?php echo $teacherName; ?></a>
        </li>
        <?php endforeach; ?>
    </ul>

    <!-- Tab Content -->
    <form method="POST" action="submit-survey.php" class="survey-form" id="studentSurveyForm" onsubmit="handleSubmit(event)"> 
        <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
        <?php if (!empty($_SESSION['csrf_token'])): ?>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <?php endif; ?>
        
        <div class="tab-content mt-3" id="surveyTabContent">
            <!-- Core Questions Pane -->
            <div class="tab-pane fade show active" id="core" role="tabpanel" aria-labelledby="core-tab">
                <?php if (empty($coreCategories)): ?>
                    <p class="alert alert-info">No core questions found for your grade level.</p>
                <?php else:
                    foreach ($coreCategories as $categoryId => $category):
                        $currentQuestionNumber = 1; // RESET number for EACH category ?>
                        <div class="category-section mb-4 p-3 border rounded">
                            <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                            <?php foreach ($category['questions'] as $question): 
                                // Only show questions with feedback_type='core' in core tab
                                $isCoreQuestion = isset($question['feedback_type']) && $question['feedback_type'] === 'core';
                                echo "<!-- CORE TAB - Question ID: " . ($question['id'] ?? 'unknown') . ", feedback_type: " . ($question['feedback_type'] ?? 'none') . ", isCore: " . ($isCoreQuestion ? 'true' : 'false') . " -->";
                                
                                if ($isCoreQuestion):
                                    // DEBUG START
                                    echo "<!-- CORE TAB - Rendering Q ID: {$question['id']} - Type: {$question['feedback_type']} -->";
                                    // DEBUG END
                                    $questionData = $question; 
                                    $questionData['number'] = $currentQuestionNumber++;
                                    $questionData['teacher_context'] = null; 
                                    require('../includes/student-question-template.php');
                                endif;
                            ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Teacher Question Panes -->
            <?php foreach ($teachers as $index => $teacher): 
                $teacherTabId = 'teacher-' . $teacher['teacher_user_dcid'];
                $teacherUserDCID = $teacher['teacher_user_dcid']; 
                $teacherName = htmlspecialchars($teacher['teacher_first_name'] . ' ' . $teacher['teacher_last_name']);
                echo "<!-- DEBUG: Creating tab pane for teacher: " . $teacherName . " (ID: " . $teacherTabId . ") -->";
            ?>
            <div class="tab-pane fade" id="<?php echo $teacherTabId; ?>" role="tabpanel" aria-labelledby="<?php echo $teacherTabId; ?>-tab">
                <h3>Questions about <?php echo $teacherName; ?></h3>
                <?php 
                // DEBUG: Show raw question data to verify feedback_type values
                echo "<!-- DEBUG QUESTION DATA FOR " . $teacherName . " -->";
                
                // Directly query for ONLY teacher-specific questions to avoid any filtering issues
                try {
                    $teacherQs = $pdo->prepare("
                        SELECT q.*, c.name as category_name 
                        FROM questions q 
                        JOIN categories c ON q.category_id = c.id 
                        WHERE c.type = 'student' 
                        AND q.feedback_type = 'teacher' 
                        AND q.grade_level = ? 
                        ORDER BY c.id, q.sort_order
                    ");
                    $teacherQs->execute([$gradeLevel]);
                    $thisTeacherResults = $teacherQs->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Debug raw query results
                    echo "<!-- DEBUG: Raw teacher query returned " . count($thisTeacherResults) . " rows -->";
                    foreach ($thisTeacherResults as $row) {
                        echo "<!-- DEBUG ROW: ID=" . $row['id'] . ", feedback_type=" . $row['feedback_type'] . ", text=" . substr($row['question_text'] ?? '', 0, 30) . "... -->";
                    }
                    
                    $thisTeacherCategories = organizeSurveyData($thisTeacherResults);
                    
                    if (empty($thisTeacherCategories)): 
                ?>
                    <p class="alert alert-info">No teacher-specific questions found for your grade level.</p>
                <?php else:
                    foreach ($thisTeacherCategories as $categoryId => $category): 
                        // Only process if the category has questions
                        if (empty($category['questions'])) continue;
                        
                        $currentQuestionNumber = 1; // RESET number for EACH category 
                ?>
                    <div class="category-section mb-4 p-3 border rounded">
                        <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                        <?php 
                        echo "<!-- DEBUG: Category " . $category['name'] . " has " . count($category['questions']) . " teacher questions -->";
                        foreach ($category['questions'] as $question): 
                            // Each question should be a teacher question since we queried specifically for them
                            $questionData = $question; 
                            $questionData['number'] = $currentQuestionNumber++;
                            $questionData['teacher_context'] = $teacherUserDCID; 
                            // Add a unique field name that includes teacher ID to prevent form conflicts
                            $questionData['field_prefix'] = 'teacher_' . $teacherUserDCID . '_';
                            echo "<!-- TEACHER TAB - Rendering Q ID: " . ($questionData['id'] ?? 'N/A') . " | Type: " . ($questionData['feedback_type'] ?? 'N/A') . " -->";
                            require('../includes/student-question-template.php');
                        endforeach; 
                        ?>
                    </div>
                <?php 
                    endforeach;
                endif;
                
                } catch (PDOException $e) {
                    echo '<p class="alert alert-danger">Error loading teacher questions: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
            <?php endforeach; // End loop through $teachers ?>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons mt-4 d-flex justify-content-between">
            <button type="button" id="prevButton" class="btn btn-secondary" style="display: none;">Previous</button>
            <button type="button" id="nextButton" class="btn btn-primary">Next</button>
            <button type="submit" id="submitButton" class="btn btn-success" style="display: none;">Submit Survey</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; // Include footer for logged-in view ?>

<!-- Force bootstrap initialization of tabs -->
<script>
// Make sure tabs are properly initialized after everything is loaded
$(document).ready(function() {
    console.log('Student survey JS loaded. Initializing Bootstrap tabs.');
    
    // First, hide ALL tab panes completely
    $('.tab-pane').removeClass('active show').css('display', 'none');
    $('.nav-link').removeClass('active');
    
    // Then activate only the first tab
    $('#core-tab').addClass('active');
    
    // Show only the core tab
    $('#core').css('display', 'block').addClass('show active');
    
    // Log tab contents for debugging
    const navItems = $('.nav-tabs .nav-item').length;
    console.log('Tab nav items:', navItems);
    
    const tabPanes = $('.tab-pane').length;
    console.log('Tab panes:', tabPanes);
    
    // Log each tab pane content
    $('.tab-pane').each(function() {
        console.log('Tab pane ID:', $(this).attr('id'), 'Content empty:', $(this).children().length === 0);
    });
});
</script>

<style>
/* Tab Content Visibility Fix */
.tab-pane {
    display: none !important;
}

.tab-pane.active {
    display: block !important;
}

/* Active tab styling */
.nav-tabs .nav-link.active {
    color: #fff;
    background: #ddb41f;
    box-shadow: 0 5px 15px rgba(221, 180, 31, 0.2);
    transform: translateY(-2px);
    font-weight: 600;
}

/* Completed tab styling */
.nav-tabs .nav-item.completed .nav-link {
    color: #1cc88a;
    border-color: #1cc88a;
}

.nav-tabs .nav-item.completed .nav-link:after {
    content: '✓';
    display: inline-block;
    margin-left: 5px;
}

/* Toast styling */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    min-width: 250px;
    margin-bottom: 10px;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    animation: slideInRight 0.3s ease, fadeOut 0.5s ease 3.5s forwards;
    background: white;
    border-left: 5px solid;
}

.toast.success {
    border-left-color: #28a745;
}

.toast.warning {
    border-left-color: #ffc107;
}

.toast.error {
    border-left-color: #dc3545;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; visibility: hidden; }
}

/* Shake animation for invalid questions */
@keyframes shake {
    0% { transform: translateX(0); }
    20% { transform: translateX(-10px); }
    40% { transform: translateX(10px); }
    60% { transform: translateX(-10px); }
    80% { transform: translateX(10px); }
    100% { transform: translateX(0); }
}

.shake-animation {
    animation: shake 0.5s;
}

/* Success Modal */
.success-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease-in-out;
}

.success-content {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    animation: scaleIn 0.3s ease-in-out;
}

.success-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background-color: #ddb41f;
    color: white;
    font-size: 40px;
    line-height: 70px;
    margin: 0 auto 20px;
}

.success-content h2 {
    color: #333;
    margin-bottom: 15px;
}

.success-content p {
    color: #666;
    margin-bottom: 10px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<!-- Inline Tab Navigation Javascript -->
<script>
$(document).ready(function() {
    // Store completed tabs
    const completedTabs = new Set();
    
    console.log("Enhanced survey tab navigation initialized");
    
    // IMPROVED INITIALIZATION - Force proper tab display on page load
    function initializeTabs() {
        console.log("Initializing tabs");
        
        // First hide all tab panes
        $('.tab-pane').removeClass('active show').hide();
        
        // Deactivate all tabs
        $('.nav-tabs .nav-link').removeClass('active');
        
        // Activate only first tab
        $('.nav-tabs .nav-link:first').addClass('active');
        
        // Show only first tab pane
        const firstTabId = $('.nav-tabs .nav-link:first').attr('href');
        $(firstTabId).addClass('active show').show();
        
        // Initialize progress bar
        updateProgressBar(firstTabId);
        
        // Update buttons
        updateButtons();
        
        console.log("Tabs initialized, showing first tab:", firstTabId);
    }
    
    // Call initialization
    initializeTabs();
    
    // Handle tab change event
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("href");
        console.log("Showing tab:", target);
        console.log("Tab content exists:", $(target).length > 0);
        console.log("Tab content has questions:", $(target).find('.question-item').length);
        
        // Hide all tab panes first
        $('.tab-pane').removeClass('show active').css('display', 'none');
        
        // Show only the current tab
        $(target).css('display', 'block').addClass('show active');
        
        // Update progress bar
        updateProgressBar(target);
        
        // Update button visibility
        updateButtons();
    });
    
    // Function to update progress bar
    function updateProgressBar(currentTab) {
        // Handle both jQuery elements and selector strings
        let tabElement, tabIndex;
        
        if (typeof currentTab === 'string') {
            // If it's a selector like '#core'
            tabElement = $(`a[href="${currentTab}"]`);
            tabIndex = tabElement.parent().index();
        } else {
            // If it's already a jQuery element
            tabElement = $(currentTab);
            tabIndex = tabElement.parent().index();
        }
        
        const totalTabs = $('.nav-tabs .nav-item').length;
        const progressPercentage = Math.round((tabIndex / (totalTabs - 1)) * 100);
        
        // Update progress bar width
        $('.progress-bar').css('width', progressPercentage + '%').attr('aria-valuenow', progressPercentage);
        
        // Update step text
        const currentStepName = tabElement.text().trim();
        $('#currentStep').text(currentStepName);
        $('#stepCount').text('Step ' + (tabIndex + 1) + ' of ' + totalTabs);
    }

    // Initialize the progress bar with the first tab
    updateProgressBar('#core');

    // Next button click handler
    $('#nextButton').on('click', function() {
        // Get current tab pane and tab
        const currentTabPane = $('.tab-pane.active');
        const currentTabId = currentTabPane.attr('id');
        
        // Find the next tab nav item based on current tab id
        const currentNavTab = $(`a[href="#${currentTabId}"]`);
        const nextNavTab = currentNavTab.parent().next().find('a[data-toggle="tab"]');
        
        console.log('Next button clicked:', {
            currentTabId: currentTabId,
            nextTabId: nextNavTab.attr('href'),
            nextTabExists: nextNavTab.length > 0
        });
        
        // IMPROVED VALIDATION - Properly handle radio groups
        let isValid = true;
        const invalidInputs = [];
        const radioGroups = {};
        
        // Get all required inputs in the current tab
        const requiredInputs = currentTabPane.find('input[required], select[required], textarea[required]');
        console.log('Required inputs found:', requiredInputs.length);
        
        // First pass: collect all radio groups
        requiredInputs.each(function() {
            if ($(this).attr('type') === 'radio') {
                const name = $(this).attr('name');
                if (!radioGroups[name]) {
                    radioGroups[name] = {
                        inputs: [],
                        checked: false,
                        questionItem: $(this).closest('.question-item')
                    };
                }
                radioGroups[name].inputs.push($(this));
                if ($(this).is(':checked')) {
                    radioGroups[name].checked = true;
                }
            }
        });
        
        // Second pass: validate standard inputs and mark radio groups
        requiredInputs.each(function() {
            // Skip radio buttons as we'll handle them separately
            if ($(this).attr('type') === 'radio') return;
            
            const questionItem = $(this).closest('.question-item');
            $(this).removeClass('input-invalid');
            questionItem.removeClass('input-invalid');
            
            // Validate all other input types
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('input-invalid');
                questionItem.addClass('input-invalid');
                invalidInputs.push($(this));
                console.log('Invalid input:', $(this).attr('name') || $(this).attr('id'));
            }
        });
        
        // Third pass: validate radio groups
        Object.keys(radioGroups).forEach(groupName => {
            const group = radioGroups[groupName];
            console.log('Checking radio group:', groupName, 'Checked:', group.checked);
            
            if (!group.checked) {
                isValid = false;
                group.questionItem.addClass('input-invalid');
                // Just add the first input from the group to invalidInputs
                invalidInputs.push(group.inputs[0]);
                console.log('Invalid radio group:', groupName);
            } else {
                group.questionItem.removeClass('input-invalid');
            }
        });
        
        if (!isValid) {
            // Show toast notification for invalid inputs
            console.log('Validation failed. Invalid inputs:', invalidInputs.length);
            
            showToast('Please complete all required fields before proceeding.', 'warning');
            
            // Add shake animation to invalid fields
            invalidInputs.forEach(input => {
                const questionItem = input.closest('.question-item');
                questionItem.addClass('shake-animation');
                setTimeout(() => {
                    questionItem.removeClass('shake-animation');
                }, 500);
            });
            
            // Scroll to first invalid input
            if (invalidInputs.length > 0) {
                $('html, body').animate({
                    scrollTop: invalidInputs[0].closest('.question-item').offset().top - 100
                }, 500);
            }
            
            return false;
        }
        
        console.log('Validation passed, going to next tab...');
        
        // Mark current tab as completed
        completedTabs.add(currentTabId);
        currentNavTab.parent().addClass('completed');
        
        // IMPROVED TAB SWITCHING - More reliable method
        if (nextNavTab.length > 0) {
            console.log('Showing next tab:', nextNavTab.attr('href'));
            
            // Forcefully hide all tab panes
            $('.tab-pane').removeClass('active show').hide();
            
            // Get next tab ID
            const nextTabId = nextNavTab.attr('href');
            
            // Manually update nav tabs
            $('.nav-tabs .nav-link').removeClass('active');
            nextNavTab.addClass('active');
            
            // Forcefully show the next tab
            $(nextTabId).addClass('active show').show();
            
            window.scrollTo(0, 0);
            
            // Update progress bar and buttons
            updateProgressBar(nextTabId);
            updateButtons();
        } else {
            console.error('No next tab found!');
        }
    });

    // Previous button click handler
    $('#prevButton').on('click', function() {
        // Get current tab pane and tab
        const currentTabPane = $('.tab-pane.active');
        const currentTabId = currentTabPane.attr('id');
        
        // Find the previous tab nav item based on current tab id
        const currentNavTab = $(`a[href="#${currentTabId}"]`);
        const prevNavTab = currentNavTab.parent().prev().find('a[data-toggle="tab"]');
        
        console.log('Prev button clicked:', {
            currentTabId: currentTabId,
            prevTabId: prevNavTab.attr('href'),
            prevTabExists: prevNavTab.length > 0
        });
        
        // Activate previous tab - manual trigger to ensure it works
        if (prevNavTab.length > 0) {
            console.log('Showing previous tab:', prevNavTab.attr('href'));
            
            // Hide all tab panes completely
            $('.tab-pane').removeClass('show active').css('display', 'none');
            
            // Get previous tab ID
            const prevTabId = prevNavTab.attr('href');
            
            // Activate previous tab with Bootstrap API
            prevNavTab.tab('show');
            
            // Make sure only the previous tab is visible
            $(prevTabId).css('display', 'block').addClass('show active');
            
            window.scrollTo(0, 0);
        } else {
            console.error('No previous tab found!');
        }
    });
    
    // Update navigation buttons visibility
    function updateButtons() {
        // Find the currently active tab
        const activeTabLink = $('.nav-tabs .nav-link.active');
        const currentTabIndex = activeTabLink.parent().index();
        const totalTabs = $('.nav-tabs .nav-item').length;
        
        console.log('Updating buttons:', {
            activeTab: activeTabLink.attr('href'),
            currentTabIndex: currentTabIndex,
            totalTabs: totalTabs
        });
        
        // Update button visibility based on current position
        $('#prevButton').css('display', currentTabIndex > 0 ? 'inline-block' : 'none');
        $('#nextButton').css('display', currentTabIndex < totalTabs - 1 ? 'inline-block' : 'none');
        $('#submitButton').css('display', currentTabIndex === totalTabs - 1 ? 'inline-block' : 'none');
    }
    
    // Update buttons when tab changes
    $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
        updateButtons();
    });
    
    // Initialize button states
    updateButtons();
});
</script>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- Add this at the end of the page, before </body> -->
<script>
// Function to show toast notification
function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Set icon based on type
    let icon = '';
    switch(type) {
        case 'success':
            icon = '✓';
            break;
        case 'warning':
            icon = '⚠';
            break;
        case 'error':
            icon = '✕';
            break;
        default:
            icon = 'ℹ';
    }
    
    // Set content
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${message}</div>
    `;
    
    // Add to container
    container.appendChild(toast);
    
    // Remove after animation completes
    setTimeout(() => {
        toast.remove();
    }, 4000);
}

// Function to create confetti effect
function createConfetti() {
    const confettiContainer = document.createElement('div');
    confettiContainer.className = 'confetti-container';
    document.body.appendChild(confettiContainer);
    
    const colors = ['#ddb41f', '#e0c04e', '#ffc107', '#28a745', '#dc3545', '#ff4d6b'];
    
    // Create 100 confetti pieces
    for (let i = 0; i < 100; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        
        // Random properties
        const color = colors[Math.floor(Math.random() * colors.length)];
        const left = Math.random() * 100 + 'vw';
        const size = Math.random() * 10 + 5 + 'px';
        const duration = Math.random() * 3 + 2 + 's';
        
        // Apply styles
        confetti.style.backgroundColor = color;
        confetti.style.left = left;
        confetti.style.width = size;
        confetti.style.height = size;
        confetti.style.animationDuration = duration;
        
        confettiContainer.appendChild(confetti);
    }
    
    // Remove after animation completes
    setTimeout(() => {
        confettiContainer.remove();
    }, 5000);
}

// Function to show success modal
function showSuccessModal() {
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'success-modal';
    modal.innerHTML = `
        <div class="success-content">
            <div class="success-icon">✓</div>
            <h2>Thank You!</h2>
            <p>Your survey has been submitted successfully.</p>
            <p>You will be redirected to the home page shortly...</p>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(modal);
    
    // Show confetti in background
    createConfetti();
}

// Update form submission handler to use direct form submission like alumni survey
$('#studentSurveyForm').on('submit', function(e) {
    // Only validate the current section
    const currentTabPane = $('.tab-pane.active');
    const requiredInputs = currentTabPane.find('input[required], select[required], textarea[required]');
    let isValid = true;
    
    requiredInputs.each(function() {
        if ($(this).attr('type') === 'radio') {
            const name = $(this).attr('name');
            const isChecked = currentTabPane.find(`input[name="${name}"]:checked`).length > 0;
            if (!isChecked) isValid = false;
        } else if (!$(this).val()) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        showToast('Please complete all required fields before submitting.', 'warning');
        return false;
    }
    
    // Disable submit button
    const submitButton = document.getElementById('submitButton');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
    }
    
    // Let the form submit normally - using the standard HTML form submission
    return true;
});
</script>

<script src="/isy_scs_ai/assets/scripts/scs/survey.js"></script>

</html>