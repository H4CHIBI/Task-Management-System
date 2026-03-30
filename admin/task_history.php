<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// --- PAGINATION & LIMIT LOGIC ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default to 10 for history
$limit = in_array($limit, [5, 10, 20, 50]) ? $limit : 10; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Get filter from URL
$user_filter = $_GET['user_filter'] ?? 'all';

try {
    // 1. Count Total Tasks for Pagination
    $count_query = "SELECT COUNT(*) FROM task_tbl WHERE is_completed = 1";
    if ($user_filter !== 'all') {
        $count_query .= " AND assigned_to = :user_id";
    }
    
    $count_stmt = $pdo->prepare($count_query);
    if ($user_filter !== 'all') {
        $count_stmt->bindValue(':user_id', $user_filter, PDO::PARAM_INT);
    }
    $count_stmt->execute();
    $total_tasks = $count_stmt->fetchColumn();
    $total_pages = ceil($total_tasks / $limit);
    $total_pages = max(1, $total_pages);

    // 2. Fetch Tasks with Limit and Offset
    $query = "SELECT t.*, p.name as project_name, u.username as assigned_user 
              FROM task_tbl t 
              LEFT JOIN project_tbl p ON t.project_id = p.id
              LEFT JOIN users_tbl u ON t.assigned_to = u.id
              WHERE t.is_completed = 1"; 

    if ($user_filter !== 'all') {
        $query .= " AND t.assigned_to = :user_id";
    }
    
    $query .= " ORDER BY t.completed_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    if ($user_filter !== 'all') {
        $stmt->bindValue(':user_id', $user_filter, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
                        <p class="text-slate-500 font-medium italic">
                            Archived log of <span class="text-emerald-600 font-bold"><?php echo $total_tasks; ?></span> completed tasks.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <form method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                            <div class="pl-3 text-[10px] font-black text-slate-400 uppercase tracking-widest border-r pr-3">Show:</div>
                            <select name="limit" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer">
                                <?php foreach([5, 10, 20, 50] as $opt): ?>
                                    <option value="<?php echo $opt; ?>" <?php echo ($limit == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?> Rows</option>
                                <?php endforeach; ?>
                            </select>

                            <div class="h-4 w-[1px] bg-slate-200"></div>

                            <select name="user_filter" onchange="this.form.submit()" 
                                    class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer py-2 px-4">
                                <option value="all">All Personnel</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo ($user_filter == $u['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-6 py-6 w-16 text-center">Status</th>
                                <th class="px-6 py-6">Task</th>
                                <th class="px-6 py-6">Project</th>
                                <th class="px-6 py-6">Assigned To</th>
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
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500 uppercase">
                                                <?php echo substr($task['assigned_user'] ?? '?', 0, 2); ?>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-600"><?php echo htmlspecialchars($task['assigned_user'] ?? 'N/A'); ?></span>
                                        </div>
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

                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Showing page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php 
                                $filter_query = "&limit=$limit&user_filter=" . urlencode($user_filter);
                            ?>
                            <a href="?page=<?php echo max(1, $page - 1) . $filter_query; ?>" 
                               class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-500 transition-all <?php echo $page <= 1 ? 'opacity-30 pointer-events-none' : 'shadow-sm'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            </a>

                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            for ($i = $start; $i <= $end; $i++): 
                            ?>
                                <a href="?page=<?php echo $i . $filter_query; ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl font-black text-xs transition-all <?php echo $i == $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-400 hover:border-indigo-500 hover:text-indigo-600'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <a href="?page=<?php echo min($total_pages, $page + 1) . $filter_query; ?>" 
                               class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-500 transition-all <?php echo $page >= $total_pages ? 'opacity-30 pointer-events-none' : 'shadow-sm'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
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
                is_completed: 0, 
                completed_at: null 
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