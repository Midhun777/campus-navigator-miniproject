<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = isset($_POST['type']) && in_array($_POST['type'], ['lost','found']) ? $_POST['type'] : 'lost';
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
    $contact = trim($_POST['contact']);
    $user_id = $_SESSION['user_id'];
    $image = null;

    // Image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $dir = 'assets/uploads/lost_found/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        $orig = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $safe = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $final = $safe . '_' . time() . '.' . $ext;
            $dest = $dir . $final;
            $tmp = $_FILES['image']['tmp_name'];
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? @finfo_file($finfo, $tmp) : null;
            if ($finfo) { @finfo_close($finfo); }
            $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!$mime || in_array($mime, $allowedMimes)) {
                if (move_uploaded_file($tmp, $dest)) { $image = $dest; }
            }
        }
    }

    $stmt = $conn->prepare('INSERT INTO lost_found (user_id, type, title, description, location, event_date, contact, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssssss', $user_id, $type, $title, $description, $location, $event_date, $contact, $image);
    if ($stmt->execute()) {
        audit_log($conn, 'lf_create', 'lost_found', $stmt->insert_id, ['type' => $type]);
        header('Location: lost_found.php?msg=Created');
        exit();
    } else {
        $error = 'Failed to create report.';
    }
}

include 'includes/header.php';
?>
<div class="max-w-xl mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">New Lost/Found Report</h2>
    <?php if (isset($error)): ?><div class="mb-3 text-red-600"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <label class="block">
            <span class="text-sm">Type</span>
            <select name="type" class="w-full px-3 py-2 border rounded">
                <option value="lost">Lost</option>
                <option value="found">Found</option>
            </select>
        </label>
        <label class="block">
            <span class="text-sm">Title</span>
            <input type="text" name="title" required class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Description</span>
            <textarea name="description" required class="w-full px-3 py-2 border rounded"></textarea>
        </label>
        <label class="block">
            <span class="text-sm">Location</span>
            <input type="text" name="location" class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Date (when lost/found)</span>
            <input type="date" name="event_date" class="w-full px-3 py-2 border rounded">
        </label>
        <label class="block">
            <span class="text-sm">Contact</span>
            <input type="text" name="contact" class="w-full px-3 py-2 border rounded" placeholder="Email or phone">
        </label>
        <label class="block">
            <span class="text-sm">Image (optional)</span>
            <input type="file" name="image" accept="image/*" class="w-full">
        </label>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Submit</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>


