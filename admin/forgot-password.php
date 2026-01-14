<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    try {
        // Check if email exists in users table instead of admin_users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update the users table with reset token
            $updateStmt = $conn->prepare("
                UPDATE users 
                SET reset_token = :token, 
                    reset_token_expiry = :expiry 
                WHERE email = :email
            ");
            
            $updateStmt->bindParam(':token', $token);
            $updateStmt->bindParam(':expiry', $expiry);
            $updateStmt->bindParam(':email', $email);
            $result = $updateStmt->execute();
            
            if ($result) {
                $resetLink = "http://{$_SERVER['HTTP_HOST']}/scs.isyedu.org/admin/reset-password.php?token=" . $token;
                $message = "If an account exists with this email, password reset instructions will be sent.";
            } else {
                $error = "An error occurred. Please try again later.";
            }
        } else {
            // For security, show the same message even if email doesn't exist
            $message = "If an account exists with this email, password reset instructions will be sent.";
        }
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        $error = "An error occurred. Please try again later.";
    }
}
require_once '../includes/header.php'; 
require_once '../includes/footer.php';
?>

<div id="single-wrapper">
    <form action="" method="POST" class="frm-single">
        <div class="inside">
            <div class="title"><strong>ISY</strong>Admin</div>
            <div class="frm-title">Reset Password</div>
            <p class="text-center">Enter your email address and we'll send you an email with instructions to reset your password.</p>

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

            <div class="frm-input">
                <input type="email" name="email" placeholder="Enter Email" class="frm-inp" required>
                <i class="fa fa-envelope frm-ico"></i>
            </div>

            <button type="submit" class="frm-submit">Send Email<i class="fa fa-arrow-circle-right"></i></button>
            <a href="login.php" class="a-link"><i class="fa fa-sign-in"></i>Back to Login</a>
            <div class="frm-footer">ISY School Climate Survey © <?php echo date('Y'); ?></div>
        </div>
    </form>
</div>

<<?php require_once '../includes/footer.php'; ?>