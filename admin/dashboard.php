<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

try {
    // 1. Basic Stats
    $proj_count = $pdo->query("SELECT COUNT(*) FROM project_tbl")->fetchColumn() ?: 0;
    $user_count = $pdo->query("SELECT COUNT(*) FROM users_tbl WHERE role = 'USER'")->fetchColumn() ?: 0;

    // 2. Completion Rate
    $task_stats = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as done FROM task_tbl")->fetch(PDO::FETCH_ASSOC);
    $total_tasks = $task_stats['total'] ?? 0;
    $completed_tasks = $task_stats['done'] ?? 0;
    $completion_rate = ($total_tasks > 0) ? round(($completed_tasks / $total_tasks) * 100) : 0;

    // 3. Today's Pending Focus
    $today = date('Y-m-d');
    $today_stmt = $pdo->prepare("SELECT COUNT(*) FROM task_tbl WHERE is_completed = 0 AND DATE(created_at) = ?");
    $today_stmt->execute([$today]);
    $pending_today = $today_stmt->fetchColumn() ?: 0;

    // 4. Recent Activity
    $recent_tasks = $pdo->query("SELECT t.title, p.name as project_name, u.username as assigned_user, t.is_completed 
                          FROM task_tbl t
                          JOIN project_tbl p ON t.project_id = p.id
                          JOIN users_tbl u ON t.assigned_to = u.id
                          ORDER BY t.created_at DESC LIMIT 5")->fetchAll();

} catch (PDOException $e) {
    die("Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TMS Admin</title>
    <link rel="stylesheet" href="../public/css/output.css">
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900">

    <div class="flex h-screen overflow-hidden">
        
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col min-w-0 bg-slate-50 overflow-hidden">
            
            <?php include '../admin/includes/header.php'; ?>

            <div class="flex-1 overflow-y-auto p-6 lg:p-10">
                <div class="max-w-7xl mx-auto">
                    
                    <div class="mb-10">
                        <h1 class="text-3xl font-black tracking-tight text-slate-800 uppercase">Dashboard Overview</h1>
                        <p class="text-slate-500 text-sm font-medium">Welcome back, <span class="text-blue-600 font-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-blue-200 transition-all group">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 group-hover:text-blue-400 transition-colors">Due Today</p>
                            <h3 class="text-3xl font-black <?php echo $pending_today > 0 ? 'text-amber-500' : 'text-slate-800'; ?>"><?php echo $pending_today; ?></h3>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Active Projects</p>
                            <h3 class="text-3xl font-black text-slate-800"><?php echo $proj_count; ?></h3>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Team Members</p>
                            <h3 class="text-3xl font-black text-slate-800"><?php echo $user_count; ?></h3>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 text-blue-500">System Success</p>
                            <h3 class="text-3xl font-black text-blue-600"><?php echo $completion_rate; ?>%</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                        
                        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                                <h2 class="font-black text-slate-800 text-xs uppercase tracking-widest">Recent Activity</h2>
                                <span class="flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Live
                                </span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <tbody class="divide-y divide-slate-50">
                                        <?php if(empty($recent_tasks)): ?>
                                            <tr><td class="p-16 text-center text-slate-400 text-xs italic font-medium">No activity recorded yet.</td></tr>
                                        <?php else: foreach ($recent_tasks as $task): ?>
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="p-5">
                                                <p class="text-sm font-black text-slate-800 mb-0.5"><?php echo htmlspecialchars($task['title']); ?></p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[9px] font-black text-blue-500 uppercase"><?php echo htmlspecialchars($task['project_name']); ?></span>
                                                    <span class="text-[9px] font-bold text-slate-300">•</span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase"><?php echo htmlspecialchars($task['assigned_user']); ?></span>
                                                </div>
                                            </td>
                                            <td class="p-5 text-right">
                                                <span class="px-3 py-1 rounded-full text-[9px] font-black <?php echo $task['is_completed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'; ?>">
                                                    <?php echo $task['is_completed'] ? 'COMPLETED' : 'PENDING'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-slate-900 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden group">
                                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-600/20 rounded-full blur-2xl group-hover:bg-blue-600/40 transition-all"></div>
                                <h2 class="text-[10px] font-black uppercase tracking-widest mb-6 text-slate-400">Quick Actions</h2>
                                <div class="grid gap-4 relative z-10">
                                    <a href="reports.php" class="flex items-center justify-between bg-white/5 hover:bg-white/10 p-4 rounded-2xl transition-all font-bold text-xs group/btn border border-white/5">
                                        View Full Reports
                                        <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                    <a href="projects.php" class="flex items-center justify-between bg-blue-600 hover:bg-blue-700 p-4 rounded-2xl transition-all font-black text-xs shadow-lg shadow-blue-900/50">
                                        New Project
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Environment</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-700 leading-none">PHP 8.x / MariaDB</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">Connection Stable</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <?php include '../admin/includes/footer.php'; ?>
        </main>
    </div>

</body>
</html>