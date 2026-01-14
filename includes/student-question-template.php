<?php
// This template receives the question data in the $questionData variable
// $questionData includes: id, number, text, type, options, teacher_context

if (!isset($questionData) || !is_array($questionData)) {
    echo "<p class='text-danger'>Error: Question data not provided to template.</p>";
    return; // Stop if data is missing
}

// Construct the input name attribute
// Format: answers[CONTEXT][QUESTION_ID]
// CONTEXT will be 'core' or the teacher_user_dcid
$inputNameBase = "answers[" . ($questionData['teacher_context'] ?? 'core') . "][" . $questionData['id'] . "]";

// Add '[]' for checkboxes as they can have multiple values
$inputName = ($questionData['type'] === 'checkbox') ? $inputNameBase . "[]" : $inputNameBase;

// Generate unique ID prefix for this question that includes teacher context
$idPrefix = isset($questionData['field_prefix']) ? $questionData['field_prefix'] : '';

// Assume most questions are required unless specified otherwise in DB (add logic if needed)
$isRequired = true;

?>

<div class="question-item mb-4 p-3 bg-light border rounded" data-question-id="<?php echo $questionData['id']; ?>">
    <p class="mb-2"><strong><?php echo $questionData['number'] . '. ' . htmlspecialchars($questionData['text']); ?></strong></p>

    <?php switch($questionData['type']):

        case 'likert_scale':
            $scaleOptions = json_decode($questionData['options'], true);
            if (is_array($scaleOptions)): ?>
                <div class="likert-scale">
                    <?php foreach ($scaleOptions as $index => $optionText):
                        $optionValue = $optionText; // Or use index+1 if saving numeric value: $index + 1;
                        $optionId = $idPrefix . "q_" . $questionData['id'] . "_t_" . ($questionData['teacher_context'] ?? 'core') . "_opt_" . $index;
                    ?>
                        <label class="likert-option">
                            <input type="radio"
                                id="<?php echo $optionId; ?>"
                                name="<?php echo $inputName; ?>"
                                value="<?php echo htmlspecialchars($optionValue); ?>"
                                <?php if ($isRequired) echo 'required'; ?>
                            >
                            <div class="option-circle"></div>
                            <div class="option-text"><?php echo htmlspecialchars($optionText); ?></div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if ($isRequired): ?>
                     <small class="text-danger error-message" style="display: none;">Please select an option.</small>
                <?php endif; ?>
            <?php else: ?>
                 <p class="text-warning">Warning: Invalid Likert scale options for question ID <?php echo $questionData['id']; ?></p>
            <?php endif; ?>
            <?php break; ?>

        <?php case 'drop_down':
            $options = explode(',', $questionData['options']);
             if (!empty($options)): ?>
                <select class="form-control" name="<?php echo $inputName; ?>" <?php if ($isRequired) echo 'required'; ?>>
                    <option value="">Select an option</option>
                    <?php foreach ($options as $option):
                        $trimmedOption = trim($option);
                    ?>
                        <option value="<?php echo htmlspecialchars($trimmedOption); ?>">
                            <?php echo htmlspecialchars($trimmedOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                 <?php if ($isRequired): ?>
                     <small class="text-danger error-message" style="display: none;">Please select an option.</small>
                <?php endif; ?>
            <?php else: ?>
                 <p class="text-warning">Warning: Invalid dropdown options for question ID <?php echo $questionData['id']; ?></p>
            <?php endif; ?>
            <?php break; ?>

        <?php case 'checkbox':
            $options = explode(',', $questionData['options']);
             if (!empty($options)): ?>
                <div class="checkbox-group">
                    <?php foreach ($options as $index => $option):
                        $trimmedOption = trim($option);
                         $optionId = $idPrefix . "q_" . $questionData['id'] . "_t_" . ($questionData['teacher_context'] ?? 'core') . "_chk_" . $index;
                    ?>
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="<?php echo $optionId; ?>"
                                name="<?php echo $inputName; ?>"
                                value="<?php echo htmlspecialchars($trimmedOption); ?>"
                                <?php // Required logic for checkboxes is more complex (e.g., require at least one) - omit for now ?>
                            >
                            <label class="form-check-label" for="<?php echo $optionId; ?>">
                                <?php echo htmlspecialchars($trimmedOption); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php // Add required validation message if needed ?>
             <?php else: ?>
                 <p class="text-warning">Warning: Invalid checkbox options for question ID <?php echo $questionData['id']; ?></p>
            <?php endif; ?>
            <?php break; ?>

        <?php case 'text': default: // Treat unknown types as text ?>
            <textarea class="form-control"
                name="<?php echo $inputName; ?>"
                <?php if ($isRequired) echo 'required'; ?>
                rows="3"></textarea>
             <?php if ($isRequired): ?>
                     <small class="text-danger error-message" style="display: none;">Please provide an answer.</small>
             <?php endif; ?>
            <?php break; ?>

    <?php endswitch; ?>
</div>
<style>
    .category-section {
        margin-bottom: 0.5rem !important; /* Reduce space below category */
        padding: 0.75rem !important; /* Reduce padding inside category box */
    }
    
    .question-item {
        margin-bottom: 0.5rem !important; /* Reduce space below question */
        padding: 0.75rem !important; /* Reduce padding inside question box */
        background-color: #f8f9fa !important;
    }
    
    #surveyTabContent .tab-pane {
        padding-top: 1rem; /* Adjust padding at the top of the tab content */
    }
    
    /* Likert Scale Radio Button Styling */
    .likert-scale {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        margin: 15px 0;
    }
    
    .likert-option {
        flex: 1;
        min-width: 80px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        position: relative;
    }
    
    .likert-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    
    .option-circle {
        height: 24px;
        width: 24px;
        border-radius: 50%;
        border: 2px solid #ccc;
        background-color: #fff;
        margin-bottom: 5px;
        transition: all 0.2s ease;
    }
    
    .likert-option input[type="radio"]:checked + .option-circle {
        border-color: #ddb41f;
        background-color: #fff;
        box-shadow: inset 0 0 0 6px #ddb41f;
    }
    
    .option-text {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }
    
    .likert-option:hover .option-circle {
        border-color: #ddb41f;
    }
</style>