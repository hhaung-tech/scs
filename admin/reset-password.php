<?php
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';
$validToken = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Check if the token exists and is still valid
        $stmt = $pdo->prepare("SELECT reset_token, reset_token_expiry FROM admin_users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $tokenInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tokenInfo) {
            // Token exists, now check if it's still valid
            $expiryTime = strtotime($tokenInfo['reset_token_expiry']);
            if ($expiryTime > time()) {
                $validToken = true;
            } else {
                $error = "Reset token has expired. Please request a new password reset.";
            }
        } else {
            $error = "Invalid reset token. Please request a new password reset.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "No reset token provided.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password === $confirm_password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $stmt = $pdo->prepare("
            UPDATE admin_users 
            SET password = ?, 
                reset_token = NULL, 
                reset_token_expiry = NULL 
            WHERE reset_token = ?
        ");
        
        if ($stmt->execute([$hashedPassword, $token])) {
            $message = "Password has been reset successfully. You can now <a href='login.php'>login</a>.";
            $validToken = false; // Hide the form after successful reset
        } else {
            $error = "Error resetting password. Please try again.";
        }
    } else {
        $error = "Passwords do not match.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div id="single-wrapper">
    <form action="" method="POST" class="frm-single">
        <div class="inside">
            <div class="title"><strong>ISY</strong>Admin</div>
            <div class="frm-title">Reset Password</div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($validToken): ?>
                <div class="frm-input">
                    <input type="password" name="password" placeholder="New Password" class="frm-inp" required minlength="8">
                    <i class="fa fa-lock frm-ico"></i>
                </div>
                <div class="frm-input">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" class="frm-inp" required minlength="8">
                    <i class="fa fa-lock frm-ico"></i>
                </div>
                <button type="submit" class="frm-submit">Reset Password<i class="fa fa-arrow-circle-right"></i></button>
            <?php endif; ?>

            <a href="login.php" class="a-link"><i class="fa fa-sign-in"></i>Back to Login</a>
            <div class="frm-footer">ISY School Climate Survey © <?php echo date('Y'); ?></div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
