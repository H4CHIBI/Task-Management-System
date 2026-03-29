<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// Get filter from URL
$user_filter = $_GET['user_filter'] ?? 'all';

try {
    // 1. Base Query: Only fetching COMPLETED tasks
    $query = "SELECT t.*, p.name as project_name, u.username as assigned_user 
              FROM task_tbl t 
              LEFT JOIN project_tbl p ON t.project_id = p.id
              LEFT JOIN users_tbl u ON t.assigned_to = u.id
              WHERE t.is_completed = 1"; 

    if ($user_filter !== 'all') {
        $query .= " AND t.assigned_to = :user_id";
    }
    
    $query .= " ORDER BY t.completed_at DESC"; // Latest completed first
    
    $stmt = $pdo->prepare($query);

    if ($user_filter !== 'all') {
        $stmt->bindValue(':user_id', $user_filter, PDO::PARAM_INT);
    }

    $stmt->execute();
    $history = $stmt->fetchAll();

    // Get users for the filter dropdown
    $users = $pdo->query("SELECT id, username FROM users_tbl ORDER BY username ASC")->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task History - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 antialiased">

    <div class="flex h-screen overflow-hidden">
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../admin/includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Task History</h1>
                        <p class="text-slate-500 font-medium">Archived log of <span class="text-emerald-600 font-bold"><?php echo count($history); ?></span> completed tasks.</p>
                    </div>

                    <form method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                        <div class="pl-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter User:</div>
                        <select name="user_filter" onchange="this.form.submit()" 
                                class="bg-slate-50 border-none text-xs font-bold text-slate-600 rounded-xl focus:ring-0 cursor-pointer py-2 px-4">
                            <option value="all">All Personnel</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo ($user_filter == $u['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-6 py-6 w-16 text-center">Status</th>
                                <th class="px-6 py-6">Task</th>
                                <th class="px-6 py-6">Project</th>
                                <th class="px-6 py-6">Completed By</th>
                                <th class="px-6 py-6">Finished On</th>
                                <th class="px-6 py-6 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($history)): ?>
                                <tr><td colspan="6" class="px-6 py-20 text-center text-slate-400 italic">No completed tasks in the archive yet.</td></tr>
                            <?php else: foreach ($history as $task): ?>
                                <tr class="group hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto shadow-lg shadow-emerald-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-slate-500 line-through decoration-slate-300 uppercase text-sm">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-500 text-[10px] font-bold border border-slate-200">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-600">
                                        <?php echo htmlspecialchars($task['assigned_user'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-slate-700">
                                            <?php echo date('M d, Y', strtotime($task['completed_at'])); ?>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-medium">
                                            at <?php echo date('g:i A', strtotime($task['completed_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button onclick="restoreTask(<?php echo $task['id']; ?>)" 
                                                class="opacity-0 group-hover:opacity-100 transition-opacity px-4 py-2 bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-500 text-[10px] font-black rounded-xl uppercase tracking-tighter">
                                            Restore to Pending
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include '../admin/includes/footer.php'; ?>
        </main>
    </div>

    <script>
    function restoreTask(taskId) {
        if(!confirm('Move this task back to the active list?')) return;
        
        fetch('modules/update_task_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                task_id: taskId, 
                is_completed: 0, // Set back to pending
                completed_at: null // Clear the completion date
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
    </script>
</body>
</html>