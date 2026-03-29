<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

try {
    // Removed created_at from the query to match your actual database schema
    $stmt = $pdo->query("SELECT id, username, role, profile_image FROM users_tbl ORDER BY id DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 antialiased" x-data="{ openUserModal: false }">

    <div class="flex h-screen overflow-hidden">
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../admin/includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full">
                
                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl font-bold border border-emerald-100">
                        <span><?php 
                            if($_GET['msg'] == 'user_added') echo "✨ New team member joined!";
                            if($_GET['msg'] == 'user_deleted') echo "🗑️ User account removed.";
                        ?></span>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Team Management</h1>
                        <p class="text-slate-500 font-medium">Currently managing <span class="text-blue-600 font-bold"><?php echo count($users); ?></span> accounts.</p>
                    </div>
                    
                    <button @click="openUserModal = true" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold shadow-xl shadow-blue-100 transition-all active:scale-95 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        Add New User
                    </button>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-6 py-6">User Details</th>
                                <th class="px-6 py-6">Access Role</th>
                                <th class="px-6 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($users as $user): ?>
                                <tr class="group hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative w-12 h-12">
                                                <img src="../public/uploads/<?php echo htmlspecialchars($user['profile_image'] ?: 'default_profile.png'); ?>" 
                                                     class="w-full h-full rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-slate-100">
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-800 tracking-tight"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">System ID: #<?php echo $user['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border <?php echo $user['role'] === 'ADMIN' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-slate-50 text-slate-500 border-slate-100'; ?>">
                                            <?php echo $user['role']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="modules/user_module.php?action=delete&id=<?php echo $user['id']; ?>" 
                                               onclick="return confirm('Permanently remove this user?')"
                                               class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-red-500 transition-all p-2 inline-block">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] font-black text-blue-500 italic bg-blue-50 px-2 py-1 rounded-md">Current Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="openUserModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak x-transition>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div @click="openUserModal = false" class="fixed inset-0 bg-slate-900/70 backdrop-blur-md"></div>
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full z-50 p-10 relative overflow-hidden">
                        <h3 class="text-3xl font-black text-slate-900 mb-8">Register User</h3>
                        <form action="modules/user_module.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <input type="hidden" name="add_user" value="1">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Username</label>
                                <input type="text" name="username" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Password</label>
                                <input type="password" name="password" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Role</label>
                                    <select name="role" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold">
                                        <option value="USER">User</option>
                                        <option value="ADMIN">Admin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Photo</label>
                                    <input type="file" name="profile_image" accept="image/*" class="w-full text-xs">
                                </div>
                            </div>
                            <div class="flex justify-end gap-4 pt-4">
                                <button type="button" @click="openUserModal = false" class="px-6 py-3 font-bold">Discard</button>
                                <button type="submit" class="px-10 py-3 bg-blue-600 text-white font-black rounded-2xl">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>