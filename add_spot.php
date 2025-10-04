<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

// Fetch categories for dropdown
$categories = [];
$cat_res = $conn->query('SELECT * FROM categories');
while ($row = $cat_res->fetch_assoc()) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $timing = $_POST['timing'];
    $direction = $_POST['direction'];
    $distance = $_POST['distance'];
    $user_id = $_SESSION['user_id'];
    $status = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'approved' : 'pending';
    $image = NULL;

    // Handle image upload (create dir, validate, and move)
    if (isset($_FILES['image'])) {
        $fileError = $_FILES['image']['error'];
        if ($fileError === UPLOAD_ERR_OK) {
            $target_dir = 'assets/images/';
            if (!is_dir($target_dir)) {
                @mkdir($target_dir, 0755, true);
            }
            $originalName = basename($_FILES['image']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($extension, $allowed)) {
                $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $finalName = $safeName . '_' . time() . '.' . $extension;
                $target_file = $target_dir . $finalName;
                $tmp = $_FILES['image']['tmp_name'];
                // Basic MIME validation
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                $mime = $finfo ? @finfo_file($finfo, $tmp) : null;
                if ($finfo) { @finfo_close($finfo); }
                $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
                if (!$mime || in_array($mime, $allowedMimes)) {
                    if (move_uploaded_file($tmp, $target_file)) {
                        $image = $target_file;
                    } else {
                        $error = 'Image upload failed while moving the file.';
                    }
                } else {
                    $error = 'Unsupported image type.';
                }
            } else {
                $error = 'Invalid image extension. Allowed: jpg, jpeg, png, gif, webp.';
            }
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            $error = 'Image upload error (code ' . $fileError . ').';
        }
    }

    $stmt = $conn->prepare('INSERT INTO spots (user_id, name, description, image, category_id, timing, direction, distance, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssissss', $user_id, $name, $description, $image, $category_id, $timing, $direction, $distance, $status);
    if ($stmt->execute()) {
        audit_log($conn, 'spot_create', 'spot', $stmt->insert_id, [
            'name' => $name,
            'status' => $status,
            'category_id' => $category_id
        ]);
        header('Location: dashboard.php?msg=Spot+added+successfully');
        exit();
    } else {
        $error = 'Failed to add spot.';
    }
}
include 'includes/header.php';
?>
<div class="max-w-lg mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Add a New Spot</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <label class="block">
            <span class="text-sm">Spot Name</span>
            <input type="text" name="name" required class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Description</span>
            <textarea name="description" required class="w-full px-3 py-2 border rounded"></textarea>
        </label>
        <label class="block">
            <span class="text-sm">Category</span>
            <select name="category_id" required class="w-full px-3 py-2 border rounded text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">
                <option value="" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="block">
            <span class="text-sm">Timing (e.g. 9am - 5pm)</span>
            <input type="text" name="timing" required class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Direction (e.g. Near Library)</span>
            <input type="text" name="direction" required class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Distance (e.g. 200m from gate)</span>
            <input type="text" name="distance" required class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Image</span>
            <input type="file" name="image" accept="image/*" class="w-full">
        </label>
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Add Spot</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?> 