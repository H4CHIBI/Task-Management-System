<?php
session_start();
require_once '../../config/config.php';

// Security Check: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}

// --- DELETE TASK ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM task_tbl WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../tasks.php?msg=task_deleted");
    } catch (PDOException $e) {
        header("Location: ../tasks.php?error=delete_failed");
    }
    exit();
}

// --- INSERT TASK ---
// --- INSERT TASK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = trim($_POST['title'] ?? '');
    $project_id = $_POST['project_id'] ?? null;
    $priority = $_POST['priority'] ?? 2; // Default to Medium (2)
    $status = 'Pending'; // Default status string
    $is_completed = 0;   // Default to not completed

    if (!empty($title) && !empty($project_id)) {
        try {
            // Correctly mapping to your task_tbl columns
            $sql = "INSERT INTO task_tbl (project_id, title, status, priority, is_completed) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$project_id, $title, $status, $priority, $is_completed]);
            
            header("Location: ../tasks.php?msg=task_added");
        } catch (PDOException $e) {
            // Log the error for debugging: error_log($e->getMessage());
            header("Location: ../tasks.php?error=failed");
        }
    }
    exit();
}
// If accessed directly without action
header("Location: ../tasks.php");
exit();