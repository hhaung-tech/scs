<?php
// Redirect to new Question Manager
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($scriptName, '/admin/');
$base = $pos !== false ? substr($scriptName, 0, $pos) : '';
$base = rtrim($base, '/');
header('Location: ' . $base . '/admin/manage-questions.php?type=staff');
exit;
?>