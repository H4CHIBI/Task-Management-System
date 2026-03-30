<?php
session_start();
require_once '../config/config.php'; 

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Removed trim for passwords as spaces can be part of a pass

    if(empty($username) || empty($password)){
        header("Location: ../index.php?error=empty_fields");
        exit();
    }

    try {
        // Added profile_image to the SELECT so it's available for the session
        $stmt = $pdo->prepare("SELECT id, username, password, role, profile_image FROM users_tbl WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])){
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'default_profile.png';

            // --- ROLE-BASED REDIRECTION ---
            if ($_SESSION['role'] === 'ADMIN') {
                header("Location: ../admin/dashboard.php");
            } else {
                // Change this to your actual user-facing page
                header("Location: ../user/user_dashboard.php"); 
            }
            exit();

        } else {
            // Generic error for security (don't tell them if it was the user or pass that was wrong)
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