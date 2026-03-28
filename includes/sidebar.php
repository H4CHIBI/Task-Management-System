<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<aside class="w-64 bg-slate-900 text-white flex-shrink-0 hidden md:flex flex-col">
    <div class="p-6 border-b border-slate-800">
        <h1 class="text-xl font-bold tracking-wider text-blue-400">TMS Admin</h1>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo ($current_page == 'dashboard.php') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800'; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="../admin/projects.php" class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo ($current_page == 'projects.php') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800'; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
            <span>Projects</span>
        </a>

        <a href="../admin/tasks.php" class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo ($current_page == 'tasks.php') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800'; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>Tasks</span>
        </a>
        
        </nav>

    <div class="p-4 border-t border-slate-800">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-red-900/20 rounded-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
            </svg>
            <span>Logout</span>
        </a>
    </div>
</aside>