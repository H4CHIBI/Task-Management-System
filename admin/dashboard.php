<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// Fetch your statistics here...
$totalTasks = $pdo->query("SELECT COUNT(*) FROM task_tbl")->fetchColumn();
$totalProjects = $pdo->query("SELECT COUNT(*) FROM project_tbl")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users_tbl")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            
            <?php include '../includes/header.php'; ?>

            <div class="p-6 flex-grow max-w-7xl mx-auto w-full">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Active Tasks</p>
                        <h3 class="text-3xl font-black text-blue-600 mt-2"><?php echo $totalTasks; ?></h3>
                    </div>
                    </div>

            </div>

            <?php include '../includes/footer.php'; ?>

        </main>
    </div>

</body>
</html>