<?php
// Helper function to determine base path if needed, adjust if your paths are absolute
function basePath($path = '') {
    $base = getenv('APP_BASE_PATH');
    if ($base === false || trim((string)$base) === '') {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $knownSegments = [
            '/admin/',
            '/student/',
            '/guardian/',
            '/staff/',
            '/board/',
            '/alumni/',
            '/assets/',
            '/includes/',
            '/config/',
        ];

        $base = '';
        foreach ($knownSegments as $seg) {
            $pos = strpos($scriptName, $seg);
            if ($pos !== false) {
                $base = substr($scriptName, 0, $pos);
                break;
            }
        }

        if ($base === '') {
            $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            $base = $dir === '' || $dir === '.' ? '' : $dir;
        }
    }
    $base = trim((string)$base);
    $base = $base === '' ? '' : '/' . trim($base, '/');
    $path = ltrim((string)$path, '/');
    return $base . '/' . $path;
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
    <link rel="icon" type="image/x-icon" href="<?php echo basePath('assets/images/isy_logo.png'); ?>">
    <link rel="stylesheet" href="<?php echo basePath('assets/styles/style.min.css'); ?>">
    <!-- Main Styles -->
    <!-- mCustomScrollbar -->
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/mCustomScrollbar/jquery.mCustomScrollbar.min.css'); ?>" />

    <!-- Waves Effect -->
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/waves/waves.min.css'); ?>" />

    <!-- Sweet Alert -->
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/sweet-alert/sweetalert.css'); ?>" />

    <!-- Percent Circle -->
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/percircle/css/percircle.css'); ?>" />

    <!-- Chartist Chart -->
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/chart/chartist/chartist.min.css'); ?>" />

    <!-- FullCalendar -->
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/fullcalendar/fullcalendar.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo basePath('assets/plugin/fullcalendar/fullcalendar.print.css'); ?>" media="print" />
    <!-- Dark Themes -->
    <link rel="stylesheet" href="<?php echo basePath('assets/styles/style-dark.min.css'); ?>" />
    <?php
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        echo '<link rel="stylesheet" href="' . basePath('assets/styles/isy-admin-theme.css') . '" />';
    }
    ?>
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
