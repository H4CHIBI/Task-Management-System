<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TMS</title>
    <link rel="stylesheet" href="public/css/output.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    
    <div class="w-full max-w-md">
        <div class="flex flex-col items-center mb-10">
            <div class="bg-blue-600 p-3 rounded-2xl shadow-xl shadow-blue-200 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">
                TMS <span class="text-blue-600">Portal</span>
            </h1>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-[0.2em] mt-1">Management System</p>
        </div>

        <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl shadow-slate-200/60 border border-slate-100" 
             x-data="{ showPw: false, password: '' }">
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out'): ?>
                <div class="mb-6 p-4 bg-blue-50 text-blue-700 rounded-2xl font-bold text-sm border border-blue-100 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Logout successful. See you soon!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-2xl font-bold text-sm border border-red-100 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>
                        <?php 
                            if ($_GET['error'] == 'invalid_credentials') echo "Access Denied: Invalid credentials.";
                            elseif ($_GET['error'] == 'empty_fields') echo "Please fill in all fields.";
                            elseif ($_GET['error'] == 'unauthorized') echo "Session expired. Please login.";
                            else echo "An unexpected error occurred.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <form action="auth/auth.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Username</label>
                    <input name="username" type="text" required
                        class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all font-bold text-slate-700 placeholder:text-slate-300 placeholder:font-normal"
                        placeholder="Your unique handle">
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Password</label>
                    <div class="relative">
                        <input :type="showPw ? 'text' : 'password'" 
                               name="password" 
                               required
                               x-model="password"
                               @click="password = ''"
                               class="w-full px-6 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all font-bold text-slate-700 placeholder:text-slate-300"
                               placeholder="••••••••••••">
                        
                        <button type="button" 
                                @click="showPw = !showPw"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-blue-600 transition-colors p-2">
                            <svg x-show="!showPw" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPw" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 hover:bg-blue-600 text-white font-black py-4 rounded-2xl transition-all duration-300 shadow-xl shadow-slate-200 transform active:scale-[0.98]">
                    Authorize Access
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-slate-50">
                <p class="text-[10px] text-center text-slate-400 font-bold uppercase tracking-wider leading-relaxed">
                    Closed System <br>
                    <span class="text-slate-300 font-normal normal-case">Contact Admin for new credentials</span>
                </p>
            </div>
        </div>
        
        <p class="text-center mt-8 text-[10px] text-slate-300 font-bold uppercase tracking-widest">
            &copy; 2026 Task Management System v1.0
        </p>
    </div>
</body>
</html>