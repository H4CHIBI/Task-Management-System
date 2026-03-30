<?php
session_start();
require_once '../../config/config.php';

// Check for admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}

// 1. ADD USER LOGIC
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $image_name = 'default_profile.png';

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $image_name = time() . '_' . $_FILES['profile_image']['name'];
        move_uploaded_file($_FILES['profile_image']['tmp_name'], '../../public/uploads/' . $image_name);
    }

    $stmt = $pdo->prepare("INSERT INTO users_tbl (username, password, role, profile_image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $role, $image_name]);
    header("Location: ../manage_users.php?msg=user_added");
    exit();
}

// 2. UPDATE USER LOGIC
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    // Update basic info
    $stmt = $pdo->prepare("UPDATE users_tbl SET username = ?, role = ? WHERE id = ?");
    $stmt->execute([$username, $role, $id]);

    // Update password if provided
    if (!empty($_POST['password'])) {
        $new_pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users_tbl SET password = ? WHERE id = ?");
        $stmt->execute([$new_pw, $id]);
    }

    // Update image if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $image_name = time() . '_' . $_FILES['profile_image']['name'];
        move_uploaded_file($_FILES['profile_image']['tmp_name'], '../../public/uploads/' . $image_name);
        $stmt = $pdo->prepare("UPDATE users_tbl SET profile_image = ? WHERE id = ?");
        $stmt->execute([$image_name, $id]);
    }

    header("Location: ../manage_users.php?msg=user_updated");
    exit();
}

// 3. DELETE USER LOGIC
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    
    // Prevent self-deletion
    if($id == $_SESSION['user_id']) {
        header("Location: ../manage_users.php?error=self_delete");
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM users_tbl WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../manage_users.php?msg=user_deleted");
    exit();
}