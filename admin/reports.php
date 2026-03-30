<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// --- CONFIGURATION & PAGINATION ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = in_array($limit, [5, 10, 20, 50]) ? $limit : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- FILTERS ---
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$status_val = $_GET['status'] ?? 'all';
$user_id    = $_GET['user_id'] ?? 'all';

try {
    // 1. Fetch Users for Filter Dropdown
    $user_stmt = $pdo->query("SELECT id, username FROM users_tbl ORDER BY username ASC");
    $all_users = $user_stmt->fetchAll();

    // 2. Build Dynamic Where Clause
    $where_clauses = ["1=1"]; 
    $params = [];

    if (!empty($start_date) && !empty($end_date)) {
        $where_clauses[] = "t.created_at BETWEEN :start AND :end";
        $params[':start'] = $start_date . " 00:00:00";
        $params[':end']   = $end_date . " 23:59:59";
    }

    if ($status_val === 'completed') {
        $where_clauses[] = "t.is_completed = 1";
    } elseif ($status_val === 'pending') {
        $where_clauses[] = "t.is_completed = 0";
    }

    if ($user_id !== 'all') {
        $where_clauses[] = "t.assigned_to = :user_id";
        $params[':user_id'] = $user_id;
    }

    $filter_sql = implode(" AND ", $where_clauses);

    // 3. Count Filtered Results for Accurate Pagination
    $count_query = "SELECT COUNT(DISTINCT p.id) FROM project_tbl p 
                    LEFT JOIN task_tbl t ON p.id = t.project_id 
                    WHERE $filter_sql";
    $count_stmt = $pdo->prepare($count_query);
    foreach ($params as $k => $v) $count_stmt->bindValue($k, $v);
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // 4. Main Analytics Query
    $query = "SELECT 
                p.name as project_name,
                COUNT(t.id) as total_tasks,
                SUM(CASE WHEN t.is_completed = 1 THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN t.is_completed = 0 THEN 1 ELSE 0 END) as pending_count
              FROM project_tbl p
              LEFT JOIN task_tbl t ON p.id = t.project_id 
              WHERE $filter_sql
              GROUP BY p.id
              ORDER BY p.name ASC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) $stmt->bindValue($key, $val);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll();

} catch (PDOException $e) {
    die("System Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Analytics | TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
</head>
<body class="bg-slate-50 font-sans text-slate-900">

    <div class="flex h-screen overflow-hidden">
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../admin/includes/header.php'; ?>

            <div class="p-6 lg:p-10 max-w-7xl mx-auto w-full">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <span class="text-blue-600 font-bold text-xs uppercase tracking-widest">Reports & Insights</span>
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 mt-1">System Analytics</h1>
                    </div>

                    <a href="modules/export_reports.php?<?php echo http_build_query($_GET); ?>" target="_blank"
                       class="inline-flex items-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-emerald-200/50 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Export PDF Report
                    </a>
                </div>

                <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm mb-8">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date Start</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date End</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status Mix</label>
                            <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all appearance-none">
                                <option value="all" <?= $status_val == 'all' ? 'selected' : '' ?>>All Progress</option>
                                <option value="completed" <?= $status_val == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="pending" <?= $status_val == 'pending' ? 'selected' : '' ?>>In Progress</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assigned To</label>
                            <select name="user_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all appearance-none">
                                <option value="all">Everyone</option>
                                <?php foreach ($all_users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $user_id == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-slate-900 hover:bg-black text-white py-3.5 rounded-xl font-bold transition-all shadow-lg shadow-slate-200">Filter</button>
                            <a href="reports.php" class="bg-slate-100 hover:bg-slate-200 text-slate-500 p-3.5 rounded-xl transition-all flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Project Details</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Task Vol.</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status Distribution</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Completion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="4" class="px-8 py-20 text-center text-slate-400 font-medium italic">
                                            No records found matching those criteria.
                                        </td>
                                    </tr>
                                <?php else: foreach ($reports as $row): 
                                    $total = $row['total_tasks'];
                                    $done = $row['completed_count'];
                                    $percent = ($total > 0) ? round(($done / $total) * 100) : 0;
                                ?>
                                <tr class="group hover:bg-slate-50/50 transition-all">
                                    <td class="px-8 py-6">
                                        <span class="block font-black text-slate-800 text-sm tracking-tight uppercase"><?= htmlspecialchars($row['project_name']) ?></span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-600 font-black text-xs"><?= $total ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex justify-center gap-2">
                                            <span class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg text-[10px] font-black border border-emerald-100">DONE: <?= $done ?></span>
                                            <span class="bg-amber-50 text-amber-600 px-3 py-1.5 rounded-lg text-[10px] font-black border border-amber-100">LEFT: <?= $row['pending_count'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center justify-end gap-4">
                                            <div class="hidden sm:block h-2 w-24 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: <?= $percent ?>%"></div>
                                            </div>
                                            <span class="font-black text-slate-700 text-sm w-10 text-right"><?= $percent ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <div class="px-8 py-6 bg-slate-50/30 border-t border-slate-100 flex items-center justify-between">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Showing Page <span class="text-slate-900"><?= $page ?></span> of <span class="text-slate-900"><?= $total_pages ?></span>
                        </p>
                        <div class="flex gap-2">
                            <?php $params_str = http_build_query(array_merge($_GET, ['page' => ''])); ?>
                            <a href="?<?= $params_str . max(1, $page-1) ?>" 
                               class="p-2.5 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-blue-500 hover:text-blue-600 transition-all <?= $page <= 1 ? 'opacity-30 pointer-events-none' : '' ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                            <a href="?<?= $params_str . min($total_pages, $page+1) ?>" 
                               class="p-2.5 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-blue-500 hover:text-blue-600 transition-all <?= $page >= $total_pages ? 'opacity-30 pointer-events-none' : '' ?>">
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
</body>
</html>