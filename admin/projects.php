<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// NEW: Get the status filter from the URL (default to 'ONGOING')
$status_filter = $_GET['status_filter'] ?? 'ONGOING';

try {
    // UPDATED: Added WHERE clause to filter by status
    $query = "SELECT p.*, 
              (SELECT COUNT(*) FROM task_tbl t WHERE t.project_id = p.id) as total_tasks,
              (SELECT COUNT(*) FROM task_tbl t WHERE t.project_id = p.id AND t.is_completed = 1) as completed_tasks
              FROM project_tbl p 
              WHERE p.status = :status
              ORDER BY p.id DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute(['status' => $status_filter]);
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Projects - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 font-sans antialiased" x-data="{ openProjectModal: false }">

    <div class="flex h-screen overflow-hidden">
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../admin/includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full">
                
                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl font-bold border border-emerald-100">
                        <span><?php 
                            if($_GET['msg'] == 'project_added') echo "✨ New project launched!";
                            if($_GET['msg'] == 'project_deleted') echo "🗑️ Project archived.";
                            if($_GET['msg'] == 'status_updated') echo "✅ Project status updated.";
                        ?></span>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Projects</h1>
                        <p class="text-slate-500 font-medium italic">Showing <span class="text-indigo-600 font-bold"><?php echo strtolower($status_filter); ?></span> initiatives.</p>
                    </div>
                    
                    <button @click="openProjectModal = true" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Start New Project
                    </button>
                </div>

                <div class="flex gap-2 mb-10 bg-white p-1.5 rounded-2xl border border-slate-100 w-fit shadow-sm">
                    <a href="?status_filter=ONGOING" 
                       class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all <?php echo $status_filter === 'ONGOING' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-400 hover:text-slate-600'; ?>">
                        Ongoing
                    </a>
                    <a href="?status_filter=COMPLETE" 
                       class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all <?php echo $status_filter === 'COMPLETE' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-400 hover:text-slate-600'; ?>">
                        Completed
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php if (empty($projects)): ?>
                        <div class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200 text-slate-400">
                            <p class="text-lg font-bold italic">No <?php echo strtolower($status_filter); ?> projects found.</p>
                        </div>
                    <?php else: foreach ($projects as $project): 
                        $total = (int)$project['total_tasks'];
                        $done = (int)$project['completed_tasks'];
                        $percent = ($total > 0) ? round(($done / $total) * 100) : 0;
                        $isComplete = ($project['status'] === 'COMPLETE');
                    ?>
                        <div class="bg-white rounded-3xl p-7 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all flex flex-col h-full relative group">
                            
                            <div class="flex justify-between items-start mb-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $isComplete ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600'; ?>">
                                    <?php echo $project['status'] ?: 'ONGOING'; ?>
                                </span>
                                
                                <a href="modules/project_module.php?action=delete&id=<?php echo $project['id']; ?>" 
                                   onclick="return confirm('Delete this project? All tasks inside will be lost.')"
                                   class="text-slate-300 hover:text-red-500 transition-colors p-1">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                </a>
                            </div>

                            <div class="flex-grow">
                                <h3 class="text-2xl font-black text-slate-800 mb-3 truncate"><?php echo htmlspecialchars($project['name']); ?></h3>
                                <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3">
                                    <?php echo htmlspecialchars($project['description'] ?: 'No description provided.'); ?>
                                </p>

                                <div class="mb-8">
                                    <div class="flex justify-between items-end mb-2">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Progress</span>
                                        <span class="text-sm font-black text-slate-700"><?php echo $percent; ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-1000 <?php echo $percent == 100 ? 'bg-emerald-500' : 'bg-indigo-500'; ?>" 
                                             style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-2 font-bold italic">
                                        <?php echo $done; ?> of <?php echo $total; ?> tasks finished
                                    </p>
                                </div>
                            </div>

                            <form action="modules/project_module.php" method="POST">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $project['status']; ?>">
                                
                                <button type="submit" 
                                        class="w-full py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 <?php echo $isComplete ? 'bg-slate-800 text-white hover:bg-slate-900' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-100'; ?>">
                                    <?php if($isComplete): ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                        Re-open Project
                                    <?php else: ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M5 13l4 4L19 7" /></svg>
                                        Mark as Complete
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            ... 

            <?php include '../admin/includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>