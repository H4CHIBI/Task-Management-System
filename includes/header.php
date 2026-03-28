<header class="bg-white border-b border-slate-200 sticky top-0 z-40 px-4 py-3">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        
        <div class="flex items-center">
            <button id="mobileMenuBtn" class="block md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">System Status</span>
                <p class="text-xs font-bold text-slate-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Operational
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden lg:flex flex-col items-end border-r border-slate-100 pr-4">
                <p id="headerClock" class="text-sm font-black text-slate-800 leading-none">00:00:00 AM</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter"><?php echo date('l, F j, Y'); ?></p>
            </div>

            <div class="relative">
                <button id="profileBtn" class="flex items-center gap-3 p-1 pr-3 hover:bg-slate-50 rounded-2xl transition-all group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-md shadow-blue-200">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-xs font-black text-slate-800 leading-none"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1"><?php echo $_SESSION['role']; ?></p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-52 bg-white border border-slate-200 rounded-2xl shadow-2xl py-2 z-50">
                    <div class="px-4 py-2 border-b border-slate-50 mb-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Account Info</p>
                    </div>
                    <a href="profile.php" class="flex items-center gap-3 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2"/></svg>
                        My Profile
                    </a>
                    <a href="settings.php" class="flex items-center gap-3 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066" stroke-width="2"/></svg>
                        Account Settings
                    </a>
                    <hr class="my-2 border-slate-100">
                    <a href="../logout.php" class="flex items-center gap-3 px-4 py-2 text-xs font-black text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Sign Out
                    </a>
                </div>
            </div>

        </div>
    </div>
</header>

<script>
    // 1. Live Clock logic
    function updateClock() {
        const el = document.getElementById('headerClock');
        if (!el) return;
        el.textContent = new Date().toLocaleTimeString('en-US', { 
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true 
        });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. Profile Dropdown Toggle
    const pBtn = document.getElementById('profileBtn');
    const pDrop = document.getElementById('profileDropdown');

    if (pBtn && pDrop) {
        pBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            pDrop.classList.toggle('hidden');
        });

        // Close dropdown when clicking anywhere else
        document.addEventListener('click', (e) => {
            if (!pBtn.contains(e.target) && !pDrop.contains(e.target)) {
                pDrop.classList.add('hidden');
            }
        });
    }
</script>