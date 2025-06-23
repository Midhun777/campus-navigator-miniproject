<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

// Fetch categories
$categories = [];
$cat_res = $conn->query('SELECT * FROM categories');
while ($row = $cat_res->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch featured spots (approved, limit 4)
$spots = [];
$spot_res = $conn->query("SELECT spots.*, categories.name AS category_name FROM spots LEFT JOIN categories ON spots.category_id = categories.id WHERE status='approved' ORDER BY created_at DESC LIMIT 4");
while ($row = $spot_res->fetch_assoc()) {
    $spots[] = $row;
}
?>
<div class="max-w-5xl mx-auto mt-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Hello, <?php echo get_user_name(); ?>!</h1>
            <div class="text-gray-600 dark:text-gray-300" id="liveTime"></div>
        </div>
        <div class="flex items-center space-x-4 mt-4 md:mt-0">
            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">Weather (Kochi): <?php echo get_weather_kochi(); ?></span>
            <a href="add_spot.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add Spot</a>
        </div>
    </div>
    <div class="mb-6">
        <div class="flex space-x-4 overflow-x-auto pb-2">
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded cursor-pointer">Hungry?</span>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded cursor-pointer">Need a place to study?</span>
            <span class="bg-pink-100 text-pink-800 px-3 py-1 rounded cursor-pointer">Hangout spots</span>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded cursor-pointer">ATM/Bank</span>
            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded cursor-pointer">Shops</span>
        </div>
    </div>
    <h2 class="text-xl font-semibold mb-4">Featured Spots</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php foreach ($spots as $spot): ?>
            <div class="bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
                <img src="<?php echo $spot['image'] ? $spot['image'] : 'assets/images/default_spot.jpg'; ?>" alt="Spot Image" class="h-32 w-full object-cover rounded mb-2">
                <div class="font-bold text-lg mb-1"><?php echo htmlspecialchars($spot['name']); ?></div>
                <div class="text-sm text-gray-500 mb-1"><?php echo htmlspecialchars($spot['category_name']); ?></div>
                <div class="text-sm mb-2"><?php echo htmlspecialchars($spot['description']); ?></div>
                <div class="mt-auto flex justify-between items-center">
                    <span class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">Timing: <?php echo htmlspecialchars($spot['timing']); ?></span>
                    <a href="spot_details.php?id=<?php echo $spot['id']; ?>" class="text-blue-600 hover:underline text-xs">View</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($spots)): ?>
            <div class="col-span-4 text-center text-gray-500">No spots to show yet.</div>
        <?php endif; ?>
    </div>
    <h2 class="text-xl font-semibold mb-4">Categories</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php foreach ($categories as $cat): ?>
            <div class="bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col items-center">
                <div class="text-3xl mb-2"><?php echo $cat['icon'] ? $cat['icon'] : '📍'; ?></div>
                <div class="font-semibold"><?php echo htmlspecialchars($cat['name']); ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
            <div class="col-span-4 text-center text-gray-500">No categories yet.</div>
        <?php endif; ?>
    </div>
</div>
<script>
// Live time
function updateLiveTime() {
    const now = new Date();
    document.getElementById('liveTime').textContent = 'Live Time: ' + now.toLocaleTimeString();
}
setInterval(updateLiveTime, 1000);
updateLiveTime();
</script>
<?php include 'includes/footer.php'; ?> 