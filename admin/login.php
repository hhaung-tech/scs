<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    try {
        if (login($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = 'An error occurred. Please try again.';
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div id="single-wrapper">
	<form method="POST" class="frm-single">
		<div class="inside">
			<div class="title"><strong>ISY School Climate Survey</strong></div>
			<!-- /.title -->
			<div class="frm-title">Admin Login</div>
			<!-- /.frm-title -->
            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="error box-title"><?php echo htmlspecialchars($error); ?></h4>
            </div>
            <?php endif; ?>
			<div class="frm-input"><input type="email" name="email" placeholder="Email Address" class="frm-inp"><i class="fa fa-user frm-ico"></i></div>
			<!-- /.frm-input -->
			<div class="frm-input"><input type="password"  name="password" placeholder="Password" class="frm-inp"><i class="fa fa-lock frm-ico"></i></div>
			<!-- /.frm-input -->
			<div class="clearfix margin-bottom-20">
				<div class="pull-left">
					<div class="checkbox primary"><input type="checkbox" id="rememberme"><label for="rememberme">Remember me</label></div>
					<!-- /.checkbox -->
				</div>
				<!-- /.pull-left -->
				<div class="pull-right"><a href="forgot-password.php" class="a-link"><i class="fa fa-unlock-alt"></i>Forgot password?</a></div>
				<!-- /.pull-right -->
			</div>
			<!-- /.clearfix -->
			<button type="submit" class="frm-submit">Login<i class="fa fa-arrow-circle-right"></i></button>
			<!-- /.row -->
			<!-- /.footer -->
		</div>
		<!-- .inside -->
	</form>
</div>
<style>
.chart-container {
    min-height: 400px;
    margin: 20px 0;
    border: 1px solid #eee;
    padding: 15px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>