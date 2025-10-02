<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $profile_pic = NULL; // Always NULL, no upload

    $stmt = $conn->prepare('INSERT INTO users (name, email, password, role, profile_pic) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $name, $email, $password, $role, $profile_pic);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = $role;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Registration failed. Email may already be used.';
    }
}
?>
<div class="max-w-md mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">Register</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600 dark:text-red-400"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
        <input type="text" name="name" placeholder="Name" required class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
        <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
        <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
        <select name="role" class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700" required>
            <option value="user" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">User</option>
            <option value="faculty" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">Faculty</option>
        </select>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Register</button>
    </form>
    <div class="mt-4 text-center text-gray-700 dark:text-gray-300">
        Already have an account? <a href="login.php" class="text-blue-600 dark:text-blue-400 hover:underline">Login</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 