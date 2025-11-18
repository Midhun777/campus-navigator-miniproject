<?php
include 'includes/db.php';
include 'includes/auth.php';

// Load colleges for dropdown
$colleges = [];
$c_res = $conn->query('SELECT id, name, code FROM colleges ORDER BY name');
if ($c_res) {
    while ($r = $c_res->fetch_assoc()) { $colleges[] = $r; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $college_id = isset($_POST['college_id']) && $_POST['college_id'] !== '' ? intval($_POST['college_id']) : NULL;
    $profile_pic = NULL;

    // Handle profile picture upload or assign random default
    if (isset($_FILES['profile_pic'])) {
        $fileError = $_FILES['profile_pic']['error'];
        if ($fileError === UPLOAD_ERR_OK) {
            $target_dir = 'assets/uploads/profile/';
            if (!is_dir($target_dir)) { @mkdir($target_dir, 0755, true); }
            $originalName = basename($_FILES['profile_pic']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($extension, $allowed)) {
                $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $finalName = $safeName . '_' . time() . '.' . $extension;
                $target_file = $target_dir . $finalName;
                $tmp = $_FILES['profile_pic']['tmp_name'];
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                $mime = $finfo ? @finfo_file($finfo, $tmp) : null;
                if ($finfo) { @finfo_close($finfo); }
                $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
                if (!$mime || in_array($mime, $allowedMimes)) {
                    if (move_uploaded_file($tmp, $target_file)) {
                        $profile_pic = $target_file;
                    }
                }
            }
        }
    }

    // If no uploaded profile pic, assign random from avatars folder
    if ($profile_pic === NULL) {
        $avatarBase = 'assets/icons/avatars/';
        $fallbackBase = 'src/icons/avatars/';
        $dirToUse = is_dir($avatarBase) ? $avatarBase : (is_dir($fallbackBase) ? $fallbackBase : null);
        if ($dirToUse) {
            $files = @glob($dirToUse . '*.{png,jpg,jpeg,webp,gif}', GLOB_BRACE);
            if ($files && count($files) > 0) {
                $rand = $files[array_rand($files)];
                $profile_pic = $rand;
            }
        }
    }

    // If user selected faculty during registration, create a pending faculty request
    // but keep their role as 'user' until an admin approves.
    $faculty_status = 'none';
    $role_for_insert = $role;
    if ($role === 'faculty') {
        $faculty_status = 'pending';
        $role_for_insert = 'user';
    }

    $stmt = $conn->prepare('INSERT INTO users (name, email, password, role, faculty_status, profile_pic, college_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssi', $name, $email, $password, $role_for_insert, $faculty_status, $profile_pic, $college_id);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = $role_for_insert;
        $_SESSION['college_id'] = $college_id;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Registration failed. Email may already be used.';
    }
}
include 'includes/header.php';
?>
<div class="max-w-md mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">Register</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600 dark:text-red-400"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <label class="block">
            <span class="text-sm">Name</span>
            <input type="text" name="name" required class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
        </label>
        <label class="block">
            <span class="text-sm">Email</span>
            <input type="email" name="email" required class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
        </label>
        <label class="block">
            <span class="text-sm">Password</span>
            <input type="password" name="password" required class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
        </label>
        <label class="block">
            <span class="text-sm">Role</span>
            <select name="role" class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700" required>
                <option value="user" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">User</option>
                <option value="faculty" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">Faculty</option>
            </select>
        </label>
        <label class="block">
            <span class="text-sm">College (optional)</span>
            <select name="college_id" class="w-full px-3 py-2 border rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                <option value="" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">No college (see all posts)</option>
                <?php foreach ($colleges as $col): ?>
                    <option value="<?php echo (int)$col['id']; ?>" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">
                        <?php echo htmlspecialchars($col['name']); ?><?php echo isset($col['code']) && $col['code'] ? ' ('.htmlspecialchars($col['code']).')' : ''; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="block">
            <span class="text-sm">Profile Picture (optional)</span>
            <input type="file" name="profile_pic" accept="image/*" class="w-full">
        </label>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Register</button>
    </form>
    <div class="mt-4 text-center text-gray-700 dark:text-gray-300">
        Already have an account? <a href="login.php" class="text-blue-600 dark:text-blue-400 hover:underline">Login</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 