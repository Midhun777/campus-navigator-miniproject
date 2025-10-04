<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Navigator</title>
    <link href="src/output.css" rel="stylesheet">
    <!-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"> -->
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const userTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (userTheme === 'dark' || (!userTheme && systemPrefersDark)) {
            document.documentElement.classList.add('dark');
            document.getElementById('theme-icon').textContent = '☀️';
        } else {
            document.documentElement.classList.remove('dark');
            document.getElementById('theme-icon').textContent = '🌙';
        }
    });

    function toggleDarkMode() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        html.classList.toggle('dark');

        if (html.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
            icon.textContent = '☀️';
        } else {
            localStorage.setItem('theme', 'light');
            icon.textContent = '🌙';
        }
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
        <a href="lost_found.php" class="hover:underline">Lost &amp; Found</a>
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['faculty','admin'])): ?>
            <a href="manage_posts.php" class="hover:underline">Manage Posts</a>
        <?php endif; ?>
    </nav>

    <!-- Avatar and theme controls -->
    <div class="flex items-center space-x-2 relative">
        <button onclick="toggleDarkMode()" class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700">
            <span id="theme-icon">🌙</span>
        </button>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="relative">
            <button id="avatarBtn" aria-expanded="false" class="focus:outline-none flex items-center justify-center h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700">
                <svg class="h-6 w-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 14c2.21 0 4 1.79 4 4v2H4v-2c0-2.21 1.79-4 4-4h8z"/>
                    <circle cx="12" cy="8" r="4"/>
                </svg>
            </button>

            <!-- Dropdown positioned fixed to avoid header layout shift -->
            <div id="avatarDropdown" class="fixed right-4 top-16 min-w-48 max-w-xs bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-[9999] transition-all duration-200 hidden" style="display: none;">
                <a href="profile.php" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['faculty','admin'])): ?>
                    <a href="manage_posts.php" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Manage Posts</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="view_audit.php" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Audit Logs</a>
                    <a href="manage_users.php" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Manage Users</a>
                <?php endif; ?>
                <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
            </div>
        </div>
        <?php else: ?>
            <a href="login.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Login</a>
        <?php endif; ?>
    </div>
</header>
<main class="p-4">
