<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $profile_pic = NULL;

    // Handle profile pic upload (optional)
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $target_dir = 'assets/images/';
        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_pic = $target_file;
        }
    }

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
    <h2 class="text-2xl font-bold mb-4">Register</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="text" name="name" placeholder="Name" required class="w-full px-3 py-2 border rounded">
        <input type="email" name="email" placeholder="Email" required class="w-full px-3 py-2 border rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full px-3 py-2 border rounded">
        <select name="role" class="w-full px-3 py-2 border rounded" required>
            <option value="user">User</option>
            <option value="faculty">Faculty</option>
        </select>
        <input type="file" name="profile_pic" accept="image/*" class="w-full">
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Register</button>
    </form>
    <div class="mt-4 text-center">
        Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 