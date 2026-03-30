<?php
session_start();

// Security: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

require_once '../config/config.php';

$user_id = $_SESSION['user_id'];

try {
    // 1. Get Statistics from task_tbl
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed
        FROM task_tbl WHERE assigned_to = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    // 2. Fetch Recent Tasks
    $stmt = $pdo->prepare("SELECT t.*, p.name as project_name 
                           FROM task_tbl t 
                           LEFT JOIN project_tbl p ON t.project_id = p.id 
                           WHERE t.assigned_to = ? 
                           ORDER BY t.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_tasks = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $stats = ['total' => 0, 'pending' => 0, 'completed' => 0];
    $recent_tasks = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 antialiased">

    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include 'includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full flex-grow">
                
                <div class="mb-10">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">
                        Welcome back, <span class="text-emerald-600"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </h1>
                    <p class="text-slate-500 font-medium">You have <span class="font-bold text-slate-700"><?php echo $stats['pending']; ?></span> active tasks to focus on today.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm text-center md:text-left">
                        <div class="text-3xl font-black text-slate-900"><?php echo $stats['total']; ?></div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Assigned</div>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm text-center md:text-left">
                        <div class="text-3xl font-black text-emerald-600"><?php echo $stats['completed']; ?></div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completed</div>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm text-center md:text-left">
                        <div class="text-3xl font-black text-amber-500"><?php echo $stats['pending']; ?></div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ongoing</div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-10">
                    <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-black text-slate-800 uppercase tracking-tight">Recent Assignments</h3>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded font-bold">LATEST 5</span>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4">Project / Title</th>
                                <th class="px-8 py-4">Priority</th>
                                <th class="px-8 py-4">Status</th>
                                <th class="px-8 py-4 text-right">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if(empty($recent_tasks)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-10 text-center text-slate-400 text-sm font-medium">No tasks found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_tasks as $task): ?>
                                <tr class="group hover:bg-slate-50/30 transition-colors">
                                    <td class="px-8 py-4">
                                        <div class="text-[10px] font-bold text-emerald-600 uppercase mb-1">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'General'); ?>
                                        </div>
                                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($task['title']); ?></div>
                                    </td>
                                    <td class="px-8 py-4">
                                        <span class="text-xs font-bold text-slate-600">Level <?php echo $task['priority']; ?></span>
                                    </td>
                                    <td class="px-8 py-4">
                                        <?php if($task['is_completed']): ?>
                                            <span class="flex items-center gap-1.5 text-emerald-600 text-[10px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Done
                                            </span>
                                        <?php else: ?>
                                            <span class="flex items-center gap-1.5 text-amber-500 text-[10px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-4 text-right text-xs text-slate-400 font-medium">
                                        <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>