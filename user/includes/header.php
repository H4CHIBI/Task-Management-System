<header class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-30">
    <div class="px-8 py-4 flex items-center justify-between">
        
        <div class="flex items-center gap-4">
            <div class="w-1 h-8 bg-emerald-500 rounded-full"></div>
            <div>
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest leading-none">Member Workspace</h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-1">
                    <?php echo date('l, F d, Y'); ?>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-8">
            
            <div class="hidden md:flex items-center bg-slate-50 border border-slate-100 px-4 py-2 rounded-2xl gap-3 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search tasks..." class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 placeholder:text-slate-300 w-48">
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-3 group">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-black text-slate-800 group-hover:text-emerald-600 transition-colors leading-none">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">
                            Team <?php echo $_SESSION['role']; ?>
                        </p>
                    </div>
                    
                    <div class="relative inline-block">
                        <img src="../public/uploads/<?php echo $_SESSION['profile_image']; ?>" 
                             class="w-10 h-10 rounded-2xl object-cover ring-2 ring-white shadow-sm group-hover:shadow-md transition-all">
                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></div>
                    </div>
                    
                    <svg class="w-4 h-4 text-slate-300 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-cloak
                     class="absolute right-0 mt-4 w-56 bg-white rounded-3xl shadow-2xl shadow-slate-200 border border-slate-100 py-3 z-50">
                    
                    <div class="px-5 py-2 mb-2">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Account</p>
                    </div>

                    <a href="profile.php" class="flex items-center gap-3 px-5 py-3 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-emerald-600 transition-colors">
                        <div class="p-2 bg-slate-50 rounded-lg group-hover:bg-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        My Profile
                    </a>

                    <div class="my-2 border-t border-slate-50"></div>
                    
                    <a href="../auth/logout.php" class="flex items-center gap-3 px-5 py-3 text-xs font-bold text-rose-500 hover:bg-rose-50 transition-colors">
                        <div class="p-2 bg-rose-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </div>
                        Sign Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>