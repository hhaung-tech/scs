<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password, $first_name, $last_name]);
                $message = "User added successfully";
                break;
                
            case 'delete':
                $id = $_POST['user_id'];
                $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                $stmt->execute([$id]);
                $message = "User deleted successfully";
                break;
                case 'edit':
                    $id = $_POST['user_id'];
                    $username = $_POST['username'];
                    $email = $_POST['email'];
                    $first_name = $_POST['first_name'];
                    $last_name = $_POST['last_name'];
                    
                    try {
                        $sql = "UPDATE admin_users SET username = ?, email = ?, first_name = ?, last_name = ?";
                        $params = [$username, $email, $first_name, $last_name];
                        
                        if (!empty($_POST['password'])) {
                            $sql .= ", password = ?";
                            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        }
                        
                        $sql .= " WHERE id = ?";
                        $params[] = $id;
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        if ($stmt->rowCount() > 0) {
                            $message = "User updated successfully";
                        } else {
                            $message = "No changes were made";
                        }
                    } catch (PDOException $e) {
                        $message = "Error updating user: " . $e->getMessage();
                    }
                    break;
        }
    }
}
// Get all users
$stmt = $pdo->query("SELECT id, username, email, first_name, last_name, last_login FROM admin_users");
$users = $stmt->fetchAll();
?>

<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar-dynamic.php'; ?>

<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
                <div class="box-content">
                    <h4 class="box-title">User Management</h4>
                    
                    <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'Error') !== false || strpos($message, 'No changes') !== false ? 'alert-danger' : 'alert-success'; ?>">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>

                    <button type="button" class="btn btn-success btn-sm waves-effect waves-light" onclick="toggleAddForm()">
                        <i class="fa fa-plus-circle"></i> Add New User
                    </button>

                    <div id="addUserForm" class="margin-top-20" style="display:none;">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" required>
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
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <span class="notice notice-success">
                                                <?php echo date('Y-m-d H:i', strtotime($user['last_login'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="notice notice-danger">Never logged in</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm waves-effect waves-light" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm waves-effect waves-light" onclick="return confirm('Are you sure you want to delete this user?')">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto dismiss alerts using vanilla JavaScript
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 3000);
        });
    }
});

// Define functions using vanilla JavaScript
function toggleAddForm() {
    const form = document.getElementById('addUserForm');
    if (form) {
        if (form.style.display === 'none') {
            resetForm();
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
}

function resetForm() {
    const form = document.querySelector('#addUserForm form');
    if (form) {
        form.reset();
        const actionInput = form.querySelector('input[name="action"]');
        const passwordInput = form.querySelector('input[name="password"]');
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (actionInput) actionInput.value = 'add';
        if (passwordInput) passwordInput.required = true;
        if (submitButton) submitButton.textContent = 'Save';
        
        const userIdInput = form.querySelector('input[name="user_id"]');
        if (userIdInput) userIdInput.remove();
    }
}

function editUser(userData) {
    const form = document.querySelector('#addUserForm form');
    if (!form) return;

    const inputs = {
        username: form.querySelector('input[name="username"]'),
        email: form.querySelector('input[name="email"]'),
        first_name: form.querySelector('input[name="first_name"]'),
        last_name: form.querySelector('input[name="last_name"]'),
        password: form.querySelector('input[name="password"]'),
        action: form.querySelector('input[name="action"]')
    };

    // Update form fields
    Object.keys(inputs).forEach(key => {
        if (inputs[key] && key !== 'password' && key !== 'action') {
            inputs[key].value = userData[key] || '';
        }
    });

    // Handle password field
    if (inputs.password) {
        inputs.password.required = false;
        inputs.password.value = '';
    }

    // Update action and add user_id
    if (inputs.action) inputs.action.value = 'edit';
    
    // Remove existing user_id if any
    const existingUserId = form.querySelector('input[name="user_id"]');
    if (existingUserId) existingUserId.remove();
    
    // Add new user_id
    form.insertAdjacentHTML('beforeend', 
        `<input type="hidden" name="user_id" value="${userData.id}">`
    );

    // Update submit button
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) submitButton.textContent = 'Update User';

    // Show form
    const formContainer = document.getElementById('addUserForm');
    if (formContainer) formContainer.style.display = 'block';
}
</script>

<?php require_once '../includes/footer.php'; ?>