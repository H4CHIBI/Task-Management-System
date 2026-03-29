<?php
session_start();
require_once '../../config/config.php';

// Set header to return JSON for the JavaScript fetch call
header('Content-Type: application/json');

// 1. Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

// 2. Capture the JSON input from the fetch request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 3. Validation: Ensure we have the Task ID and the new completion state
if (isset($data['task_id']) && isset($data['is_completed'])) {
    $taskId = (int)$data['task_id'];
    $isCompleted = (int)$data['is_completed'];
    
    // Use the timestamp sent from JS, or default to NULL if we are un-completing
    $completedAt = ($isCompleted === 1) ? ($data['completed_at'] ?? date('Y-m-d H:i:s')) : null;

    try {
        // 4. Prepared Statement: Update only existing columns
        // We removed 'status' from this query as per your database change.
        $sql = "UPDATE task_tbl 
                SET is_completed = ?, 
                    completed_at = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$isCompleted, $completedAt, $taskId]);

        // 5. Check if the row was actually found and updated
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Task status synchronized.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => 'Task not found or no changes detected.'
            ]);
        }
    } catch (PDOException $e) {
        // Log error server-side and return a clean message to the UI
        error_log("Database Error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'error' => 'A database error occurred while updating the task.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid data payload received.'
    ]);
}
exit();