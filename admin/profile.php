<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

$message = '';
$error = '';

// Get current user's data
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update profile information
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("UPDATE admin_users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $email, $_SESSION['admin_id']]);
    $message = "Profile updated successfully";
    
    // Handle password change if provided
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                $message = "Profile and password updated successfully";
            } else {
                $error = "New passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
    
    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();
}
?>

<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div id="wrapper">
    <div class="main-content">
        <div class="row small-spacing">
            <div class="col-xs-12">
                <div class="box-content card white">
                    <h4 class="box-title">Profile Management</h4>
                    <!-- Alert messages -->
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
                    
                    <!-- Profile Form -->
                    <div class="card-content">
                        <form class="form-horizontal" method="POST">
                            <div class="form-group">
                                <label for="first_name" class="col-sm-2 control-label">First Name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="last_name" class="col-sm-2 control-label">Last Name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email" class="col-sm-2 control-label">Email</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group margin-top-30">
                                <label class="col-sm-2 control-label">Change Password</label>
                                <div class="col-sm-10">
                                    <input type="password" name="current_password" class="form-control margin-bottom-10" placeholder="Current Password">
                                    <input type="password" name="new_password" class="form-control margin-bottom-10" placeholder="New Password">
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password">
                                    <p class="help-block small">Leave password fields empty if you don't want to change it.</p>
                                </div>
                            </div>

                            <div class="form-group margin-top-20">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success btn-sm waves-effect waves-light">
                                        <i class="fa fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    $(alert).fadeOut(300, function() { 
                        if (this.parentNode) this.remove(); 
                    });
                }
            }, 3000);
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>