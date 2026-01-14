<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

$message = '';
$error = '';
$currentPage = 'survey-settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_settings':
            try {
                // Get all survey types
                $stmt = $pdo->query("SELECT survey_type FROM survey_settings");
                $surveyTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Update each survey setting
                foreach ($surveyTypes as $surveyType) {
                    $isActive = isset($_POST['active_surveys']) && in_array($surveyType, $_POST['active_surveys']);
                    $displayOrder = (int)($_POST['order_' . $surveyType] ?? 0);
                    
                    $updateStmt = $pdo->prepare("
                        UPDATE survey_settings 
                        SET is_active = ?, display_order = ?, updated_at = CURRENT_TIMESTAMP 
                        WHERE survey_type = ?
                    ");
                    // Convert boolean to string for PostgreSQL
                    $updateStmt->execute([$isActive ? 'true' : 'false', $displayOrder, $surveyType]);
                }
                
                $message = "Survey settings updated successfully!";
            } catch (PDOException $e) {
                $error = "Error updating survey settings: " . $e->getMessage();
            }
            break;
            
        case 'update_display':
            $surveyType = $_POST['survey_type'] ?? '';
            $displayName = $_POST['display_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $iconClass = $_POST['icon_class'] ?? '';
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE survey_settings 
                    SET display_name = ?, description = ?, icon_class = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE survey_type = ?
                ");
                $stmt->execute([$displayName, $description, $iconClass, $surveyType]);
                $message = "Display settings updated successfully!";
            } catch (PDOException $e) {
                $error = "Error updating display settings: " . $e->getMessage();
            }
            break;
    }
}

// Get current survey settings
$stmt = $pdo->query("SELECT * FROM survey_settings ORDER BY display_order, survey_type");
$surveySettings = $stmt->fetchAll();

// Get survey statistics
$stats = [];
foreach ($surveySettings as $setting) {
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT r.submission_id) as response_count
        FROM responses r
        INNER JOIN questions q ON r.question_id = q.id
        INNER JOIN categories c ON q.category_id = c.id
        WHERE c.type = ?
    ");
    $countStmt->execute([$setting['survey_type']]);
    $stats[$setting['survey_type']] = $countStmt->fetch()['response_count'] ?? 0;
}
?>

<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar-dynamic.php'; ?>

<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
                <div class="box-content">
                    <h4 class="box-title">Survey Management Settings</h4>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Survey Activation Settings -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h5>Survey Activation Control</h5>
                            <p class="text-muted">Enable or disable surveys and set their display order</p>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Survey Type</th>
                                                <th>Status</th>
                                                <th>Display Order</th>
                                                <th>Responses</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($surveySettings as $setting): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($setting['display_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($setting['survey_type']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="checkbox">
                                                            <input type="checkbox" 
                                                                   id="active_<?php echo $setting['survey_type']; ?>" 
                                                                   name="active_surveys[]" 
                                                                   value="<?php echo $setting['survey_type']; ?>"
                                                                   <?php echo $setting['is_active'] ? 'checked' : ''; ?>>
                                                            <label for="active_<?php echo $setting['survey_type']; ?>">
                                                                <span class="label label-<?php echo $setting['is_active'] ? 'success' : 'default'; ?>">
                                                                    <?php echo $setting['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               name="order_<?php echo $setting['survey_type']; ?>" 
                                                               value="<?php echo $setting['display_order']; ?>" 
                                                               class="form-control" 
                                                               style="width: 80px;" 
                                                               min="0" max="100">
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            <?php echo $stats[$setting['survey_type']]; ?> responses
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-info" 
                                                                onclick="editDisplay('<?php echo $setting['survey_type']; ?>')">
                                                            <i class="fa fa-edit"></i> Edit Display
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Update Survey Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Display Modal -->
<div class="modal fade" id="editDisplayModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_display">
                <input type="hidden" name="survey_type" id="edit_survey_type">
                
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Edit Display Settings</h4>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_display_name">Display Name</label>
                        <input type="text" class="form-control" id="edit_display_name" name="display_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_icon_class">Icon Class (Font Awesome)</label>
                        <input type="text" class="form-control" id="edit_icon_class" name="icon_class" placeholder="fa-file-text">
                        <small class="text-muted">Example: fa-child, fa-graduation-cap, fa-industry</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editDisplay(surveyType) {
    // Find the survey data
    const surveys = <?php echo json_encode($surveySettings); ?>;
    const survey = surveys.find(s => s.survey_type === surveyType);
    
    if (survey) {
        document.getElementById('edit_survey_type').value = survey.survey_type;
        document.getElementById('edit_display_name').value = survey.display_name;
        document.getElementById('edit_icon_class').value = survey.icon_class;
        document.getElementById('edit_description').value = survey.description;
        
        $('#editDisplayModal').modal('show');
    }
}

// Update status labels when checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="active_surveys[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const label = this.parentNode.querySelector('.label');
            if (this.checked) {
                label.textContent = 'Active';
                label.className = 'label label-success';
            } else {
                label.textContent = 'Inactive';
                label.className = 'label label-default';
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
