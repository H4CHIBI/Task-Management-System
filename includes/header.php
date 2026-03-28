<header class="bg-white border-b p-4 flex justify-between items-center sticky top-0 z-10 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-700 uppercase tracking-wide">Overview</h2>
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500 text-right">
            Welcome, <br>
            <strong class="text-gray-800"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </span>
        <div class="h-10 w-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold shadow-md">
            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
        </div>
    </div>
</header>