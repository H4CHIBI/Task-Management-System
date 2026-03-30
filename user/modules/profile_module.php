<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];

    try {
        // Base query
        $sql = "UPDATE users_tbl SET username = ? ";
        $params = [$username];

        // Update password if provided
        if (!empty($new_password)) {
            $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = ? ";
            $params[] = $hashed_pw;
        }

        // Handle Image Upload
        if (!empty($_FILES['profile_image']['name'])) {
            $img_name = time() . '_' . $_FILES['profile_image']['name'];
            move_uploaded_file($_FILES['profile_image']['tmp_name'], "../../public/uploads/" . $img_name);
            $sql .= ", profile_image = ? ";
            $params[] = $img_name;
            $_SESSION['profile_image'] = $img_name;
        }

        $sql .= " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['username'] = $username;
        header("Location: ../profile.php?success=updated");

    } catch (PDOException $e) {
        header("Location: ../profile.php?error=failed");
    }
}