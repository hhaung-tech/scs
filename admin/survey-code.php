<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/survey-helper.php';
requireLogin();

// Get active survey types for dropdown
$activeSurveyTypes = getActiveSurveyTypes($pdo);

$message = '';
$error = '';
$currentPage = 'survey-codes';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_code':
            $type = $_POST['type'];
            $code = $_POST['code'];
            $expires_at = $_POST['expires_at'] ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;
            
            $stmt = $pdo->prepare("INSERT INTO survey_codes (type, code, expires_at) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$type, $code, $expires_at]);
                $message = "Survey code added successfully";
            } catch (PDOException $e) {
                $error = "Error adding survey code";
            }
            break;
            
        case 'deactivate_code':
            $code_id = $_POST['code_id'];
            $stmt = $pdo->prepare("UPDATE survey_codes SET active = false WHERE id = ?");
            try {
                $stmt->execute([$code_id]);
                $message = "Code deactivated successfully";
            } catch (PDOException $e) {
                $error = "Error deactivating code";
            }
            break;
        case 'edit_code':
            $code_id = $_POST['code_id'];
            $type = $_POST['type'];
            $code = $_POST['code'];
            $expires_at = $_POST['expires_at'] ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;
            
            $stmt = $pdo->prepare("UPDATE survey_codes SET type = ?, code = ?, expires_at = ? WHERE id = ?");
            try {
                $stmt->execute([$type, $code, $expires_at, $code_id]);
                $message = "Survey code updated successfully";
            } catch (PDOException $e) {
                $error = "Error updating survey code";
            }
            break;
    }
}

$stmt = $pdo->query("SELECT * FROM survey_codes WHERE active = true ORDER BY created_at DESC");
$codes = $stmt->fetchAll();
?>

<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar-dynamic.php'; ?>
<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
                <div class="box-content">
                    <h4 class="box-title">Survey Access Codes</h4>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <button type="button" class="btn btn-success btn-sm waves-effect waves-light" onclick="toggleAddForm()">
                        <i class="fa fa-plus-circle"></i> Add
                    </button>

                    <div id="addCodeForm" class="margin-top-20" style="display:none;">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_code">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Survey Type</label>
                                        <select name="type" class="form-control" required>
                                            <option value="">Select Type</option>
                                            <option value="student">Student</option>
					    <option value="alumni">Alumni</option>
						<option value="board">Board</option>
                                            <!-- <option value="guardian">Guardian</option>
                                            <option value="staff">Staff</option> -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Access Code</label>
                                        <input type="text" name="code" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Expires At (optional)</label>
                                        <input type="datetime-local" name="expires_at" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group margin-bottom-20">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
                                <button type="button" class="btn btn-default waves-effect waves-light" onclick="toggleAddForm()">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive margin-top-20">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>Created</th>
                                    <th>Expires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($codes as $code): ?>
                                <tr>
                                    <td><?php echo ucfirst($code['type']); ?></td>
                                    <td><?php echo htmlspecialchars($code['code']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($code['created_at'])); ?></td>
                                    <td>
                                        <?php if ($code['expires_at']): ?>
                                                <?php echo date('Y-m-d H:i', strtotime($code['expires_at'])); ?>
                                        <?php else: ?>
                                            <span class="notice notice-grey">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm waves-effect waves-light" onclick="editCode(<?php echo htmlspecialchars(json_encode($code)); ?>)">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="deactivate_code">
                                            <input type="hidden" name="code_id" value="<?php echo $code['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm waves-effect waves-light" onclick="return confirm('Are you sure you want to deactivate this code?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function editCode(codeData) {
    document.querySelector('select[name="type"]').value = codeData.type;
    document.querySelector('input[name="code"]').value = codeData.code;
    if (codeData.expires_at) {
        const expiryDate = new Date(codeData.expires_at);
        document.querySelector('input[name="expires_at"]').value = expiryDate.toISOString().slice(0, 16);
    } else {
        document.querySelector('input[name="expires_at"]').value = '';
    }
    
    // Update form for edit mode
    document.querySelector('input[name="action"]').value = 'edit_code';
    document.querySelector('form').insertAdjacentHTML('beforeend', 
        `<input type="hidden" name="code_id" value="${codeData.id}">`
    );
    
    // Change button text
    document.querySelector('form button[type="submit"]').textContent = 'Update';
    
    // Show form
    document.getElementById('addCodeForm').style.display = 'block';
}

// Add reset form function
function resetForm() {
    document.querySelector('form').reset();
    document.querySelector('input[name="action"]').value = 'add_code';
    const codeIdInput = document.querySelector('input[name="code_id"]');
    if (codeIdInput) codeIdInput.remove();
    document.querySelector('form button[type="submit"]').textContent = 'Save';
}

// Update the toggleAddForm function
function toggleAddForm() {
    const form = document.getElementById('addCodeForm');
    if (form.style.display === 'none') {
        resetForm();
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
