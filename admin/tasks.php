<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// --- PAGINATION & LIMIT LOGIC ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5; 
$limit = in_array($limit, [5, 10, 20, 50]) ? $limit : 5; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// --- FILTER & SORT SETTINGS ---
$priority_filter = $_GET['priority_filter'] ?? 'all';
$user_filter = $_GET['user_filter'] ?? 'all';
$sort_priority = $_GET['sort_priority'] ?? 'DESC'; // Default: High to Low

try {
    // 1. Build Filter Conditions
    $where_clauses = ["t.is_completed = 0"];
    $params = [];

    if ($priority_filter !== 'all') {
        $where_clauses[] = "t.priority = :priority";
        $params[':priority'] = $priority_filter;
    }
    if ($user_filter !== 'all') {
        $where_clauses[] = "t.assigned_to = :user_id";
        $params[':user_id'] = $user_filter;
    }

    $where_sql = implode(" AND ", $where_clauses);

    // 2. Count Total Records
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM task_tbl t WHERE $where_sql");
    foreach ($params as $key => $val) { $count_stmt->bindValue($key, $val); }
    $count_stmt->execute();
    $total_tasks = $count_stmt->fetchColumn();
    $total_pages = ceil($total_tasks / $limit);
    $total_pages = max(1, $total_pages);

    // 3. Fetch Tasks with Dynamic Sorting
    // We sort by priority first, then by ID (latest) as a secondary sort
    $query = "SELECT t.*, p.name as project_name, u.username as assigned_user 
              FROM task_tbl t 
              LEFT JOIN project_tbl p ON t.project_id = p.id
              LEFT JOIN users_tbl u ON t.assigned_to = u.id
              WHERE $where_sql
              ORDER BY t.priority $sort_priority, t.id DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) { $stmt->bindValue($key, $val); }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tasks = $stmt->fetchAll();

    $users = $pdo->query("SELECT id, username FROM users_tbl ORDER BY username ASC")->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Tasks - TMS</title>
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
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Active Tasks</h1>
                        <p class="text-slate-500 font-medium">Managing <span class="text-indigo-600 font-bold"><?php echo $total_tasks; ?></span> pending items.</p>
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

                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Sort:</div>
                            <select name="sort_priority" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer">
                                <option value="DESC" <?php echo ($sort_priority == 'DESC') ? 'selected' : ''; ?>>High ➔ Low</option>
                                <option value="ASC" <?php echo ($sort_priority == 'ASC') ? 'selected' : ''; ?>>Low ➔ High</option>
                            </select>

                            <div class="h-4 w-[1px] bg-slate-200"></div>

                            <select name="user_filter" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer pr-4">
                                <option value="all">All Personnel</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo ($user_filter == $u['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <input type="hidden" name="priority_filter" value="<?php echo htmlspecialchars($priority_filter); ?>">
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-6 py-6 w-20 text-center">Done</th>
                                <th class="px-6 py-6">Task Details</th>
                                <th class="px-6 py-6">Project</th>
                                <th class="px-6 py-6">Assignee</th>
                                <th class="px-6 py-6 text-center">Priority</th>
                                <th class="px-6 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(empty($tasks)): ?>
                                <tr><td colspan="6" class="px-6 py-20 text-center text-slate-400 italic">No tasks match your criteria.</td></tr>
                            <?php else: foreach ($tasks as $task): ?>
                                <tr class="group hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="markAsDone(<?php echo $task['id']; ?>)" class="w-10 h-10 rounded-full border-2 border-slate-200 text-transparent group-hover:border-emerald-500 group-hover:text-emerald-500 hover:!bg-emerald-500 hover:!text-white transition-all flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 uppercase text-sm"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <div class="text-[10px] text-slate-400 font-medium">Ref #<?php echo $task['id']; ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-black border border-indigo-100">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-black uppercase">
                                                <?php echo substr($task['assigned_user'] ?? '?', 0, 1); ?>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-600"><?php echo htmlspecialchars($task['assigned_user'] ?? 'Unassigned'); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php $p = (int)$task['priority']; ?>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black <?php echo $p==3 ? 'bg-red-50 text-red-600' : ($p==2 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'); ?>">
                                            <?php echo $p==3 ? 'HIGH' : ($p==2 ? 'MED' : 'LOW'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="modules/task_module.php?action=delete&id=<?php echo $task['id']; ?>" onclick="return confirm('Delete this task?')" class="text-slate-300 hover:text-red-500 transition-colors p-2">
                                            <svg class="w-5 h-5 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>

                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                        <div class="flex gap-2">
                            <?php $base_url = "?limit=$limit&user_filter=$user_filter&priority_filter=$priority_filter&sort_priority=$sort_priority"; ?>
                            <a href="<?php echo $base_url; ?>&page=<?php echo max(1, $page-1); ?>" 
                               class="p-2 bg-white border rounded-xl <?php echo $page <= 1 ? 'opacity-50 pointer-events-none' : 'hover:border-indigo-500 hover:text-indigo-600 shadow-sm'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                            <a href="<?php echo $base_url; ?>&page=<?php echo min($total_pages, $page+1); ?>" 
                               class="p-2 bg-white border rounded-xl <?php echo $page >= $total_pages ? 'opacity-50 pointer-events-none' : 'hover:border-indigo-500 hover:text-indigo-600 shadow-sm'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    function markAsDone(taskId) {
        if(!confirm('Complete this task?')) return;
        fetch('modules/update_task_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task_id: taskId, is_completed: 1 })
        }).then(() => window.location.reload());
    }
    </script>
</body>
</html>