<footer class="mt-auto py-8 px-8 border-t border-slate-100 bg-white/50">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">
                &copy; <?php echo date('Y'); ?> Task Management System
            </span>
            <span class="text-emerald-500">•</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                v2.4.0
            </span>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 text-[10px] font-black text-slate-300 uppercase tracking-widest">
                System Status: 
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
            </div>
        </div>
    </div>
</footer>