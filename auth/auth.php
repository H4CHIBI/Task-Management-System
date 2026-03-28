<?php
session_start();

require_once '../config/config.php'; 

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password']) ?? '';

    if(empty($username) || empty($password)){
        header("Location: ../index.php?error=empty_fields");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users_tbl WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])){
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // 2. Redirect path (assuming dashboard is in public/admin/)
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            header("Location: ../index.php?error=invalid_credentials");
            exit();
        }

    } catch(PDOException $e) {
        error_log($e->getMessage());
        header("Location: ../index.php?error=system_error");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}