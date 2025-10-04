<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

$spot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$spot_id) {
    echo '<div class="text-center mt-8 text-red-600">Invalid spot ID.</div>';
    include 'includes/footer.php';
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch spot
$stmt = $conn->prepare('SELECT * FROM spots WHERE id = ?');
$stmt->bind_param('i', $spot_id);
$stmt->execute();
$spot = $stmt->get_result()->fetch_assoc();
if (!$spot || ($spot['user_id'] != $user_id && !$is_admin)) {
    echo '<div class="text-center mt-8 text-red-600">You do not have permission to edit this spot.</div>';
    include 'includes/footer.php';
    exit();
}

// Fetch categories
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
    $image = $spot['image'];

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

    $stmt = $conn->prepare('UPDATE spots SET name=?, description=?, image=?, category_id=?, timing=?, direction=?, distance=? WHERE id=?');
    $stmt->bind_param('sssisssi', $name, $description, $image, $category_id, $timing, $direction, $distance, $spot_id);
    if ($stmt->execute()) {
        audit_log($conn, 'spot_update', 'spot', $spot_id, [
            'name' => $name,
            'category_id' => $category_id
        ]);
        header('Location: spot_details.php?id=' . $spot_id . '&msg=Spot+updated');
        exit();
    } else {
        $error = 'Failed to update spot.';
    }
}
?>
<div class="max-w-lg mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Edit Spot</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="text" name="name" value="<?php echo htmlspecialchars($spot['name']); ?>" required class="w-full px-3 py-2 border rounded">
        <textarea name="description" required class="w-full px-3 py-2 border rounded"><?php echo htmlspecialchars($spot['description']); ?></textarea>
        <select name="category_id" required class="w-full px-3 py-2 border rounded">
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $spot['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="timing" value="<?php echo htmlspecialchars($spot['timing']); ?>" required class="w-full px-3 py-2 border rounded">
        <input type="text" name="direction" value="<?php echo htmlspecialchars($spot['direction']); ?>" required class="w-full px-3 py-2 border rounded">
        <input type="text" name="distance" value="<?php echo htmlspecialchars($spot['distance']); ?>" required class="w-full px-3 py-2 border rounded">
        <div>
            <label class="block mb-1">Current Image:</label>
            <img src="<?php echo $spot['image'] ? $spot['image'] : 'assets/images/default_spot.jpg'; ?>" alt="Spot Image" class="h-24 w-24 object-cover rounded mb-2">
            <input type="file" name="image" accept="image/*" class="w-full">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Update Spot</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?> 