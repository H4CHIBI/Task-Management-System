<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

require_once '../config/config.php';
$user_id = $_SESSION['user_id'];

try {
    // 1. Overview Stats
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as pending
        FROM task_tbl WHERE assigned_to = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    // 2. Performance by Project
    $projStmt = $pdo->prepare("SELECT 
        p.name as project_name,
        COUNT(t.id) as total_tasks,
        SUM(CASE WHEN t.is_completed = 1 THEN 1 ELSE 0 END) as done
        FROM project_tbl p
        JOIN task_tbl t ON p.id = t.project_id
        WHERE t.assigned_to = ?
        GROUP BY p.id
        ORDER BY p.name ASC");
    $projStmt->execute([$user_id]);
    $projects = $projStmt->fetchAll();

    $efficiency = ($stats['total'] > 0) ? ($stats['completed'] / $stats['total']) * 100 : 0;

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports & Analytics - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .progress-glow { box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased" x-data="{}">

    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include 'includes/header.php'; ?>

            <div class="p-6 lg:p-12 max-w-6xl mx-auto w-full">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-6">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Analytics Dashboard</h1>
                        <p class="text-slate-500 font-medium text-sm">Performance tracking for your assigned projects.</p>
                    </div>

                    <a href="modules/generate_report.php" target="_blank" 
                       class="inline-flex items-center gap-2 bg-slate-900 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl text-xs font-bold transition-all shadow-lg shadow-slate-200 active:scale-95 group">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Workload</span>
                        <div class="text-3xl font-black text-slate-900 mt-1"><?= $stats['total'] ?></div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">Total Tasks Assigned</div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Completed</span>
                        <div class="text-3xl font-black text-slate-900 mt-1"><?= $stats['completed'] ?? 0 ?></div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">Finished Milestones</div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">In Progress</span>
                        <div class="text-3xl font-black text-slate-900 mt-1"><?= $stats['pending'] ?? 0 ?></div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">Active Pending Tasks</div>
                    </div>

                    <div class="bg-slate-900 p-6 rounded-2xl shadow-xl text-white">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Efficiency</span>
                        <div class="text-3xl font-black mt-1"><?= round($efficiency) ?>%</div>
                        <div class="w-full bg-slate-800 h-1 rounded-full mt-4 overflow-hidden">
                            <div class="bg-emerald-400 h-full progress-glow" style="width: <?= $efficiency ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                        <h3 class="font-black text-slate-900 text-xs uppercase tracking-widest">Project Completion Summary</h3>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Live Data</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="px-8 py-4">Assigned Project</th>
                                    <th class="px-8 py-4">Task Ratio</th>
                                    <th class="px-8 py-4 text-right">Progress</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(empty($projects)): ?>
                                    <tr>
                                        <td colspan="3" class="px-8 py-16 text-center text-slate-300 font-bold text-xs uppercase tracking-widest">No project data found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $proj): 
                                        $p_rate = ($proj['total_tasks'] > 0) ? ($proj['done'] / $proj['total_tasks']) * 100 : 0;
                                    ?>
                                        <tr class="hover:bg-slate-50/30 transition-colors group">
                                            <td class="px-8 py-6">
                                                <div class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition-colors">
                                                    <?= htmlspecialchars($proj['project_name']) ?>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-4">
                                                    <div class="flex-1 max-w-[200px] bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                                        <div class="bg-emerald-500 h-full transition-all duration-700" style="width: <?= $p_rate ?>%"></div>
                                                    </div>
                                                    <span class="text-[10px] font-bold text-slate-400"><?= $proj['done'] ?> / <?= $proj['total_tasks'] ?></span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <span class="inline-block px-3 py-1 rounded-lg text-[11px] font-black <?= ($p_rate == 100) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-600' ?>">
                                                    <?= round($p_rate) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <div class="inline-block px-6 py-2 bg-slate-100 rounded-full border border-slate-200">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em]">
                            System Record &bull; Generated <?= date('F d, Y') ?> &bull; <?= date('h:i A') ?>
                        </p>
                    </div>
                </div>

            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

</body>
</html>