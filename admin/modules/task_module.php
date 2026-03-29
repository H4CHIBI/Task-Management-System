<?php
session_start();
require_once '../../config/config.php';

// Security: Ensure only logged-in Admins can process these actions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add_task':
            $title = $_POST['title'];
            $project_id = $_POST['project_id'];
            $priority = $_POST['priority'];
            $assigned_to = $_POST['assigned_to'];

            // We initialize is_completed to 0 for all new tasks
            $sql = "INSERT INTO task_tbl (title, project_id, priority, assigned_to, is_completed, created_at) 
                    VALUES (?, ?, ?, ?, 0, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $project_id, $priority, $assigned_to]);

            header("Location: ../tasks.php?msg=task_added");
            break;

        case 'update_task':
            $task_id = $_POST['task_id'];
            $title = $_POST['title'];
            $project_id = $_POST['project_id'];
            $priority = $_POST['priority'];
            $assigned_to = $_POST['assigned_to'];

            // Update only the metadata. We don't touch is_completed here 
            // because that is handled by the AJAX/checkmark toggle.
            $sql = "UPDATE task_tbl SET 
                    title = ?, 
                    project_id = ?, 
                    priority = ?, 
                    assigned_to = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $project_id, $priority, $assigned_to, $task_id]);

            header("Location: ../tasks.php?msg=task_updated");
            break;

        case 'delete':
            $task_id = $_GET['id'] ?? null;

            if ($task_id) {
                $stmt = $pdo->prepare("DELETE FROM task_tbl WHERE id = ?");
                $stmt->execute([$task_id]);
                header("Location: ../tasks.php?msg=task_deleted");
            } else {
                header("Location: ../tasks.php?error=invalid_id");
            }
            break;

        default:
            header("Location: ../tasks.php");
            break;
    }
} catch (PDOException $e) {
    // Log error and redirect with error message
    error_log($e->getMessage());
    header("Location: ../tasks.php?error=db_error");
}
exit();