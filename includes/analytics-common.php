<?php
function getAnalyticsData($pdo, $surveyType) {
    try {
        $countStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT submission_id) as total_count
            FROM responses r
            INNER JOIN questions q ON r.question_id = q.id
            INNER JOIN categories c ON q.category_id = c.id
            WHERE c.type = :type
        ");
        $countStmt->bindValue(':type', $surveyType, PDO::PARAM_STR);
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total_count'];

        $stmt = $pdo->prepare("
        SELECT 
            c.name as category_name,
            q.id as question_id,
            q.question_text,
            q.question_type,
            q.options,
            COUNT(DISTINCT r.id) as response_count,
            string_agg(r.answer, '|||' ORDER BY r.answer ASC) as answers
        FROM categories c
        INNER JOIN questions q ON c.id = q.category_id
        LEFT JOIN responses r ON q.id = r.question_id
        WHERE c.type = :type
        GROUP BY c.name, q.id, q.question_text, q.question_type, q.options
        ORDER BY c.name, q.sort_order
    "); 
        $stmt->bindValue(':type', $surveyType, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['results' => $results, 'total_count' => $totalCount];
    } catch (PDOException $e) {
        // Enhanced error logging
        error_log("Database Error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        return ['results' => [], 'total_count' => 0];
    }
}

// New function to get teacher-specific analytics data for student surveys
function getTeacherAnalyticsData($pdo, $teacherId = null) {
    try {
        // Base count query to get total responses 
        $countStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT submission_id) as total_count
            FROM responses r
            INNER JOIN questions q ON r.question_id = q.id
            INNER JOIN categories c ON q.category_id = c.id
            WHERE c.type = 'student' 
            AND q.feedback_type = 'teacher'
            " . ($teacherId ? "AND r.teacher_id = :teacher_id" : "") . "
        ");
        
        if ($teacherId) {
            $countStmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_STR);
        }
        
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total_count'];
        
        // Main query to get teacher-specific feedback
        $stmt = $pdo->prepare("
            SELECT 
                c.name as category_name,
                q.id as question_id,
                q.question_text,
                q.question_type,
                q.options,
                COUNT(DISTINCT r.id) as response_count,
                string_agg(r.answer, '|||' ORDER BY r.answer ASC) as answers
            FROM categories c
            INNER JOIN questions q ON c.id = q.category_id
            LEFT JOIN responses r ON q.id = r.question_id
            WHERE c.type = 'student'
            AND q.feedback_type = 'teacher'
            " . ($teacherId ? "AND r.teacher_id = :teacher_id" : "") . "
            GROUP BY c.name, q.id, q.question_text, q.question_type, q.options
            ORDER BY c.name, q.sort_order
        ");
        
        if ($teacherId) {
            $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['results' => $results, 'total_count' => $totalCount];
    } catch (PDOException $e) {
        // Enhanced error logging
        error_log("Teacher Analytics Error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        return ['results' => [], 'total_count' => 0];
    }
}

// Get list of teachers with survey responses
function getTeachersWithResponses($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT 
                t.teacher_user_dcid,
                t.teacher_first_name || ' ' || t.teacher_last_name as teacher_name,
                COUNT(DISTINCT r.submission_id) as response_count
            FROM teachers t
            JOIN responses r ON r.teacher_id = t.teacher_user_dcid
            JOIN questions q ON r.question_id = q.id
            WHERE q.feedback_type = 'teacher'
            GROUP BY t.teacher_user_dcid, teacher_name
            ORDER BY teacher_name
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching teachers with responses: " . $e->getMessage());
        return [];
    }
}

function processAnalyticsData($queryResult) {
    if (empty($queryResult['results'])) {
        return ['statistics' => [], 'total_responses' => 0];
    }

    $statistics = [];
    $results = $queryResult['results'];
    $total_responses = $queryResult['total_count'];

    foreach ($results as $row) {
        $category = $row['category_name'];
        if (!isset($statistics[$category])) {
            $statistics[$category] = [];
        }

        $questionData = [
            'question_id' => $row['question_id'],
            'question' => $row['question_text'],
            'type' => $row['question_type']
        ];
        
        // Add teacher name if it exists
        if (isset($row['teacher_name'])) {
            $questionData['teacher_name'] = $row['teacher_name'];
        }

        if ($row['question_type'] === 'drop_down' || $row['question_type'] === 'checkbox') {
            $options = [];
            if (!empty($row['options'])) {
                $decodedOptions = json_decode($row['options'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOptions)) {
                    $options = $decodedOptions;
                } else {
                    // Fallback: try parsing as comma-separated string
                    $options = array_map('trim', explode(',', $row['options']));
                }
            }
            
            // Initialize data array with zeros
            $data = array_fill_keys($options, 0);
            
            // Process checkbox answers
            if (!empty($row['answers'])) {
                $answers = explode('|||', $row['answers']);
                foreach ($answers as $answer) {
                    $answer = trim($answer);
                    if (!empty($answer)) {
                        if ($row['question_type'] === 'drop_down') {
                            // For drop_down, answer is a single value
                            if (isset($data[$answer])) {
                                $data[$answer]++;
                            }
                        } else {
                            // For checkbox, answer is JSON array
                            $selections = json_decode($answer, true);
                            if (is_array($selections)) {
                                foreach ($selections as $selection) {
                                    if (isset($data[$selection])) {
                                        $data[$selection]++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        
            $questionData['options'] = $options;
            $questionData['data'] = array_values($data);
            $questionData['labels'] = array_keys($data);
        
        }elseif ($row['question_type'] === 'text') {
            $answers = !empty($row['answers']) ? explode('|||', $row['answers']) : [];
            $answers = array_filter(array_map('trim', $answers), function($answer) {
                return !empty($answer);
            });
            $questionData['answers'] = array_values($answers);   

        } else {
            // Get options based on question type
            if ($row['question_type'] === 'likert_scale') {

                $definedOptions = json_decode($row['options'] ?? '', true);
                
                if (is_array($definedOptions)) {
                    $options = $definedOptions;
                } else {
                    // Get the likert scale type from options
                    $scaleType = trim($row['options'] ?? '');
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
                            // For custom scale, get options from the question settings
                            $customOptions = json_decode($row['options'], true);
                            if (is_array($customOptions) && !empty($customOptions)) {
                                $options = $customOptions;
                            } else {
                                $options = ['1', '2', '3', '4', '5']; // fallback
                            }
                            break;
                        default:
                            $options = ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'];
                    }
                }
            } else {
                $options = !empty($row['options']) ? json_decode($row['options'], true) : [];
                $options = is_array($options) ? $options : [];
            }

            // Initialize data array with zeros
            $data = array_fill_keys($options, 0);
            
            // Process answers
            if (!empty($row['answers'])) {
                $answers = explode('|||', $row['answers']);
                foreach ($answers as $answer) {
                    $answer = trim($answer);
                    if (!empty($answer)) {
                        if ($row['question_type'] === 'likert_scale') {
                            if (is_numeric($answer) && $answer > 0 && $answer <= count($options)) {
                                $mappedAnswer = $options[$answer - 1];
                                $data[$mappedAnswer]++;
                            } elseif (isset($data[$answer])) {
                                $data[$answer]++;
                            }
                        } elseif ($row['question_type'] === 'drop_down' || $row['question_type'] === 'checkbox') {
                            $selections = json_decode($answer, true);
                            if (is_array($selections)) {
                                foreach ($selections as $selection) {
                                    if (isset($data[$selection])) {
                                        $data[$selection]++;
                                    }
                                }
                            }
                        } else {
                            if (isset($data[$answer])) {
                                $data[$answer]++;
                            }
                        }
                    }
                }
            }

            $questionData['options'] = $options;
            $questionData['data'] = array_values($data);
            $questionData['labels'] = array_keys($data);
        }
        $statistics[$category][] = $questionData;
    }

    return [
        'statistics' => $statistics,
        'total_responses' => $total_responses
    ];
}