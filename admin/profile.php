<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/config.php';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users_tbl WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - TMS</title>
    <link rel="stylesheet" href="../public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 antialiased">
    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>
        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include 'includes/header.php'; ?>

            <div class="p-8 max-w-4xl mx-auto w-full">
                <div class="mb-10">
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">Account Settings</h1>
                    <p class="text-slate-500 font-medium">Update your personal information and security credentials.</p>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl font-bold border border-emerald-100">
                        <?php 
                            if($_GET['msg'] == 'updated') echo "✅ Profile updated successfully!";
                        ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <form action="modules/profile_module.php" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                        
                        <div class="flex items-center gap-8 pb-8 border-b border-slate-50">
                            <div class="relative group">
                                <img src="../public/uploads/<?php echo htmlspecialchars($user['profile_image'] ?: 'default_profile.png'); ?>" 
                                     class="w-32 h-32 rounded-3xl object-cover ring-4 ring-slate-50 shadow-lg">
                                <div class="absolute inset-0 bg-black/40 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-width="2"/><path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/></svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 text-lg">Profile Picture</h3>
                                <p class="text-slate-400 text-xs mb-4">JPG or PNG. Max size of 800K</p>
                                <input type="file" name="profile_image" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Username</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required 
                                       class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Account Role</label>
                                <input type="text" value="<?php echo $user['role']; ?>" disabled 
                                       class="w-full px-6 py-4 bg-slate-100 border border-slate-200 rounded-2xl font-bold text-slate-400 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="pt-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">New Password (leave blank to keep current)</label>
                            <input type="password" name="new_password" placeholder="••••••••••••"
                                   class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700">
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl font-black shadow-xl shadow-blue-100 transition-all active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>