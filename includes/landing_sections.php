<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<section class="min-h-[90vh] w-full bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-2 py-8">
  <div class="w-full max-w-5xl p-6 md:p-10 bg-white dark:bg-gray-900 rounded-3xl shadow-xl">
    <div class="flex flex-col md:items-center gap-6 mb-8">
      <div class="max-w-3xl">
        <h1 class="text-4xl font-extrabold text-blue-900 dark:text-blue-200 mb-2">Welcome to Spotyfind</h1>
        <p class="text-lg text-gray-500 dark:text-gray-300">Discover, share, and manage the best spots around your campus. Browse categories, view details, and contribute your own finds to help classmates navigate smarter.</p>
        <div class="mt-6 space-x-4">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 transition-colors">Explore Spots</a>
            <a href="add_spot.php" class="inline-block px-6 py-2 bg-green-600 text-white rounded-full shadow hover:bg-green-700 transition-colors">Add a Spot</a>
          <?php else: ?>
            <a href="dashboard.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full shadow hover:bg-blue-700 transition-colors">Explore Spots</a>
            <a href="register.php" class="inline-block px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-full shadow">Sign up</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div id="features" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 transition-all hover:shadow-2xl hover:-translate-y-1">
        <div class="text-3xl mb-2">📍</div>
        <div class="font-bold text-xl mb-1">Discover Study Spots</div>
        <p class="text-sm text-gray-500 dark:text-gray-300">Find quiet corners, labs, canteens, and more—curated by your community.</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 transition-all hover:shadow-2xl hover:-translate-y-1">
        <div class="text-3xl mb-2">⭐</div>
        <div class="font-bold text-xl mb-1">Rate and Review</div>
        <p class="text-sm text-gray-500 dark:text-gray-300">Share experiences and tips so others know what to expect before they go.</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 transition-all hover:shadow-2xl hover:-translate-y-1">
        <div class="text-3xl mb-2">🏫</div>
        <div class="font-bold text-xl mb-1">Faculties & Categories</div>
        <p class="text-sm text-gray-500 dark:text-gray-300">Browse by category or faculty for faster discovery and better organization.</p>
      </div>
    </div>

    <div class="mt-8 text-center">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <p class="text-gray-500 dark:text-gray-300 mb-4">Already have an account?</p>
        <a href="login.php" class="px-6 py-2 bg-blue-600 text-white rounded-full shadow hover:bg-blue-700">Login</a>
      <?php else: ?>
        <a href="dashboard.php" class="px-6 py-2 bg-blue-600 text-white rounded-full shadow hover:bg-blue-700">Explore Spots</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section id="why" class="w-full max-w-5xl mx-auto px-2 py-8">
  <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl p-6 md:p-10">
    <h2 class="text-3xl font-extrabold text-blue-900 dark:text-blue-200 mb-4">Why Choose Spotyfind?</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-blue-100 dark:bg-blue-900 rounded-2xl p-6">
        <h3 class="text-xl font-bold text-blue-900 dark:text-blue-200 mb-2">Campus-first discovery</h3>
        <p class="text-sm text-gray-700 dark:text-gray-300">Built for students and staff to quickly find study spaces, facilities, and hidden gems across your campus.</p>
      </div>
      <div class="bg-green-100 dark:bg-green-900 rounded-2xl p-6">
        <h3 class="text-xl font-bold text-green-800 dark:text-green-200 mb-2">Community powered</h3>
        <p class="text-sm text-gray-700 dark:text-gray-300">Add new spots, suggest edits, and keep information fresh with lightweight moderation.</p>
      </div>
    </div>
  </div>
  </section>

<section id="services" class="w-full max-w-5xl mx-auto px-2 pb-8">
  <h2 class="text-2xl font-semibold mb-4 text-blue-900 dark:text-blue-200">Services</h2>
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
      <div class="text-2xl mb-2">🔎</div>
      <div class="font-semibold mb-1">Browse by Category</div>
      <p class="text-sm text-gray-500 dark:text-gray-300">Libraries, labs, canteens, parking and more.</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
      <div class="text-2xl mb-2">📝</div>
      <div class="font-semibold mb-1">Add & Edit Spots</div>
      <p class="text-sm text-gray-500 dark:text-gray-300">Contribute your finds and improve details collaboratively.</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
      <div class="text-2xl mb-2">🛡️</div>
      <div class="font-semibold mb-1">Moderation</div>
      <p class="text-sm text-gray-500 dark:text-gray-300">Faculty/admins approve pending submissions and review reports.</p>
    </div>
  </div>
</section>

<section id="contact" class="w-full max-w-5xl mx-auto px-2 pb-12">
  <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl p-6 md:p-10 text-center">
    <h2 class="text-2xl font-bold mb-2">Have a question or suggestion?</h2>
    <p class="text-gray-500 dark:text-gray-300 mb-4">Login to add a spot or reach out to the admins via your profile.</p>
    <a href="login.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700">Get Started</a>
  </div>
</section>


