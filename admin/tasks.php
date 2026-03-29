<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// Get filters from URL
$priority_filter = $_GET['priority_filter'] ?? 'all';
$user_filter = $_GET['user_filter'] ?? 'all'; // New User Filter

try {
    // 1. Base Query
    $query = "SELECT t.*, p.name as project_name, u.username as assigned_user 
              FROM task_tbl t 
              LEFT JOIN project_tbl p ON t.project_id = p.id
              LEFT JOIN users_tbl u ON t.assigned_to = u.id
              WHERE t.is_completed = 0"; 
    
    // 2. Apply Priority Filter
    if ($priority_filter !== 'all') {
        $query .= " AND t.priority = :priority";
    }

    // 3. Apply User Filter
    if ($user_filter !== 'all') {
        $query .= " AND t.assigned_to = :user_id";
    }
    
    $query .= " ORDER BY t.id DESC";
    
    $stmt = $pdo->prepare($query);

    if ($priority_filter !== 'all') {
        $stmt->bindValue(':priority', $priority_filter, PDO::PARAM_INT);
    }
    if ($user_filter !== 'all') {
        $stmt->bindValue(':user_id', $user_filter, PDO::PARAM_INT);
    }

    $stmt->execute();
    $tasks = $stmt->fetchAll();

    // Data for dropdowns
    $projects = $pdo->query("SELECT id, name FROM project_tbl ORDER BY name ASC")->fetchAll();
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
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 antialiased" 
      x-data="{ 
        openTaskModal: false, 
        isEdit: false,
        formData: { id: '', title: '', project_id: '', priority: 2, assigned_to: '' }
      }">

    <div class="flex h-screen overflow-hidden">
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../admin/includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Active Tasks</h1>
                        <p class="text-slate-500 font-medium">Viewing <?php echo count($tasks); ?> filtered items.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <form method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                            <div class="pl-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter By:</div>
                            
                            <input type="hidden" name="priority_filter" value="<?php echo htmlspecialchars($priority_filter); ?>">

                            <select name="user_filter" onchange="this.form.submit()" 
                                    class="bg-slate-50 border-none text-xs font-bold text-slate-600 rounded-xl focus:ring-0 cursor-pointer py-2 px-4">
                                <option value="all">All Users</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo ($user_filter == $u['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <button @click="isEdit = false; formData = { id: '', title: '', project_id: '', priority: 2, assigned_to: '' }; openTaskModal = true" 
                                class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold shadow-xl hover:bg-indigo-700 transition-all active:scale-95">
                            + New Task
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-6 py-6 w-20 text-center">Done</th>
                                <th class="px-6 py-6">Task Description</th>
                                <th class="px-6 py-6">Project</th>
                                <th class="px-6 py-6">Assignee</th>
                                <th class="px-6 py-6 text-center">Priority</th>
                                <th class="px-6 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="6" class="px-6 py-20 text-center text-slate-400 italic">No tasks found for this user.</td></tr>
                            <?php else: foreach ($tasks as $task): ?>
                                <tr class="group hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <button onclick="markAsDone(<?php echo $task['id']; ?>)" 
                                                class="w-10 h-10 rounded-full border-2 border-slate-200 text-transparent group-hover:border-emerald-500 group-hover:text-emerald-500 hover:!bg-emerald-500 hover:!text-white transition-all flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800 uppercase text-sm"><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-black border border-indigo-100">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-600"><?php echo htmlspecialchars($task['assigned_user'] ?? 'Unassigned'); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <?php 
                                            $p = (int)$task['priority'];
                                            $pColors = [3 => 'bg-red-50 text-red-600', 2 => 'bg-amber-50 text-amber-600', 1 => 'bg-emerald-50 text-emerald-600'];
                                            $pLabels = [3 => 'HIGH', 2 => 'MED', 1 => 'LOW'];
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black <?php echo $pColors[$p] ?? 'bg-slate-100'; ?>">
                                            <?php echo $pLabels[$p] ?? 'MED'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="
                                                isEdit = true;
                                                formData = { 
                                                    id: '<?php echo $task['id']; ?>', 
                                                    title: '<?php echo addslashes($task['title']); ?>', 
                                                    project_id: '<?php echo $task['project_id']; ?>', 
                                                    priority: '<?php echo $task['priority']; ?>',
                                                    assigned_to: '<?php echo $task['assigned_to']; ?>'
                                                };
                                                openTaskModal = true;
                                            " class="p-2 text-slate-400 hover:text-indigo-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <a href="modules/task_module.php?action=delete&id=<?php echo $task['id']; ?>" 
                                               onclick="return confirm('Delete this task?')"
                                               class="p-2 text-slate-400 hover:text-red-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            </main>
    </div>

    <script>
    function markAsDone(taskId) {
        if(!confirm('Complete this task?')) return;
        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        fetch('modules/update_task_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task_id: taskId, is_completed: 1, completed_at: now })
        }).then(() => window.location.reload());
    }
    </script>
</body>
</html>