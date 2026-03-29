<header class="bg-white border-b border-slate-200 sticky top-0 z-40 px-4 py-3">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        
        <div class="flex items-center">
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest leading-none">System Status</span>
                <p class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Operational
                </p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <div class="hidden lg:flex flex-col items-end border-r border-slate-100 pr-6">
                <p id="headerClock" class="text-sm font-black text-slate-800 leading-none">00:00:00 AM</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1">
                    <?php echo date('l, F j, Y'); ?>
                </p>
            </div>

            <div class="relative">
                <button id="profileBtn" class="flex items-center gap-3 p-1 pr-3 hover:bg-slate-50 rounded-2xl transition-all group border border-transparent hover:border-slate-100">
                    <div class="relative w-10 h-10 shadow-md shadow-blue-100">
                        <img src="../public/uploads/<?php echo $_SESSION['profile_image'] ?: 'default_profile.png'; ?>" 
                             class="w-full h-full rounded-xl object-cover ring-2 ring-white" 
                             alt="Avatar">
                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-blue-600 border-2 border-white rounded-full flex items-center justify-center">
                            <div class="w-1 h-1 bg-white rounded-full"></div>
                        </div>
                    </div>

                    <div class="text-left hidden md:block">
                        <p class="text-xs font-black text-slate-800 leading-none"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mt-1"><?php echo $_SESSION['role']; ?></p>
                    </div>

                    <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-600 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 9l-7 7-7-7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-[2rem] shadow-2xl py-3 z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                    <div class="px-5 py-3 border-b border-slate-50 mb-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Account Management</p>
                    </div>
                    
                    <a href="profile.php" class="flex items-center gap-3 px-5 py-3 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-all">
                        <div class="bg-blue-50 p-1.5 rounded-lg">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2.5"/></svg>
                        </div>
                        Personal Profile
                    </a>

                    <a href="settings.php" class="flex items-center gap-3 px-5 py-3 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-all">
                        <div class="bg-slate-50 p-1.5 rounded-lg text-slate-400 group-hover:text-blue-600">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066" stroke-width="2.5"/></svg>
                        </div>
                        Security Settings
                    </a>

                    <div class="mx-4 my-2 border-t border-slate-100"></div>
                    
                    <a href="../auth/logout.php" class="flex items-center gap-3 px-5 py-3 text-xs font-extrabold text-red-500 hover:bg-red-50 transition-all rounded-b-2xl">
                        <div class="bg-red-100 p-1.5 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        Secure Sign Out
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

    // 2. Profile Dropdown Toggle Logic
    const pBtn = document.getElementById('profileBtn');
    const pDrop = document.getElementById('profileDropdown');

    if (pBtn && pDrop) {
        pBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            pDrop.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!pBtn.contains(e.target) && !pDrop.contains(e.target)) {
                pDrop.classList.add('hidden');
            }
        });
    }
</script>