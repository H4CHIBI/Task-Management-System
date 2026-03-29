<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    
    // 1. Handle Profile Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = '../../public/uploads/';
        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . $user_id . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $image_name)) {
            $stmt = $pdo->prepare("UPDATE users_tbl SET profile_image = ? WHERE id = ?");
            $stmt->execute([$image_name, $user_id]);
            $_SESSION['profile_image'] = $image_name; // Update session for header
        }
    }

    // 2. Update Username
    $stmt = $pdo->prepare("UPDATE users_tbl SET username = ? WHERE id = ?");
    $stmt->execute([$username, $user_id]);
    $_SESSION['username'] = $username;

    // 3. Update Password (only if provided)
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users_tbl SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
    }

    header("Location: ../profile.php?msg=updated");
    exit();
}