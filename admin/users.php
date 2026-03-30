<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../index.php?error=unauthorized");
    exit();
}
require_once '../config/config.php';

// --- PAGINATION & LIMIT LOGIC ---
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
$limit = in_array($limit, [5, 10, 20, 50]) ? $limit : 10; 

try {
    // Fetch users with the selected limit
    $stmt = $pdo->prepare("SELECT id, username, role, profile_image FROM users_tbl ORDER BY id DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $total_users = $pdo->query("SELECT COUNT(*) FROM users_tbl")->fetchColumn();
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
<body class="bg-slate-50 antialiased" x-data="{ 
    openUserModal: false, 
    openEditModal: false,
    editUser: { id: '', username: '', role: '' }
}">

    <div class="flex h-screen overflow-hidden">
        <?php include '../admin/includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include '../admin/includes/header.php'; ?>

            <div class="p-8 max-w-7xl mx-auto w-full">
                
                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-6 p-4 rounded-2xl border font-bold text-sm flex items-center gap-3 animate-pulse bg-emerald-50 border-emerald-100 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <?php 
                            if($_GET['msg'] == 'user_updated') echo "Account updated successfully!";
                            if($_GET['msg'] == 'user_added') echo "New team member registered!";
                            if($_GET['msg'] == 'user_deleted') echo "User has been removed.";
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] == 'self_delete'): ?>
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 font-bold text-sm flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Security Alert: You cannot delete your own account!
                    </div>
                <?php endif; ?>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Team Management</h1>
                        <p class="text-slate-500 font-medium">Currently managing <span class="text-blue-600 font-bold"><?php echo $total_users; ?></span> accounts.</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <form method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                            <select name="limit" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer">
                                <?php foreach([5, 10, 20, 50] as $opt): ?>
                                    <option value="<?php echo $opt; ?>" <?php echo ($limit == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?> Rows</option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <button @click="openUserModal = true" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold shadow-xl shadow-blue-100 transition-all active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            Add Member
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b">
                            <tr>
                                <th class="px-6 py-4">User Details</th>
                                <th class="px-6 py-4">Role Access</th>
                                <th class="px-6 py-4 text-right">Actions</th>
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
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button @click="openEditModal = true; editUser = { id: '<?php echo $user['id']; ?>', username: '<?php echo htmlspecialchars($user['username']); ?>', role: '<?php echo $user['role']; ?>' }"
                                                class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-blue-600 transition-all p-2 inline-block">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="modules/user_module.php?action=delete&id=<?php echo $user['id']; ?>" 
                                               onclick="return confirm('Permanently remove this user?')"
                                               class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-red-500 transition-all p-2 inline-block">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </a>
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
                        <form action="modules/user_module.php" method="POST" enctype="multipart/form-data" class="space-y-6"
                              x-data="{ p1: '', p2: '' }" @submit="if(p1 !== p2) { alert('Passwords do not match!'); $event.preventDefault(); }">
                            <input type="hidden" name="add_user" value="1">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Username</label>
                                <input type="text" name="username" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 font-bold">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Password</label>
                                    <input type="password" name="password" x-model="p1" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 font-bold">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm</label>
                                    <input type="password" x-model="p2" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-blue-500 font-bold">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Role</label>
                                    <select name="role" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-black text-xs">
                                        <option value="USER">User</option>
                                        <option value="ADMIN">Admin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Photo</label>
                                    <input type="file" name="profile_image" accept="image/*" class="w-full text-[10px] font-bold text-slate-400">
                                </div>
                            </div>
                            <div class="flex justify-end gap-4 pt-4">
                                <button type="button" @click="openUserModal = false" class="px-6 py-3 font-bold text-slate-400 text-sm">Discard</button>
                                <button type="submit" class="px-10 py-3 bg-blue-600 text-white font-black rounded-2xl shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">Save Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak x-transition>
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div @click="openEditModal = false" class="fixed inset-0 bg-slate-900/70 backdrop-blur-md"></div>
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full z-50 p-10 relative">
                        <h3 class="text-3xl font-black text-slate-900 mb-8">Update Member</h3>
                        <form action="modules/user_module.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <input type="hidden" name="update_user" value="1">
                            <input type="hidden" name="id" x-model="editUser.id">
                            
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Username</label>
                                <input type="text" name="username" x-model="editUser.username" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Change Role</label>
                                    <select name="role" x-model="editUser.role" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-black text-xs">
                                        <option value="USER">User</option>
                                        <option value="ADMIN">Admin</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">New Photo</label>
                                    <input type="file" name="profile_image" accept="image/*" class="w-full text-[10px] font-bold text-slate-400">
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="block text-[10px] font-black text-blue-600 uppercase tracking-widest mb-2 px-1">Security</label>
                                <input type="password" name="password" placeholder="New Password (optional)" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none text-sm font-bold">
                            </div>

                            <div class="flex justify-end gap-4 pt-4">
                                <button type="button" @click="openEditModal = false" class="px-6 py-3 font-bold text-slate-400 text-sm">Cancel</button>
                                <button type="submit" class="px-10 py-3 bg-slate-900 text-white font-black rounded-2xl hover:bg-blue-600 transition-all">Update Access</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>
</html>