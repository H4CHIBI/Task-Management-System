<?php
// Identify the current page for active styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300"></div>

<aside id="sidebar" class="bg-slate-900 text-white w-64 min-h-screen transition-all duration-300 ease-in-out 
    fixed md:relative z-50 flex flex-col shadow-2xl md:shadow-none -translate-x-full md:translate-x-0">
    
    <button id="toggleSidebar" class="absolute -right-10 top-10 bg-blue-600 text-white p-2 rounded-r-xl shadow-xl hover:bg-blue-700 transition-all z-50 hidden md:block">
        <svg id="toggleIcon" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <div class="p-6 mb-4">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h1 class="sidebar-text text-xl font-black tracking-tighter uppercase leading-none">
                TMS <span class="text-blue-500 block text-[10px] tracking-widest">Admin</span>
            </h1>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-2">
        <a href="dashboard.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'dashboard.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="sidebar-text font-bold text-sm">Dashboard</span>
        </a>

        <a href="projects.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'projects.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <span class="sidebar-text font-bold text-sm">Projects</span>
        </a>

        <a href="tasks.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'tasks.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="sidebar-text font-bold text-sm">Tasks</span>
        </a>

        <a href="reports.php" class="flex items-center gap-4 p-3 rounded-xl transition-all <?php echo ($current_page == 'reports.php') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-white/5 text-slate-400 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span class="sidebar-text font-bold text-sm">Reports & Stats</span>
        </a>
    </nav>

    <div class="p-4 border-t border-white/5">
        <a href="../logout.php" class="flex items-center gap-4 p-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="sidebar-text font-bold text-sm">Logout</span>
        </a>
    </div>
</aside>

<style>
    /* Desktop Hide State: Forces the sidebar off-screen and removes its width from the layout */
    .sidebar-hidden-desktop {
        margin-left: -16rem !important; 
    }

    /* Rotates the toggle icon when the sidebar is hidden */
    .sidebar-hidden-desktop #toggleIcon {
        transform: rotate(180deg);
    }

    /* Mobile Slide-in State */
    .mobile-sidebar-open {
        transform: translateX(0) !important;
    }

    /* Smooth transition for both margin and transform */
    #sidebar {
        transition: margin-left 0.3s ease-in-out, transform 0.3s ease-in-out;
    }
</style>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Check local storage and apply state BEFORE transitions are active to prevent flicker
    if (localStorage.getItem('sidebarStatus') === 'hidden' && window.innerWidth > 768) {
        sidebar.classList.add('sidebar-hidden-desktop');
    }

    // Toggle Desktop Sidebar
    toggleBtn?.addEventListener('click', () => {
        const isHidden = sidebar.classList.toggle('sidebar-hidden-desktop');
        localStorage.setItem('sidebarStatus', isHidden ? 'hidden' : 'visible');
    });

    // Mobile logic: Using a function to keep it DRY
    const toggleMobileMenu = (isOpen) => {
        sidebar.classList.toggle('mobile-sidebar-open', isOpen);
        overlay.classList.toggle('hidden', !isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen); // Prevent background scroll
    };

    // Assuming your mobileBtn is in a separate header file:
    document.getElementById('mobileMenuBtn')?.addEventListener('click', () => toggleMobileMenu(true));
    overlay?.addEventListener('click', () => toggleMobileMenu(false));
</script>