<?php
session_start();
require_once '../../config/config.php';

// Security Check: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}

/**
 * ACTION: TOGGLE PROJECT STATUS
 * Flips status between ONGOING and COMPLETE
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $project_id = (int)$_POST['project_id'];
    $current_status = $_POST['current_status'];
    
    // Updated Logic: Use 'COMPLETE' instead of 'CLOSED' to match your DB
    $new_status = ($current_status === 'ONGOING') ? 'COMPLETE' : 'ONGOING';
    
    try {
        $stmt = $pdo->prepare("UPDATE project_tbl SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $project_id]);
        
        // Redirect back to the list. 
        // If we just completed it, redirect to the COMPLETE list, otherwise back to ONGOING.
        header("Location: ../projects.php?status_filter=" . $new_status . "&msg=status_updated");
    } catch (PDOException $e) {
        header("Location: ../projects.php?error=update_failed");
    }
    exit();
}

/**
 * ACTION: DELETE PROJECT
 */
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Option A: Hard delete (will fail if tasks exist due to FK constraints)
        $stmt = $pdo->prepare("DELETE FROM project_tbl WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../projects.php?msg=project_deleted");
    } catch (PDOException $e) {
        // Triggers if foreign key constraints prevent deletion (e.g., tasks are linked to this project)
        header("Location: ../projects.php?error=dependency_error");
    }
    exit();
}

/**
 * ACTION: INSERT PROJECT
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $name = trim($_POST['project_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = 'ONGOING'; 
    $owner_id = $_SESSION['user_id']; 

    if (!empty($name)) {
        try {
            $sql = "INSERT INTO project_tbl (name, description, status, owner_id) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                $name, 
                strip_tags($description), 
                $status,
                $owner_id 
            ]);
            
            header("Location: ../projects.php?status_filter=ONGOING&msg=project_added");
        } catch (PDOException $e) {
            header("Location: ../projects.php?error=insert_failed");
        }
    } else {
        header("Location: ../projects.php?error=empty_name");
    }
    exit();
}

// Default fallback
header("Location: ../projects.php");
exit();