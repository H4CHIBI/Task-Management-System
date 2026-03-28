<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

$filter = $_GET['priority_filter'] ?? 'all';

try {
    // 1. Fetch tasks with project names
    $query = "SELECT t.*, p.name as project_name 
              FROM task_tbl t 
              LEFT JOIN project_tbl p ON t.project_id = p.id";
    
    if ($filter !== 'all') {
        $query .= " WHERE t.priority = :priority";
    }
    
    $query .= " ORDER BY t.id DESC";
    
    $stmt = $pdo->prepare($query);
    if ($filter !== 'all') {
        $stmt->bindValue(':priority', $filter, PDO::PARAM_INT);
    }
    $stmt->execute();
    $tasks = $stmt->fetchAll();

    // 2. Fetch projects for the dropdown
    $projects = $pdo->query("SELECT id, name FROM project_tbl")->fetchAll();

    // 3. Calculate Progress Stats
    $totalTasks = count($tasks);
    $completedTasks = count(array_filter($tasks, fn($t) => $t['is_completed'] == 1));
    $percent = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100) : 0;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tasks - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ openTaskModal: false }">

    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../includes/header.php'; ?>

            <div class="p-6 max-w-7xl mx-auto w-full">
                
                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl font-bold border border-emerald-100 shadow-sm flex items-center gap-2">
                        <span><?php 
                            if($_GET['msg'] == 'task_added') echo "✅ Task successfully assigned!";
                            if($_GET['msg'] == 'task_deleted') echo "🗑️ Task removed from system.";
                            if($_GET['msg'] == 'tasks_cleared') echo "🧹 All completed tasks have been cleared.";
                        ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Overall Completion</p>
                        <div class="flex items-end gap-2">
                            <span class="text-3xl font-black text-slate-800"><?php echo $percent; ?>%</span>
                            <span class="text-xs text-slate-500 mb-1.5"><?php echo $completedTasks; ?> / <?php echo $totalTasks; ?> Tasks</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full mt-3 overflow-hidden">
                            <div class="bg-blue-600 h-full transition-all duration-500" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Task Management</h1>
                        <p class="text-sm text-gray-500 italic mb-4">Assign and track specific items for your projects.</p>
                        
                        <form method="GET" class="flex items-center gap-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Priority Filter:</label>
                            <select name="priority_filter" onchange="this.form.submit()" 
                                class="text-xs font-bold border border-slate-200 bg-white rounded-lg px-3 py-1.5 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm">
                                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="3" <?php echo $filter == '3' ? 'selected' : ''; ?>>High</option>
                                <option value="2" <?php echo $filter == '2' ? 'selected' : ''; ?>>Medium</option>
                                <option value="1" <?php echo $filter == '1' ? 'selected' : ''; ?>>Low</option>
                            </select>
                        </form>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="modules/task_module.php?action=clear_completed" 
                            onclick="return confirm('Permanently delete ALL completed tasks?')"
                            class="px-5 py-2.5 rounded-xl font-bold text-red-500 hover:bg-red-50 border border-transparent hover:border-red-100 transition flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            Clear Completed
                        </a>

                        <button @click="openTaskModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-100 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Add New Task
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Task Details</th>
                                <th class="px-6 py-5">Project</th>
                                <th class="px-6 py-5">Priority</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No tasks found. Click "Add New Task" to begin.</td></tr>
                            <?php else: foreach ($tasks as $task): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" <?php echo $task['is_completed'] ? 'checked' : ''; ?>
                                                   onchange="toggleTaskStatus(<?php echo $task['id']; ?>, this.checked)">
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 <?php echo $task['is_completed'] ? 'line-through text-gray-400' : ''; ?>" id="task-title-<?php echo $task['id']; ?>">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <?php echo htmlspecialchars($task['project_name'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                            $pColors = [3 => 'bg-red-100 text-red-700', 2 => 'bg-amber-100 text-amber-700', 1 => 'bg-emerald-100 text-emerald-700'];
                                            $pLabels = [3 => 'High', 2 => 'Medium', 1 => 'Low'];
                                            $p = $task['priority'];
                                        ?>
                                        <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase <?php echo $pColors[$p] ?? 'bg-gray-100'; ?>">
                                            <?php echo $pLabels[$p] ?? 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="modules/task_module.php?action=delete&id=<?php echo $task['id']; ?>" 
                                           onclick="return confirm('Remove task?')"
                                           class="text-red-400 hover:text-red-600 text-xs font-black uppercase transition opacity-0 group-hover:opacity-100">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="openTaskModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak x-transition>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div @click="openTaskModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full z-50 p-8">
                        <h3 class="text-2xl font-black text-slate-800 mb-6">Create New Task</h3>
                        <form action="modules/task_module.php" method="POST" class="space-y-5">
                            <input type="hidden" name="add_task" value="1">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Task Title</label>
                                <input type="text" name="title" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Project</label>
                                <select name="project_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Choose project...</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Priority</label>
                                <select name="priority" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1">Low</option>
                                    <option value="2" selected>Medium</option>
                                    <option value="3">High</option>
                                </select>
                            </div>
                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" @click="openTaskModal = false" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl">Cancel</button>
                                <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition hover:bg-blue-700">Save Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include '../includes/footer.php'; ?>
        </main>
    </div>

    <script>
    function toggleTaskStatus(taskId, isChecked) {
        const taskTitle = document.getElementById('task-title-' + taskId);
        fetch('modules/update_task_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task_id: taskId, is_completed: isChecked ? 1 : 0 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                isChecked ? taskTitle.classList.add('line-through', 'text-gray-400') : taskTitle.classList.remove('line-through', 'text-gray-400');
            } else {
                alert('Error updating task');
                location.reload(); 
            }
        });
    }
    </script>
</body>
</html>