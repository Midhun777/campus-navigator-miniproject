<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare('SELECT id, name, role, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $role, $db_password);
        $stmt->fetch();
        if ($password === $db_password) { // No hashing as requested
            $_SESSION['user_id'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'No user found with that em55.ail.';
    }
}
?>
<div class="max-w-md mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Login</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
        <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
    </form>
    <div class="mt-4 text-center">
        Don't have an account? <a href="register.php" class="text-green-600 hover:underline">Register</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 