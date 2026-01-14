<?php
if (isset($_GET['survey'])) {
    switch ($_GET['survey']) {
        case 'student':
            include 'student/survey.php';
            break;
        case 'guardian':
            include 'guardian/survey.php';
            break;
        case 'staff':
            include 'staff/survey.php';
            break;
        case 'board':
            include 'board/survey.php';
            break;
        case 'alumni':
            include 'alumni/survey.php';
            break;
        case 'teacher':
            include 'teacher/survey.php';
            break;
        default:
            include 'includes/welcome-page-dynamic.php';
    }
} else {
    include 'includes/welcome-page-dynamic.php';
}
?>
