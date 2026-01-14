<?php
function getSurveyQuestions($pdo, $surveyType) {
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
        WHERE c.type = ?
        ORDER BY c.id, q.sort_order, q.id
    ");
    $stmt->execute([$surveyType]);
    return $stmt->fetchAll();
}

function organizeSurveyData($results) {
    $categories = [];
    foreach ($results as $row) {
        $categoryId = $row['category_id'];
        if (!isset($categories[$categoryId])) {
            $categories[$categoryId] = [
                'id' => $categoryId,
                'name' => $row['category_name'],
                'questions' => []
            ];
        }
        if ($row['question_id']) {
            $categories[$categoryId]['questions'][] = [
                'id' => $row['question_id'],
                'text' => $row['question_text'],
                'type' => $row['question_type'] ?? 'text',
                'options' => $row['options'],
                'question_id' => $row['question_id']
            ];
        }
    }
    return $categories;
}

function handleSurveySubmission($pdo, $answers) {
    $submission_id = uniqid('survey_', true);
    foreach ($answers as $questionId => $answer) {
        if (is_array($answer)) {
            $answer = json_encode($answer);
        } else {
            $answer = trim($answer);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO responses (question_id, answer, created_at, submission_id)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmt->execute([$questionId, $answer, $submission_id]);
    }
    return true;
}
?>