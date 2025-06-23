<?php
include 'includes/header.php';
include 'includes/functions.php';
?>
<div class="max-w-2xl mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h1 class="text-3xl font-bold mb-4">Welcome to Campus Navigator</h1>
    <p class="mb-4">Campus Navigator helps students and visitors find spots in and around the campus easily. Discover food spots, study areas, hangouts, and more!</p>
    <div class="mb-4">
        <span class="font-semibold">Live Weather (Kochi):</span>
        <span class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded">
            <?php echo get_weather_kochi(); ?>
        </span>
    </div>
    <div class="flex space-x-4 mt-6">
        <a href="login.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Login</a>
        <a href="register.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Register</a>
    </div>
    <div class="mt-8 text-gray-600 dark:text-gray-300">
        <h2 class="text-xl font-semibold mb-2">About Our Campus</h2>
        <p>Our campus is a vibrant hub of learning, innovation, and community. Use Campus Navigator to explore all the amazing places and resources available to you!</p>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 