<?php
// Identify the current page for active styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300"></div>

<aside id="sidebar" class="bg-slate-900 text-white w-64 min-h-screen fixed md:relative z-50 flex flex-col shadow-2xl md:shadow-none -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    
    <div class="p-6 mb-4">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h1 class="text-xl font-black tracking-tighter uppercase leading-none">
                TMS <span class="text-blue-500 block text-[10px] tracking-widest">Admin</span>
            </h1>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-2">
        <a href="dashboard.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'dashboard.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="font-bold text-sm">Dashboard</span>
        </a>

        <a href="users.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'users.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <span class="font-bold text-sm">Manage Users</span>
        </a>

        <a href="projects.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'projects.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <span class="font-bold text-sm">Projects</span>
        </a>

        <a href="tasks.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'tasks.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="font-bold text-sm">Tasks</span>
        </a>

        <a href="task_history.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'task_history.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="font-bold text-sm">Task History</span>
        </a>

        <a href="reports.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'reports.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span class="font-bold text-sm">Reports & Stats</span>
        </a>
    </nav>

    <div class="p-4 border-t border-white/5">
        <a href="../auth/logout.php" class="flex items-center gap-4 p-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="font-bold text-sm">Logout</span>
        </a>
    </div>
</aside>