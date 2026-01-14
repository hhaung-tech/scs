<?php
// generate-ai.php
header('Content-Type: application/json');

// Include your DB config & AI helpers
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ai-insights.php';
require_once __DIR__ . '/../includes/analytics-common-ai.php';


// If you require login
// require_once '../../includes/auth.php';
// requireLogin();

$questionId = isset($_GET['questionId']) ? (int) $_GET['questionId'] : 0;
$surveyType = isset($_GET['surveyType']) ? trim($_GET['surveyType']) : 'alumni';

$response = [
    'success' => false,
    'insight' => ''
];

if ($questionId <= 0) {
    echo json_encode($response);
    exit;
}

try {
    // 1. Get all questions data for this survey (or you can do a narrower DB query).
    $data = getAnalyticsData($pdo, $surveyType);
    if (empty($data['results'])) {
        throw new Exception("No results for type: $surveyType");
    }

    // 2. Find the single question we need
    $row = null;
    foreach ($data['results'] as $r) {
        if ((int)$r['question_id'] === $questionId) {
            $row = $r;
            break;
        }
    }
    if (!$row) {
        throw new Exception("Question ID $questionId not found under survey type: $surveyType");
    }

    // 3. Build minimal $questionData to pass to AI
    $questionData = [
        'question_id' => $row['question_id'],
        'question'    => $row['question_text'],
        'type'        => $row['question_type']
    ];

    if (in_array($row['question_type'], ['drop_down', 'checkbox', 'likert_scale'])) {
        // We build distribution
        $options = [];
        if ($row['question_type'] === 'drop_down' || $row['question_type'] === 'checkbox') {
            if (!empty($row['options'])) {
                $decodedOptions = json_decode($row['options'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOptions)) {
                    $options = $decodedOptions;
                } else {
                    $options = array_map('trim', explode(',', $row['options']));
                }
            }
        } elseif ($row['question_type'] === 'likert_scale') {
            $definedOptions = json_decode($row['options'], true);
            if (is_array($definedOptions)) {
                $options = $definedOptions;
            } else {
                $scaleType = trim($row['options']);
                switch ($scaleType) {
                    case 'agreement':
                        $options = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
                        break;
                    case 'numeric':
                        $options = ['1', '2', '3', '4', '5'];
                        break;
                    case 'frequency':
                        $options = ['Never', 'Rarely', 'Sometimes', 'Often', 'Always'];
                        break;
                    case 'custom':
                        $customOptions = json_decode($row['options'], true);
                        if (is_array($customOptions) && !empty($customOptions)) {
                            $options = $customOptions;
                        } else {
                            $options = ['1', '2', '3', '4', '5'];
                        }
                        break;
                    default:
                        $options = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
                }
            }
        }
        $dataArr = array_fill_keys($options, 0);

        // Tally answers
        if (!empty($row['answers'])) {
            $answersArr = explode('|||', $row['answers']);
            foreach ($answersArr as $answer) {
                $answer = trim($answer);
                if (empty($answer)) continue;

                if ($row['question_type'] === 'likert_scale') {
                    if (is_numeric($answer) && $answer > 0 && $answer <= count($options)) {
                        $mapped = $options[$answer - 1];
                        $dataArr[$mapped]++;
                    } elseif (isset($dataArr[$answer])) {
                        $dataArr[$answer]++;
                    }
                } elseif ($row['question_type'] === 'drop_down') {
                    if (isset($dataArr[$answer])) {
                        $dataArr[$answer]++;
                    }
                } elseif ($row['question_type'] === 'checkbox') {
                    $selections = json_decode($answer, true);
                    if (is_array($selections)) {
                        foreach ($selections as $s) {
                            if (isset($dataArr[$s])) {
                                $dataArr[$s]++;
                            }
                        }
                    }
                }
            }
        }

        $questionData['labels'] = array_keys($dataArr);
        $questionData['data']   = array_values($dataArr);
    }
    elseif ($row['question_type'] === 'text') {
        $answers = !empty($row['answers']) ? explode('|||', $row['answers']) : [];
        $answers = array_filter(array_map('trim', $answers));
        $questionData['answers'] = array_values($answers);
    }

    // 4. Call AI
    $insight = generateAIInsights($questionData);

    // 5. Return JSON
    $response['success'] = true;
    $response['insight'] = $insight;

} catch (Exception $ex) {
    error_log("AI Generation Error: " . $ex->getMessage());
    $response['success'] = false;
}

echo json_encode($response);
exit;
