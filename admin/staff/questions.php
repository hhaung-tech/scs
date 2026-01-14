<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
requireLogin();
$currentPage = 'staff-questions';

// Get all staff categories and their questions
$stmt = $pdo->prepare("
    SELECT 
        c.id as category_id,
        c.name as category_name,
        q.id as question_id,
        q.question_text,
        q.question_type,
        q.options,
        q.sort_order
    FROM categories c
    LEFT JOIN questions q ON c.id = q.category_id
    WHERE c.type = 'staff'
    ORDER BY c.id ASC, q.sort_order ASC, q.id ASC
");
$stmt->execute();
$results = $stmt->fetchAll();
?>
<?php include '../../includes/questions-common.php'; ?>