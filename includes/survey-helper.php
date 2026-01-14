<?php
/**
 * Survey Helper Functions
 * Provides utility functions for managing dynamic survey settings
 */

/**
 * Get all active surveys ordered by display_order
 */
function getActiveSurveys($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM survey_settings 
            WHERE is_active = true 
            ORDER BY display_order ASC, survey_type ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting active surveys: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all surveys (active and inactive) ordered by display_order
 */
function getAllSurveys($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM survey_settings 
            ORDER BY display_order ASC, survey_type ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting all surveys: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if a specific survey type is active
 */
function isSurveyActive($pdo, $surveyType) {
    try {
        $stmt = $pdo->prepare("
            SELECT is_active FROM survey_settings 
            WHERE survey_type = ? AND is_active = true
        ");
        $stmt->execute([$surveyType]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error checking survey status: " . $e->getMessage());
        return false;
    }
}

/**
 * Get survey settings for a specific survey type
 */
function getSurveySettings($pdo, $surveyType) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM survey_settings 
            WHERE survey_type = ?
        ");
        $stmt->execute([$surveyType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting survey settings: " . $e->getMessage());
        return null;
    }
}

/**
 * Get active survey types as array
 */
function getActiveSurveyTypes($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT survey_type FROM survey_settings 
            WHERE is_active = true 
            ORDER BY display_order ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error getting active survey types: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if survey exists and is active, redirect if not
 */
function validateSurveyAccess($pdo, $surveyType, $redirectUrl = '../index.php') {
    if (!isSurveyActive($pdo, $surveyType)) {
        header("Location: $redirectUrl?error=survey_not_available");
        exit;
    }
}

/**
 * Get survey response count for active surveys
 */
function getActiveSurveyStats($pdo) {
    try {
        $activeSurveys = getActiveSurveyTypes($pdo);
        $stats = [];
        
        foreach ($activeSurveys as $surveyType) {
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT r.submission_id) as response_count
                FROM responses r
                INNER JOIN questions q ON r.question_id = q.id
                INNER JOIN categories c ON q.category_id = c.id
                WHERE c.type = ?
            ");
            $stmt->execute([$surveyType]);
            $stats[$surveyType] = $stmt->fetch()['response_count'] ?? 0;
        }
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error getting survey stats: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total questions count for active surveys
 */
function getActiveSurveyQuestionCounts($pdo) {
    try {
        $activeSurveys = getActiveSurveyTypes($pdo);
        $counts = [];
        
        foreach ($activeSurveys as $surveyType) {
            $stmt = $pdo->prepare("
                SELECT COUNT(q.id) as question_count
                FROM questions q
                INNER JOIN categories c ON q.category_id = c.id
                WHERE c.type = ?
            ");
            $stmt->execute([$surveyType]);
            $counts[$surveyType] = $stmt->fetch()['question_count'] ?? 0;
        }
        
        return $counts;
    } catch (PDOException $e) {
        error_log("Error getting question counts: " . $e->getMessage());
        return [];
    }
}
?>
