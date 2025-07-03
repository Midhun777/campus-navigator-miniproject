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
<div class="min-h-[90vh] w-full bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-2 py-8">
  <div class="w-full max-w-5xl p-6 md:p-10 bg-white dark:bg-gray-900 rounded-3xl shadow-xl transition-all duration-500">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-blue-900 dark:text-blue-200 mb-2 transition-colors duration-300">Hello, <?php echo get_user_name(); ?>!</h1>
        <div class="text-gray-500 dark:text-gray-300" id="liveTime"></div>
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-3 mt-4 md:mt-0">
        <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full shadow transition-colors duration-300">Weather (Kochi): <?php echo get_weather_kochi(); ?></span>
        <a href="add_spot.php" class="bg-gradient-to-r from-green-400 to-blue-500 text-white px-6 py-2 rounded-full shadow hover:from-green-500 hover:to-blue-600 transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400">+ Add Spot</a>
      </div>
    </div>
    <div class="mb-8">
      <div class="flex flex-wrap gap-3 justify-center md:justify-start">
        <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-4 py-2 rounded-full cursor-pointer shadow hover:scale-105 transition-transform duration-200">Hungry?</span>
        <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-4 py-2 rounded-full cursor-pointer shadow hover:scale-105 transition-transform duration-200">Need a place to study?</span>
        <span class="bg-pink-100 dark:bg-pink-900 text-pink-800 dark:text-pink-200 px-4 py-2 rounded-full cursor-pointer shadow hover:scale-105 transition-transform duration-200">Hangout spots</span>
        <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-4 py-2 rounded-full cursor-pointer shadow hover:scale-105 transition-transform duration-200">ATM/Bank</span>
        <span class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-4 py-2 rounded-full cursor-pointer shadow hover:scale-105 transition-transform duration-200">Shops</span>
      </div>
    </div>
    <h2 class="text-2xl font-semibold mb-6 text-blue-900 dark:text-blue-200 transition-colors duration-300">Featured Spots</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
      <?php foreach ($spots as $spot): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 flex flex-col transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
          <img src="<?php echo $spot['image'] ? $spot['image'] : 'assets/images/default_spot.jpg'; ?>" alt="Spot Image" class="h-32 w-full object-cover rounded-xl mb-2 transition-all duration-300">
          <div class="font-bold text-lg mb-1 text-blue-900 dark:text-blue-200 transition-colors duration-300"><?php echo htmlspecialchars($spot['name']); ?></div>
          <div class="text-sm text-gray-500 dark:text-gray-300 mb-1"><?php echo htmlspecialchars($spot['category_name']); ?></div>
          <div class="text-sm mb-2"><?php echo htmlspecialchars($spot['description']); ?></div>
          <div class="mt-auto flex justify-between items-center">
            <span class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">Timing: <?php echo htmlspecialchars($spot['timing']); ?></span>
            <a href="spot_details.php?id=<?php echo $spot['id']; ?>" class="text-blue-600 hover:underline text-xs transition-colors duration-200">View</a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($spots)): ?>
        <div class="col-span-4 text-center text-gray-500">No spots to show yet.</div>
      <?php endif; ?>
    </div>
    <h2 class="text-2xl font-semibold mb-6 text-blue-900 dark:text-blue-200 transition-colors duration-300">Categories</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
      <?php foreach ($categories as $cat): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 flex flex-col items-center transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
          <div class="text-4xl mb-2 animate-fade-in-slow"><?php echo $cat['icon'] ? $cat['icon'] : '📍'; ?></div>
          <div class="font-semibold text-gray-900 dark:text-gray-100 transition-colors duration-300"><?php echo htmlspecialchars($cat['name']); ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($categories)): ?>
        <div class="col-span-4 text-center text-gray-500">No categories yet.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<style>
@keyframes fade-in-slow {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fade-in-slow {
  animation: fade-in-slow 1s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
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