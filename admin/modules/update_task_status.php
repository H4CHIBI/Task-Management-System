<?php
session_start();
require_once '../../config/config.php';

// Security: Only Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get the JSON data from the JavaScript Fetch request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['task_id']) && isset($data['is_completed'])) {
    try {
        $stmt = $pdo->prepare("UPDATE task_tbl SET is_completed = ? WHERE id = ?");
        $stmt->execute([$data['is_completed'], $data['task_id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}