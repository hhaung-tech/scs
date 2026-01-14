<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    
    try {
        // Check if any admin exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password, $first_name, $last_name]);
            $message = "Admin account created successfully. You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Admin account already exists.";
        }
    } catch (PDOException $e) {
        $error = "Error creating admin account: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Initial Setup</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="setup-container">
        <h2>Create Initial Admin Account</h2>
        
        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label>First Name:</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Last Name:</label>
                <input type="text" name="last_name" required>
            </div>
            <button type="submit">Create Admin Account</button>
        </form>
    </div>
</body>
</html>