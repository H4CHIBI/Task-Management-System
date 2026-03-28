<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

try {
    // Fetch all projects
    $stmt = $pdo->query("SELECT * FROM project_tbl ORDER BY id DESC");
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Projects - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ openProjectModal: false }">

    <div class="flex h-screen overflow-hidden">
        <?php include '../includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../includes/header.php'; ?>

            <div class="p-6 max-w-7xl mx-auto w-full">
                
                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-xl font-bold border border-green-200">
                        <?php 
                            if($_GET['msg'] == 'project_added') echo "✅ Project created successfully!";
                            if($_GET['msg'] == 'project_deleted') echo "🗑️ Project removed successfully!";
                        ?>
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800">Project Management</h1>
                        <p class="text-sm text-gray-500">Create and organize your main project folders.</p>
                    </div>
                    <button @click="openProjectModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New Project
                    </button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-xs font-bold text-gray-400 uppercase">
                            <tr>
                                <th class="px-6 py-4">Project Name</th>
                                <th class="px-6 py-4">Created At</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($projects)): ?>
                                <tr><td colspan="3" class="px-6 py-12 text-center text-gray-400 italic">No projects yet.</td></tr>
                            <?php else: foreach ($projects as $project): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </td>
            
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php 
                                            echo !empty($project['created_at']) 
                                                ? date('M d, Y', strtotime($project['created_at'])) 
                                                : 'N/A'; 
                                        ?>
                                    </td>
            
                                    <td class="px-6 py-4 text-right">
                                        <a href="modules/project_module.php?action=delete&id=<?php echo $project['id']; ?>" 
                                            class="text-red-500 font-bold hover:text-red-700 transition" 
                                            onclick="return confirm('Delete this project?')">DELETE</a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="openProjectModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full z-50 p-6">
                        <h3 class="text-xl font-black text-slate-800 mb-4">Add New Project</h3>
                        
                        <form action="modules/project_module.php" method="POST" class="space-y-4">
                            <input type="hidden" name="add_project" value="1">
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Project Title</label>
                                <input type="text" name="project_name" required placeholder="e.g., Web Development" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" @click="openProjectModal = false" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-lg">Cancel</button>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include '../includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>