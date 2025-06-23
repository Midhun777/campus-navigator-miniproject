<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
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

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = 'assets/images/';
        $target_file = $target_dir . time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
        }
    }

    $stmt = $conn->prepare('INSERT INTO spots (user_id, name, description, image, category_id, timing, direction, distance, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssissss', $user_id, $name, $description, $image, $category_id, $timing, $direction, $distance, $status);
    if ($stmt->execute()) {
        header('Location: dashboard.php?msg=Spot+added+successfully');
        exit();
    } else {
        $error = 'Failed to add spot.';
    }
}
?>
<div class="max-w-lg mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Add a New Spot</h2>
    <?php if (isset($error)): ?>
        <div class="mb-4 text-red-600"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="text" name="name" placeholder="Spot Name" required class="w-full px-3 py-2 border rounded">
        <textarea name="description" placeholder="Description" required class="w-full px-3 py-2 border rounded"></textarea>
        <select name="category_id" required class="w-full px-3 py-2 border rounded">
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="timing" placeholder="Timing (e.g. 9am - 5pm)" required class="w-full px-3 py-2 border rounded">
        <input type="text" name="direction" placeholder="Direction (e.g. Near Library)" required class="w-full px-3 py-2 border rounded">
        <input type="text" name="distance" placeholder="Distance (e.g. 200m from gate)" required class="w-full px-3 py-2 border rounded">
        <input type="file" name="image" accept="image/*" class="w-full">
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Add Spot</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?> 