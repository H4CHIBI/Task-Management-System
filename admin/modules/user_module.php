<?php
session_start();
require_once '../../config/config.php';

// Security Check: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}

/**
 * ACTION: ADD NEW USER
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'USER';
    
    // Default image if upload fails or is empty
    $image_name = 'default_profile.png'; 

    // Handle File Upload logic
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = '../../public/uploads/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        // Generate unique filename using timestamp to avoid overwriting existing files
        $image_name = time() . '_' . uniqid() . '.' . $file_ext; 
        $target_path = $upload_dir . $image_name;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            // Fallback to default if move fails
            $image_name = 'default_profile.png';
        }
    }

    if (!empty($username) && !empty($password)) {
        try {
            // Securely hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // SQL matches your columns: id (auto), username, password, role, profile_image
            $sql = "INSERT INTO users_tbl (username, password, role, profile_image) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $hashed_password, $role, $image_name]);

            header("Location: ../users.php?msg=user_added");
        } catch (PDOException $e) {
            // Handle duplicate usernames or DB errors
            header("Location: ../users.php?error=registration_failed");
        }
    } else {
        header("Location: ../users.php?error=missing_fields");
    }
    exit();
}

/**
 * ACTION: DELETE USER
 */
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Security: Prevent an admin from deleting their own account
    if ($id === (int)$_SESSION['user_id']) {
        header("Location: ../users.php?error=cannot_delete_self");
        exit();
    }

    try {
        // First, optionally get the filename to delete the physical file (housekeeping)
        $img_stmt = $pdo->prepare("SELECT profile_image FROM users_tbl WHERE id = ?");
        $img_stmt->execute([$id]);
        $user_data = $img_stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM users_tbl WHERE id = ?");
        $stmt->execute([$id]);

        // Delete the physical image file if it's not the default one
        if ($user_data && $user_data['profile_image'] !== 'default_profile.png') {
            $file_to_delete = '../../public/uploads/' . $user_data['profile_image'];
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }

        header("Location: ../users.php?msg=user_deleted");
    } catch (PDOException $e) {
        header("Location: ../users.php?error=delete_failed");
    }
    exit();
}

// Fallback
header("Location: ../users.php");
exit();