<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

require_once '../config/config.php';
$user_id = $_SESSION['user_id'];

// --- PAGINATION & FILTER ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Base Query: Only completed tasks for this specific user
$queryStr = "FROM task_tbl t LEFT JOIN project_tbl p ON t.project_id = p.id WHERE t.assigned_to = ? AND t.is_completed = 1";
$params = [$user_id];

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) " . $queryStr);
    $countStmt->execute($params);
    $total_tasks = $countStmt->fetchColumn();
    $total_pages = ceil($total_tasks / $limit);

    $dataQuery = "SELECT t.*, p.name as project_name " . $queryStr . " ORDER BY t.completed_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($dataQuery);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
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
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 antialiased">

    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include 'includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full flex-grow">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Completed Work</h1>
                        <p class="text-slate-500 font-medium mt-1">Review your finished tasks and project milestones.</p>
                    </div>
                    
                    <form method="GET" class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-slate-100 shadow-sm">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-r border-slate-100 pr-3">View</span>
                        <select name="limit" onchange="this.form.submit()" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest text-slate-600 focus:ring-0 cursor-pointer">
                            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 Rows</option>
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25 Rows</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 Rows</option>
                        </select>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-8 py-5">Task Details</th>
                                <th class="px-8 py-5">Priority</th>
                                <th class="px-8 py-5">Date Assigned</th>
                                <th class="px-8 py-5 text-right">Completion Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(empty($tasks)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center opacity-20">
                                            <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                                            <p class="font-black uppercase tracking-widest text-xs">No history recorded yet</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $task): ?>
                                    <tr class="group hover:bg-slate-50/30 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-tight">
                                                <?php echo htmlspecialchars($task['project_name'] ?: 'General Task'); ?>
                                            </div>
                                            <div class="font-black text-slate-800 text-sm italic line-through decoration-slate-300 decoration-2">
                                                <?php echo htmlspecialchars($task['title']); ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span class="text-[10px] font-black uppercase text-slate-400 border border-slate-100 px-2 py-1 rounded-md">
                                                Lvl <?php echo $task['priority']; ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="text-xs font-bold text-slate-500">
                                                <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex items-center justify-end gap-5">
                                                <div class="text-right">
                                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-tighter leading-none">Finished on</p>
                                                    <p class="text-[11px] font-bold text-slate-400 mt-1"><?php echo date('M d, Y', strtotime($task['completed_at'])); ?></p>
                                                </div>
                                                
                                                <a href="modules/task_actions.php?action=undo&id=<?php echo $task['id']; ?>" 
                                                   onclick="return confirm('Move this task back to Ongoing assignments?')"
                                                   class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-600 rounded-xl transition-all shadow-sm border border-slate-100 active:scale-90"
                                                   title="Restore Task">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l5 5m-5-5l5-5"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Total Archive: <?php echo $total_tasks; ?> Tasks
                    </p>
                    
                    <div class="flex gap-2">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" 
                               class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-black transition-all <?php echo $page == $i ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>

            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

</body>
</html>