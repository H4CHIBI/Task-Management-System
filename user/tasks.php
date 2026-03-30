<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

require_once '../config/config.php';
$user_id = $_SESSION['user_id'];

// --- PAGINATION & FILTER LOGIC ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$project_filter = isset($_GET['project_id']) ? $_GET['project_id'] : '';

// 1. Fetch projects for the filter dropdown
$projStmt = $pdo->prepare("SELECT DISTINCT p.id, p.name FROM project_tbl p 
                           JOIN task_tbl t ON t.project_id = p.id 
                           WHERE t.assigned_to = ?");
$projStmt->execute([$user_id]);
$available_projects = $projStmt->fetchAll();

// 2. Build Query
$queryStr = "FROM task_tbl t LEFT JOIN project_tbl p ON t.project_id = p.id WHERE t.assigned_to = ? AND t.is_completed = 0";
$params = [$user_id];

if (!empty($priority_filter)) {
    $queryStr .= " AND t.priority = ?";
    $params[] = $priority_filter;
}
if (!empty($project_filter)) {
    $queryStr .= " AND t.project_id = ?";
    $params[] = $project_filter;
}

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) " . $queryStr);
    $countStmt->execute($params);
    $total_tasks = $countStmt->fetchColumn();
    $total_pages = ceil($total_tasks / $limit);

    // Sorting by Project Name, then Priority
    $dataQuery = "SELECT t.*, p.name as project_name " . $queryStr . " 
                  ORDER BY p.name ASC, t.priority DESC, t.created_at DESC 
                  LIMIT $limit OFFSET $offset";
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
    <title>Active Tasks - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="bg-slate-50 antialiased" x-data="{ }"></body>

    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include 'includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full flex-grow">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Active Tasks</h1>
                        <p class="text-slate-500 font-medium mt-1">Your current workload sorted by project.</p>
                    </div>

                    <form method="GET" class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                        <select name="project_id" onchange="this.form.submit()" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer">
                            <option value="">All Projects</option>
                            <?php foreach($available_projects as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $project_filter == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="h-4 w-[1px] bg-slate-100 mx-1"></div>
                        <select name="priority" onchange="this.form.submit()" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest focus:ring-0 cursor-pointer">
                            <option value="">All Priorities</option>
                            <option value="3" <?= $priority_filter == '3' ? 'selected' : '' ?>>High</option>
                            <option value="2" <?= $priority_filter == '2' ? 'selected' : '' ?>>Medium</option>
                            <option value="1" <?= $priority_filter == '1' ? 'selected' : '' ?>>Low</option>
                        </select>
                        <?php if(!empty($priority_filter) || !empty($project_filter)): ?>
                            <a href="tasks.php" class="ml-2 px-3 py-1 bg-rose-50 text-rose-500 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-rose-100 transition-colors">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-8 py-5">Assignment</th>
                                <th class="px-8 py-5 text-center">Priority</th>
                                <th class="px-8 py-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(empty($tasks)): ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-20 text-center text-slate-300 font-bold uppercase text-[10px] tracking-widest">
                                        No active tasks found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $task): ?>
                                    <tr class="group hover:bg-slate-50/30 transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="text-[10px] font-bold text-emerald-600 uppercase mb-1 tracking-tight"><?= htmlspecialchars($task['project_name']); ?></div>
                                            <div class="font-black text-slate-800 text-sm"><?= htmlspecialchars($task['title']); ?></div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex justify-center">
                                                <?php 
                                                    $colors = [1 => 'bg-slate-100 text-slate-500', 2 => 'bg-amber-50 text-amber-600', 3 => 'bg-rose-50 text-rose-600'];
                                                    $labels = [1 => 'Low', 2 => 'Medium', 3 => 'High'];
                                                ?>
                                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter <?= $colors[$task['priority']]; ?>">
                                                    <?= $labels[$task['priority']]; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <a href="modules/task_actions.php?action=complete&id=<?= $task['id']; ?>" 
                                               onclick="return confirm('Mark as completed?')"
                                               class="bg-slate-900 hover:bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                Mark Done
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mt-4 mb-8">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Showing <?= count($tasks) ?> of <?= $total_tasks ?> Assignments
                    </p>
                    
                    <div class="flex items-center gap-2">
                        <?php $url_params = $_GET; ?>
                        <?php if($page > 1): ?>
                            <?php $url_params['page'] = $page - 1; ?>
                            <a href="?<?= http_build_query($url_params) ?>" class="px-3 py-2 bg-white text-slate-400 border border-slate-100 rounded-xl hover:bg-slate-50 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                        <?php endif; ?>

                        <div class="flex gap-1">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <?php $url_params['page'] = $i; ?>
                                <a href="?<?= http_build_query($url_params) ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl text-xs font-black transition-all 
                                   <?= $page == $i ? 'bg-emerald-500 text-white shadow-lg' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50' ?>">
                                    <?= $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if($page < $total_pages): ?>
                            <?php $url_params['page'] = $page + 1; ?>
                            <a href="?<?= http_build_query($url_params) ?>" class="px-3 py-2 bg-white text-slate-400 border border-slate-100 rounded-xl hover:bg-slate-50 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

</body>
</html>