<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

try {
    // 1. Fetch tasks with project names using a LEFT JOIN
    // Note: Ensuring we use 'title' from your task_tbl and 'project_name' from project_tbl
    $sql = "SELECT t.*, p.name 
            FROM task_tbl t 
            LEFT JOIN project_tbl p ON t.project_id = p.id 
            ORDER BY t.id DESC";
    $stmt = $pdo->query($sql);
    $tasks = $stmt->fetchAll();

    // 2. Fetch projects for the dropdown in the modal
    $projects = $pdo->query("SELECT id, name FROM project_tbl")->fetchAll();
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
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ openTaskModal: false }">

    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../includes/header.php'; ?>

            <div class="p-6 max-w-7xl mx-auto w-full">
                
                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-4 p-4 bg-blue-100 text-blue-700 rounded-xl font-bold border border-blue-200 shadow-sm">
                        <?php 
                            if($_GET['msg'] == 'task_added') echo "✅ Task successfully assigned!";
                            if($_GET['msg'] == 'task_deleted') echo "🗑️ Task removed from system.";
                        ?>
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Task Management</h1>
                        <p class="text-sm text-gray-500 italic">Assign and track specific items for your projects.</p>
                    </div>
                    <button @click="openTaskModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add New Task
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Task Details</th>
                                <th class="px-6 py-5">Assigned Project</th>
                                <th class="px-6 py-5">Priority</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 italic font-medium">No tasks found. Click "Add New Task" to start.</td></tr>
                            <?php else: foreach ($tasks as $task): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   class="sr-only peer" 
                                                   <?php echo $task['is_completed'] ? 'checked' : ''; ?>
                                                   onchange="toggleTaskStatus(<?php echo $task['id']; ?>, this.checked)">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800 <?php echo $task['is_completed'] ? 'line-through text-gray-400' : ''; ?>" id="task-title-<?php echo $task['id']; ?>">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                            <?php echo htmlspecialchars($task['name'] ?? 'Unassigned'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[10px] font-black uppercase text-slate-500"><?php echo htmlspecialchars($task['priority']); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="modules/task_module.php?action=delete&id=<?php echo $task['id']; ?>" 
                                           onclick="return confirm('Remove this task?')"
                                           class="text-red-500 hover:text-red-700 text-xs font-black uppercase tracking-tighter">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="openTaskModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div @click="openTaskModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full z-50 p-8 transform transition-all">
                        <h3 class="text-2xl font-black text-slate-800 mb-6">Create New Task</h3>
                        
                        <form action="modules/task_module.php" method="POST" class="space-y-5">
                            <input type="hidden" name="add_task" value="1">
    
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Task Title</label>
                                <input type="text" name="title" required placeholder="What needs to be done?" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Project Folder</label>
                                <select name="project_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all cursor-pointer">
                                    <option value="" disabled selected>Select a Project...</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project['id']; ?>">
                                            <?php echo htmlspecialchars($project['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Priority Level</label>
                                <select name="priority" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all cursor-pointer">
                                    <option value="1">Low</option>
                                    <option value="2" selected>Medium</option>
                                    <option value="3">High</option>
                                </select>
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" @click="openTaskModal = false" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition">Cancel</button>
                                <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition">Save Task</button>
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
        const statusValue = isChecked ? 1 : 0;
        const taskTitle = document.getElementById('task-title-' + taskId);

        fetch('modules/update_task_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                task_id: taskId,
                is_completed: statusValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Visual feedback: Strike through the text when done
                if (isChecked) {
                    taskTitle.classList.add('line-through', 'text-gray-400');
                } else {
                    taskTitle.classList.remove('line-through', 'text-gray-400');
                }
            } else {
                alert('Error: ' + data.message);
                location.reload(); 
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Server connection failed.');
        });
    }
    </script>
</body>
</html>