<?php
session_start();
require_once '../config/database.php';
require_once '../includes/survey-common.php';

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get submission ID from session
        $submission_id = $_SESSION['survey_submission_id'] ?? uniqid('survey_', true);
        
        // Process answers
        if (isset($_POST['answers']) && is_array($_POST['answers'])) {
            // Loop through answer groups (core and teacher-specific)
            foreach ($_POST['answers'] as $context => $questionAnswers) {
                if (!is_array($questionAnswers)) continue;
                
                foreach ($questionAnswers as $questionId => $answer) {
                    // Handle array answers (checkboxes)
                    if (is_array($answer)) {
                        $answer = json_encode($answer);
                    } else {
                        $answer = trim($answer);
                    }
                    
                    // If this is a teacher context (not 'core')
                    $teacherId = null;
                    if ($context !== 'core') {
                        $teacherId = $context; // Context is the teacher_user_dcid
                    }
                    
                    // Insert into responses table
                    $stmt = $pdo->prepare("
                        INSERT INTO responses (question_id, answer, created_at, submission_id, teacher_id)
                        VALUES (?, ?, NOW(), ?, ?)
                    ");
                    $stmt->execute([$questionId, $answer, $submission_id, $teacherId]);
                }
            }
        } else {
            throw new Exception("No answer data provided");
        }
        
        // Clear session data related to the survey
        unset($_SESSION['survey_submission_id']);
        unset($_SESSION['survey_student_id']);
        unset($_SESSION['survey_student_psid']);
        unset($_SESSION['survey_grade_level']);
        unset($_SESSION['survey_teachers']);
        
        // Redirect to thank you page
        header("Location: ../thank-you.php");
        exit;
    } catch (Exception $e) {
        error_log("Survey Submission Error: " . $e->getMessage());
        
        // Return to the survey with an error message
        header("Location: survey.php?error=submission_failed&details=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Not a POST request
    header("Location: survey.php");
    exit;
}
?> 