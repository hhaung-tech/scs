<?php
// Helper function to determine base path if needed, adjust if your paths are absolute
function basePath($path = '') {
    // Simple example, adjust based on your actual URL structure or config
    $base = rtrim('/isy_scs_ai', '/'); // Assuming this is your base web path
    return $base . '/' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>School Climate Survey</title>
    <link rel="icon" type="image/x-icon" href="isy_scs_ai/isy_scs_ai/assets/images/isy_logo.png">
    <link rel="stylesheet" href="/isy_scs_ai/assets/styles/style.min.css">
    <!-- Main Styles -->
    <!-- mCustomScrollbar -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/mCustomScrollbar/jquery.mCustomScrollbar.min.css" />

    <!-- Waves Effect -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/waves/waves.min.css" />

    <!-- Sweet Alert -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/sweet-alert/sweetalert.css" />

    <!-- Percent Circle -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/percircle/css/percircle.css" />

    <!-- Chartist Chart -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/chart/chartist/chartist.min.css" />

    <!-- FullCalendar -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/fullcalendar/fullcalendar.min.css" />
    <link rel="stylesheet" href="/isy_scs_ai/assets/plugin/fullcalendar/fullcalendar.print.css" media="print" />
    <!-- Dark Themes -->
    <link rel="stylesheet" href="/isy_scs_ai/assets/styles/style-dark.min.css" />
    <?php
    // Conditionally load survey CSS based on the current page
    if (isset($currentPage) && $currentPage === 'student-survey') {
        echo '<link rel="stylesheet" href="' . basePath('assets/styles/student-survey.css') . '" />';
    } else {
        // Load default survey CSS for other pages (guardian, staff, etc.)
        echo '<link rel="stylesheet" href="' . basePath('assets/styles/survey.css') . '" />';
    }
    ?>
    <script>
    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</head>
<body>
