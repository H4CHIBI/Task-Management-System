<?php
session_start();
require_once '../../config/config.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit();
}

// --- DELETE PROCESS ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM project_tbl WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: ../projects.php?msg=project_deleted");
    } catch (PDOException $e) {
        header("Location: ../projects.php?error=dependency_error");
    }
    exit();
}

// --- INSERT PROCESS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $project_name = trim($_POST['project_name'] ?? '');
    
    if (!empty($project_name)) {
        try {
            // MATCHED TO YOUR TABLE: Using 'name' column
            $stmt = $pdo->prepare("INSERT INTO project_tbl (name) VALUES (?)");
            $stmt->execute([$project_name]);
            header("Location: ../projects.php?msg=project_added");
        } catch (PDOException $e) {
            header("Location: ../projects.php?error=failed");
        }
    } else {
        header("Location: ../projects.php?error=empty_field");
    }
    exit();
}