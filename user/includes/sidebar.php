<aside class="w-72 bg-slate-900 h-screen flex flex-col flex-shrink-0 transition-all duration-300 border-r border-slate-800">
    <div class="p-8">
        <div class="flex items-center gap-3 group cursor-pointer">
            <div class="w-11 h-11 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="text-white font-black text-2xl tracking-tighter uppercase leading-none">TMS<span class="text-emerald-500">.</span></span>
                <span class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mt-1">Workspace</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-1.5 mt-4">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        
        function isActive($page, $current) {
            return $page === $current 
                ? 'bg-emerald-600 text-white shadow-xl shadow-emerald-900/20' 
                : 'text-slate-400 hover:bg-slate-800/50 hover:text-white';
        }
        ?>

        <a href="user_dashboard.php" class="flex items-center gap-4 px-5 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest transition-all group <?php echo isActive('user_dashboard.php', $current_page); ?>">
            <svg class="w-5 h-5 transition-colors <?php echo $current_page == 'user_dashboard.php' ? 'text-white' : 'text-slate-600 group-hover:text-emerald-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="tasks.php" class="flex items-center gap-4 px-5 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest transition-all group <?php echo isActive('tasks.php', $current_page); ?>">
            <svg class="w-5 h-5 transition-colors <?php echo $current_page == 'tasks.php' ? 'text-white' : 'text-slate-600 group-hover:text-emerald-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            My Tasks
        </a>

        <a href="task_history.php" class="flex items-center gap-4 px-5 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest transition-all group <?php echo isActive('task_history.php', $current_page); ?>">
            <svg class="w-5 h-5 transition-colors <?php echo $current_page == 'task_history.php' ? 'text-white' : 'text-slate-600 group-hover:text-emerald-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            History
        </a>

        <a href="reports.php" class="flex items-center gap-4 px-5 py-4 rounded-2xl font-bold text-xs uppercase tracking-widest transition-all group <?php echo isActive('reports.php', $current_page); ?>">
            <svg class="w-5 h-5 transition-colors <?php echo $current_page == 'reports.php' ? 'text-white' : 'text-slate-600 group-hover:text-emerald-400'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Reports
        </a>
    </nav>

    <div class="px-6 py-8 mt-auto">
        <div class="group relative bg-slate-800/30 hover:bg-slate-800/50 p-4 rounded-[2rem] border border-slate-800/50 transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <img src="../public/uploads/<?php echo $_SESSION['profile_image']; ?>" 
                         class="w-12 h-12 rounded-2xl object-cover border-2 border-slate-700/50 shadow-lg ring-2 ring-emerald-500/10" 
                         alt="Profile">
                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-4 border-slate-900 rounded-full animate-pulse"></span>
                </div>
                
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-black text-white truncate tracking-tight">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </h4>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Online Now</span>
                    </div>
                </div>

                <a href="../auth/logout.php" class="text-slate-500 hover:text-rose-500 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</aside>