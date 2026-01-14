<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/analytics-common.php';
requireLogin();

// Set page configuration
$currentPage = 'alumni-analytics';
$surveyType = 'alumni';

// Initialize variables
$statistics = [];
$total_responses = 0;

try {
    // Verify database connection
    if (!$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        throw new PDOException("Database connection not established");
    }

    // Get analytics data
    $results = getAnalyticsData($pdo, $surveyType);
    if (!$results) {
        throw new Exception("No data retrieved for " . $surveyType . " survey");
    }

    // Process the data
    $data = processAnalyticsData($results);
    if (!isset($data['statistics']) || !isset($data['total_responses'])) {
        throw new Exception("Invalid data structure returned from processing");
    }

    // Set the processed data
    $statistics = $data['statistics'];
    $total_responses = $data['total_responses'];

    // Include template files
    require_once '../../includes/header.php';
    require_once '../../includes/sidebar.php';

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Analytics Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die("An error occurred while processing the data: " . $e->getMessage());
}

require_once '../../includes/analytics-template.php';
require_once '../../includes/footer.php'; 
?>

