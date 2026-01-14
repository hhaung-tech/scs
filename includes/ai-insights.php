<?php

function generateAIInsights($questionData, $maxTokens = 1000) {
    
    // Load environment configuration
    $envFile = __DIR__ . '/../config/env.php';
    if (file_exists($envFile)) {
        require_once $envFile;
    }
    
    // Get OpenAI API key from environment variable or config
    $openaiApiKey = getenv('OPENAI_API_KEY') ?: 'your-openai-api-key-here';

    $prompt = buildPrompt($questionData);

    $postFields = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful AI that provides summary insights from survey data.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => $maxTokens,
        'temperature' => 0.7
    ];

    // cURL to OpenAI
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openaiApiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute API call
    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        error_log("AI Insights cURL error: " . $error);
        curl_close($ch);
        return '';
    }

    curl_close($ch);
    $responseData = json_decode($response, true);

    // Extract the AI-generated answer
    if (isset($responseData['choices'][0]['message']['content'])) {
        return trim($responseData['choices'][0]['message']['content']);
    }

    // Fallback if no content returned
    return '';
}

/**
 * A helper function to build a prompt from question data.
 * Adjust the level of detail or format as needed.
 */
function buildPrompt($questionData) {

    $questionText = $questionData['question'] ?? 'N/A';
    $questionType = $questionData['type'] ?? 'unknown';

    // Construct basic summary of distribution or text answers
    $details = '';
    if (in_array($questionType, ['drop_down', 'checkbox', 'likert_scale'])) {
        // Summarize distributions
        if (isset($questionData['labels'], $questionData['data'])) {
            $details .= "Here is the distribution of answers:\n";
            foreach ($questionData['labels'] as $index => $label) {
                $count = $questionData['data'][$index] ?? 0;
                $details .= "- $label: $count\n";
            }
        }
    } elseif ($questionType === 'text' && !empty($questionData['answers'])) {
        // Summarize text answers
        // For large sets of text answers, you might only send a subset or a combined summary
        $allAnswers = $questionData['answers'];
        // You might limit to a certain number of answers or characters:
        $limitedAnswers = array_slice($allAnswers, 0, 5); // only first 5 to reduce token usage
        $details .= "Here are a few sample open-ended responses:\n";
        foreach ($limitedAnswers as $answer) {
            $details .= "- $answer\n";
        }
    }

    // Build final prompt
    $prompt = <<<EOT
Question: "{$questionText}"
Type: {$questionType}

{$details}

Please provide a detailed analysis of the survey responses, including the following: 1. **Trends and Patterns**: Identify any significant trends or patterns in the responses (e.g., majority agreement, disagreements, neutral responses). 2. **Contextualization**: Provide context for the results, such as what these trends might suggest about the respondents' perspectives and how this compares to typical survey results. 3. **Actionable Insights**: Suggest potential next steps or actions based on the findings, such as areas for improvement or further investigation. 4. **Emotional or Sentiment Insights**: Consider any emotional or sentiment-related factors that may explain the respondents' choices (e.g., dissatisfaction, ambivalence). 5. **Connection to Goals**: Relate the insights back to the objectives of the survey or the broader goals (e.g., improving curriculum, aligning educational programs with real-world applications). Please provide a detailed analysis of the survey responses in valid HTML format. - Do not wrap the answer in code blocks (no triple backticks). - Wrap each main heading ("###") with <strong><u>...</u></strong>. - Convert any **bold text** to <strong>bold text</strong>. - The output should be valid HTML so it can be displayed on a webpage directly.
EOT;

    return $prompt;
}
?>
