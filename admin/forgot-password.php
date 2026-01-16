<?php
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    try {
        // Check if email exists in admin_users table
        $stmt = $pdo->prepare("SELECT id, email, first_name, last_name FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // First, add reset token columns if they don't exist
            try {
                $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64)");
                $pdo->exec("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS reset_token_expiry TIMESTAMP");
            } catch (PDOException $e) {
                // Columns might already exist, continue
            }
            
            // Update the admin_users table with reset token
            $updateStmt = $pdo->prepare("
                UPDATE admin_users 
                SET reset_token = ?, reset_token_expiry = ? 
                WHERE email = ?
            ");
            $result = $updateStmt->execute([$token, $expiry, $email]);
            
            if ($result) {
                $resetLink = "http://{$_SERVER['HTTP_HOST']}/admin/reset-password.php?token=" . $token;
                
                // In a real application, you would send an email here
                // For now, we'll just show the reset link for testing
                $message = "Password reset link generated. In production, this would be sent via email.<br><br>";
                $message .= "For testing purposes, here's your reset link:<br>";
                $message .= "<a href='{$resetLink}' target='_blank'>Reset Password</a>";
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
require_once __DIR__ . '/../includes/header.php';
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
            <div class="frm-footer">ISY School Climate Survey &copy; <?php echo date('Y'); ?></div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>