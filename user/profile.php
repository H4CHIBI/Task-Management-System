<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/config.php';
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
<body class="bg-[#f8fafc] text-slate-900 antialiased">

    <div class="flex h-screen overflow-hidden">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 flex flex-col overflow-y-auto">
            <?php include 'includes/header.php'; ?>

            <div class="p-6 lg:p-12 max-w-4xl mx-auto w-full">
                <div class="mb-10">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Account Settings</h1>
                    <p class="text-slate-500 font-medium text-sm">Update your security credentials and profile information.</p>
                </div>

                <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm overflow-hidden">
                    <form action="modules/profile_module.php" method="POST" enctype="multipart/form-data" class="p-8 lg:p-12">
                        
                        <div class="flex items-center gap-8 mb-12 pb-12 border-b border-slate-50">
                            <div class="relative group">
                                <img src="../public/uploads/<?= $_SESSION['profile_image'] ?>" 
                                     class="w-24 h-24 rounded-[2rem] object-cover border-4 border-slate-100 shadow-xl" id="preview">
                                <label class="absolute inset-0 flex items-center justify-center bg-slate-900/40 rounded-[2rem] opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                    <input type="file" name="profile_image" class="hidden" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-width="2"/></svg>
                                </label>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 text-lg"><?= htmlspecialchars($_SESSION['username']) ?></h3>
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-1">System <?= $_SESSION['role'] ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($_SESSION['username']) ?>" 
                                       class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Password</label>
                                <input type="password" name="new_password" placeholder="Leave blank to keep current" 
                                       class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            </div>
                        </div>

                        <div class="mt-12 flex justify-end">
                            <button type="submit" class="bg-slate-900 hover:bg-emerald-600 text-white px-10 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
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