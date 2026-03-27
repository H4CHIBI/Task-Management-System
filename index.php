<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailwind + PHP Test</title>
    <link href="public/css/output.css" rel="stylesheet">
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm">
        <h1 class="text-3xl font-bold text-indigo-600 mb-4">
            It's Working! 🚀
        </h1>
        <p class="text-gray-600 mb-6">
            If you see a dark blue background and this white card, 
            Tailwind is successfully scanning your PHP files.
        </p>
        <button class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg transition">
            PHP Version: <?php echo phpversion(); ?>
        </button>
    </div>

</body>
</html>