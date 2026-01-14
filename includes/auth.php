<?php

// function isLoggedIn() {
//     if (isset($_SESSION['admin_id']) && isset($_SESSION['last_activity'])) {
//         if (time() - $_SESSION['last_activity'] > 300) {
//             logout();
//             return false;
//         }
//         // Update last activity time
//         $_SESSION['last_activity'] = time();
//         return true;
//     }
//     return false;
// }

// function requireLogin() {
//     if (!isLoggedIn()) {
//         header('Location: /../admin/login.php');
//         exit;
//     }
// }
session_start();
function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['last_activity'] = time(); // Add session start time
        
        $stmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }
    return false;
    
    // $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    // $stmt->execute([$email]);
    // $user = $stmt->fetch();
    
    // if ($user && password_verify($password, $user['password'])) {
    //     $_SESSION['admin_id'] = $user['id'];
    //     $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
    //     $_SESSION['last_activity'] = time();
        
    //     $stmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    //     $stmt->execute([$user['id']]);
        
    //     return true;
    // }
    // return false;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Location: /isy_scs_ai/admin/login.php');
        exit;
    }
}

function logout() {
    // Clear all session data
    $_SESSION = array();
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    // Destroy the session
    session_destroy();
    // Add cache control headers
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Location: /isy_scs_ai/admin/login.php');
    exit;
}
?>