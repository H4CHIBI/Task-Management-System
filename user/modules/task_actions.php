<?php
session_start();

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}

// 2. Import Database Configuration
// Nested inside user/modules/, so we go up two levels
require_once '../../config/config.php';

$user_id = $_SESSION['user_id'];

// 3. Validate Request Parameters
if (isset($_GET['action']) && isset($_GET['id'])) {
    $task_id = (int)$_GET['id'];
    $action = $_GET['action'];

    try {
        if ($action === 'complete') {
            /**
             * ACTION: COMPLETE
             * Sets status to 1 and timestamps the completion.
             */
            $stmt = $pdo->prepare("UPDATE task_tbl 
                                   SET is_completed = 1, 
                                       completed_at = NOW() 
                                   WHERE id = ? AND assigned_to = ?");
            $stmt->execute([$task_id, $user_id]);

            if ($stmt->rowCount() > 0) {
                header("Location: ../tasks.php?success=task_completed");
            } else {
                header("Location: ../tasks.php?error=not_found_or_access_denied");
            }

        } elseif ($action === 'undo') {
            /**
             * ACTION: UNDO / RESTORE
             * Sets status back to 0 and clears the completion timestamp.
             */
            $stmt = $pdo->prepare("UPDATE task_tbl 
                                   SET is_completed = 0, 
                                       completed_at = NULL 
                                   WHERE id = ? AND assigned_to = ?");
            $stmt->execute([$task_id, $user_id]);

            if ($stmt->rowCount() > 0) {
                header("Location: ../task_history.php?success=task_restored");
            } else {
                header("Location: ../task_history.php?error=not_found_or_access_denied");
            }

        } else {
            // Invalid action provided
            header("Location: ../user_dashboard.php");
        }
        exit();

    } catch (PDOException $e) {
        // Log error for the dev and show a generic error to user
        error_log("Database Error in task_actions: " . $e->getMessage());
        header("Location: ../user_dashboard.php?error=system_error");
        exit();
    }
} else {
    // No parameters provided
    header("Location: ../user_dashboard.php");
    exit();
}