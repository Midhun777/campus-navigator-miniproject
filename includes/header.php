<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Navigator</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
    // Dark mode toggle
    function toggleDarkMode() {
        document.documentElement.classList.toggle('dark');
    }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
<header class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 shadow">
    <div class="flex items-center space-x-2">
        <img src="/assets/images/logo.png" alt="Logo" class="h-10 w-10">
        <span class="font-bold text-xl">Campus Navigator</span>
    </div>
    <nav class="space-x-4">
        <a href="index.php" class="hover:underline">Home</a>
        <a href="dashboard.php" class="hover:underline">Dashboard</a>
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['faculty','admin'])): ?>
            <a href="manage_posts.php" class="hover:underline">Manage Posts</a>
        <?php endif; ?>
        <a href="profile.php" class="hover:underline">Profile</a>
        <a href="logout.php" class="hover:underline">Logout</a>
    </nav>
    <div class="flex items-center space-x-2">
        <button onclick="toggleDarkMode()" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700">🌙</button>
        <span id="greeting">Hello, User!</span>
    </div>
</header>
<main class="p-4"> 