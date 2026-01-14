<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $survey_type = $_POST['survey_type'] ?? '';
    $survey_code = $_POST['survey_code'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM survey_codes 
            WHERE type = ? 
            AND code = ? 
            AND active = true 
            AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([$survey_type, $survey_code]);
        
        if ($stmt->rowCount() > 0) {
            // Valid code - grant access
            $_SESSION['authenticated_' . $survey_type] = true;
            $_SESSION['survey_code'] = $survey_code;
            
            // Redirect to survey page
            header("Location: ../{$survey_type}/survey.php");
            exit;
        } else {
            // Invalid code - redirect back with error
            header("Location: ../{$survey_type}/survey.php?error=invalid_code");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: ../{$survey_type}/survey.php?error=system_error");
        exit;
    }
}

// No POST data - redirect to home
header("Location: ../index.php");
exit;