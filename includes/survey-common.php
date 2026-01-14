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

function parseQuestionOptions($options) {
    if ($options === null) {
        return [];
    }

    $options = trim((string)$options);
    if ($options === '') {
        return [];
    }

    $decoded = json_decode($options, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    $parts = preg_split('/\s*(?:,|;|\||\r\n|\r|\n)\s*/', $options);
    $parts = array_values(array_filter(array_map('trim', $parts), function ($v) {
        return $v !== '';
    }));
    return $parts;
}

function normalizeQuestionType($type) {
    $t = strtolower(trim((string)$type));
    if ($t === '') {
        return 'text';
    }

    if (in_array($t, ['multiple_checkbox', 'multiple_checkboxes', 'checkboxes', 'choose_multiple', 'choose_multiple_checkbox', 'multi_checkbox'], true)) {
        return 'checkbox';
    }

    if (in_array($t, ['dropdown', 'drop-down', 'select'], true)) {
        return 'drop_down';
    }

    if (in_array($t, ['mcq', 'radio', 'multiple-choice'], true)) {
        return 'multiple_choice';
    }

    if (in_array($t, ['likert', 'likertscale', 'likert-scale'], true)) {
        return 'likert_scale';
    }

    if (in_array($t, ['textarea', 'text_response', 'free_text', 'open_ended'], true)) {
        return 'text';
    }

    return $t;
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
                'type' => normalizeQuestionType($row['question_type'] ?? 'text'),
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