<?php
session_start();
require_once '../config/database.php';
require_once '../includes/survey-common.php';
require_once '../includes/survey-helper.php';

// Validate that guardian survey is active
validateSurveyAccess($pdo, 'guardian');

$message = '';
$error = '';
$currentPage = 'guardian';
$surveyType = 'guardian';
$questionNumber = 1;

// Check for survey code
if (!isset($_SESSION['survey_code'])) {
    if (isset($_POST['survey_code'])) {
        // Verify the survey code
        $stmt = $pdo->prepare("
            SELECT * FROM survey_codes 
            WHERE type = 'guardian' 
            AND code = ? 
            AND active = true 
            AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$_POST['survey_code']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['survey_code'] = $_POST['survey_code'];
            $_SESSION['authenticated_guardian'] = true;
            header("Location: survey.php");
            exit;
        } else {
            header("Location: survey.php?error=invalid_code"); 
            exit;
        }
    } else {
        // Get error message if exists
        $error = isset($_GET['error']) ? $_GET['error'] : '';
        // Show survey code form
        include '../includes/survey-code-form.php';
        exit;
    }
}

// Check authentication before proceeding
if (!isset($_SESSION['authenticated_guardian']) || !$_SESSION['authenticated_guardian']) {
    unset($_SESSION['survey_code']); // Clear any existing code
    header("Location: survey.php?error=access_denied");  // Added error parameter
    exit;
}

// Handle survey submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answers'])) {
    try {
        handleSurveySubmission($pdo, $_POST['answers']);
        unset($_SESSION['survey_code']);
        unset($_SESSION['authenticated_guardian']);
        header("Location: /isy_scs_ai/thank-you.php");
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        exit(json_encode(['error' => $e->getMessage()]));
    }
}

// Handle survey submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answers'])) {
    try {
        handleSurveySubmission($pdo, $_POST['answers']);
        unset($_SESSION['survey_code']);
        unset($_SESSION['authenticated_guardian']);
        header("Location: /isy_scs_ai/thank-you.php");
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        exit(json_encode(['error' => $e->getMessage()]));
    }
}
// Get survey questions
$results = getSurveyQuestions($pdo, 'guardian');
$categories = organizeSurveyData($results);

// Update total sections count
$totalSections = count($categories);
$currentSection = isset($_GET['section']) ? (int)$_GET['section'] : 0;

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php include '../includes/header.php';?>
    <div class="container survey-container">
    <div class="survey-header">
        <h1>School Climate Survey - Guardian</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>

    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress" style="width: <?php echo (($currentSection + 1) / $totalSections) * 100; ?>%"></div>
        </div>
        <div class="section-navigation">
            <span>Section <?php echo $currentSection + 1; ?> of <?php echo $totalSections; ?></span>
        </div>
    </div>

    <form method="POST" class="survey-form" onsubmit="handleSubmit(event)">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <?php foreach ($categories as $categoryIndex => $category): ?>
            <div class="category-section" style="display: none;">
                <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                <?php foreach ($category['questions'] as $question): ?>
                    <?php if ($question['type'] === 'content'): ?>
                        <div class="content-text">
                            <?php echo nl2br(htmlspecialchars($question['text'])); ?>
                        </div>
                    <?php else: ?>
                        <div class="question-item">
                            <p><strong><?php echo $questionNumber . '. ' . htmlspecialchars($question['text']); ?></strong></p>
                            <?php $questionNumber++; ?>
                            
                            <?php switch($question['type']): 
                                case 'likert_scale': ?>
                                    <div class="likert-scale">
                                        <?php 
                                        $scaleOptions = parseQuestionOptions($question['options']);
                                        if (!empty($scaleOptions)) {
                                            foreach ($scaleOptions as $index => $optionText): ?>
                                                <div class="scale-option">
                                                    <input type="radio" 
                                                        id="option_<?php echo $index; ?>_<?php echo $question['id']; ?>" 
                                                        name="answers[<?php echo $question['id']; ?>]" 
                                                        value="<?php echo ($index + 1); ?>" 
                                                        required
                                                        aria-required="true"
                                                        tabindex="0">
                                                    <label for="option_<?php echo $index; ?>_<?php echo $question['id']; ?>">
                                                        <span class="radio-circle"></span>
                                                        <span class="radio-text"><?php echo htmlspecialchars($optionText); ?></span>
                                                    </label>
                                                </div>
                                            <?php endforeach;
                                        } ?>
                                    </div>
                                    <div class="error-message">Please select an option</div>
                                    <?php break; ?>

                                <?php case 'drop_down': ?>
                                    <select name="answers[<?php echo $question['id']; ?>]" required>
                                        <option value="">Select an option</option>
                                        <?php foreach (parseQuestionOptions($question['options']) as $option): ?>
                                            <option value="<?php echo htmlspecialchars(trim($option)); ?>">
                                                <?php echo htmlspecialchars(trim($option)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php break; ?>

                                <?php case 'checkbox': ?>
                                    <?php foreach (parseQuestionOptions($question['options']) as $option): ?>
                                        <label>
                                            <input type="checkbox" name="answers[<?php echo $question['id']; ?>][]" 
                                                   value="<?php echo htmlspecialchars(trim($option)); ?>">
                                            <?php echo htmlspecialchars(trim($option)); ?>
                                        </label><br>
                                    <?php endforeach; ?>
                                    <?php break; ?>

                                <?php case 'text': ?>
                                    <textarea name="answers[<?php echo $question['id']; ?>]" required rows="4"></textarea>
                                    <?php break; ?>

                            <?php endswitch; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="navigation-buttons">
            <button type="button" id="prevButton" onclick="prevSection()" style="display: none;">Previous</button>
            <button type="button" id="nextButton" onclick="nextSection()">Next</button>
            <button type="submit" id="submitButton" style="display: none;">Submit Survey</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script src="/school_climate_survey/assets/js/survey.js"></script>