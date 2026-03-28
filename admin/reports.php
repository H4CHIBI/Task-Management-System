<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// 1. Fetch Users - Using 'username' based on your screenshot
// Also fetching ADMINs just in case you assigned tasks to yourself
$user_stmt = $pdo->query("SELECT id, username FROM users_tbl ORDER BY username ASC");
$all_users = $user_stmt->fetchAll();

// 2. Capture all Filter Inputs
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$status_val = $_GET['status'] ?? 'all';
$user_id    = $_GET['user_id'] ?? 'all';

try {
    $params = [];
    $filters = "";

    // Date Filtering (Using created_at - check if this column exists in task_tbl)
    if (!empty($start_date) && !empty($end_date)) {
        $filters .= " AND t.created_at BETWEEN :start AND :end ";
        $params[':start'] = $start_date . " 00:00:00";
        $params[':end']   = $end_date . " 23:59:59";
    }

    // Status Filtering
    if ($status_val === 'completed') {
        $filters .= " AND t.is_completed = 1 ";
    } elseif ($status_val === 'pending') {
        $filters .= " AND t.is_completed = 0 ";
    }

    // User Filtering
    if ($user_id !== 'all') {
        $filters .= " AND t.assigned_to = :user_id ";
        $params[':user_id'] = $user_id;
    }

    // 3. Main Analytics Query
    $query = "SELECT 
                p.name as project_name,
                COUNT(t.id) as total_tasks,
                SUM(CASE WHEN t.is_completed = 1 THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN t.is_completed = 0 THEN 1 ELSE 0 END) as pending_count
              FROM project_tbl p
              LEFT JOIN task_tbl t ON p.id = t.project_id $filters
              GROUP BY p.id
              ORDER BY p.name ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Analytics - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
</head>
<body class="bg-slate-50 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">System Analytics</h1>
                        <p class="text-slate-500 font-medium italic">Filter project performance by date, status, or member.</p>
                    </div>

                    <a href="modules/export_reports.php?<?php echo http_build_query($_GET); ?>" 
                       class="inline-flex items-center gap-2 bg-slate-900 hover:bg-black text-white px-6 py-3.5 rounded-2xl font-bold shadow-xl transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Download PDF Report
                    </a>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-10">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">From Date</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">To Date</label>
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status</label>
                            <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="all" <?php echo $status_val == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="completed" <?php echo $status_val == 'completed' ? 'selected' : ''; ?>>Completed Only</option>
                                <option value="pending" <?php echo $status_val == 'pending' ? 'selected' : ''; ?>>Pending Only</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Team Member</label>
                            <select name="user_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="all">Every Member</option>
                                <?php foreach ($all_users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo $user_id == $u['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">Apply</button>
                            <a href="reports.php" class="bg-slate-100 hover:bg-slate-200 text-slate-500 p-3 rounded-xl transition flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Project Name</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Volume</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status Mix</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Efficiency</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if (empty($reports)): ?>
                                <tr><td colspan="5" class="px-8 py-16 text-center text-slate-400 font-medium italic">No data matching your current filters.</td></tr>
                            <?php else: foreach ($reports as $row): 
                                $total = $row['total_tasks'];
                                $done = $row['completed_count'];
                                $percent = ($total > 0) ? round(($done / $total) * 100) : 0;
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-6 font-bold text-slate-800"><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td class="px-8 py-6 text-center font-bold text-slate-500"><?php echo $total; ?></td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-2">
                                        <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded text-[10px] font-black border border-emerald-100">Done: <?php echo $done; ?></span>
                                        <span class="bg-amber-50 text-amber-600 px-2 py-1 rounded text-[10px] font-black border border-amber-100">Left: <?php echo $row['pending_count']; ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 w-64">
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full transition-all duration-1000" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right font-black text-slate-700"><?php echo $percent; ?>%</td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include '../includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>