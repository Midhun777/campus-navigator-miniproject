<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Load colleges for dropdown
$colleges = [];
$c_res = $conn->query('SELECT id, name, code FROM colleges ORDER BY name');
if ($c_res) { while ($r = $c_res->fetch_assoc()) { $colleges[] = $r; } }

// Fetch user info
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
// Handle profile picture change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_profile_pic'])) {
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
                        $upd = $conn->prepare('UPDATE users SET profile_pic=? WHERE id=?');
                        $upd->bind_param('si', $target_file, $user_id);
                        $upd->execute();
                        header('Location: profile.php?msg=Profile+picture+updated');
                        exit();
                    } else {
                        $upload_error = 'Failed to move uploaded file.';
                    }
                } else {
                    $upload_error = 'Unsupported image type.';
                }
            } else {
                $upload_error = 'Invalid image extension.';
            }
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            $upload_error = 'Image upload error (code ' . $fileError . ').';
        }
    }
}

// Handle college change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_college'])) {
    $newCollegeId = isset($_POST['college_id']) && $_POST['college_id'] !== '' ? intval($_POST['college_id']) : NULL;
    $upd = $conn->prepare('UPDATE users SET college_id = ? WHERE id = ?');
    if ($newCollegeId === NULL) {
        $null = NULL;
        $upd->bind_param('ii', $null, $user_id);
    } else {
        $upd->bind_param('ii', $newCollegeId, $user_id);
    }
    $upd->execute();
    $_SESSION['college_id'] = $newCollegeId;
    header('Location: profile.php?msg=College+updated');
    exit();
}

// Handle delete post
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Only allow delete if user owns the post or is admin
    $check = $conn->prepare('SELECT * FROM spots WHERE id = ? AND (user_id = ? OR ? = "admin")');
    $check->bind_param('iis', $del_id, $user_id, $_SESSION['role']);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $conn->query('DELETE FROM spots WHERE id = ' . $del_id);
        audit_log($conn, 'spot_delete', 'spot', $del_id, ['by' => 'owner_or_admin']);
        header('Location: profile.php?msg=Spot+deleted');
        exit();
    }
}

// Fetch user's posts
$posts = [];
$res = $conn->query("SELECT spots.*, categories.name AS category_name FROM spots LEFT JOIN categories ON spots.category_id = categories.id WHERE spots.user_id = $user_id ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    $posts[] = $row;
}

// Fetch user's favorites
$favorites = [];
$fav_res = $conn->query("SELECT spots.*, categories.name AS category_name FROM favorites LEFT JOIN spots ON favorites.spot_id = spots.id LEFT JOIN categories ON spots.category_id = categories.id WHERE favorites.user_id = $user_id");
while ($row = $fav_res->fetch_assoc()) {
    $favorites[] = $row;
}

// Fetch recent activity (audit logs)
$activities = [];
$act_stmt = $conn->prepare('SELECT action, entity_type, entity_id, details, created_at FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
$act_stmt->bind_param('i', $user_id);
$act_stmt->execute();
$act_res = $act_stmt->get_result();
while ($row = $act_res->fetch_assoc()) {
    $activities[] = $row;
}
include 'includes/header.php';
?>
<div class="max-w-4xl mx-auto mt-8">
    <div class="flex items-center space-x-4 mb-8">
        <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : (is_dir('assets/icons/avatars') ? (function(){ $files = glob('assets/icons/avatars/*.{png,jpg,jpeg,webp,gif}', GLOB_BRACE); return $files ? $files[array_rand($files)] : 'assets/images/default_user.png'; })() : 'assets/images/default_user.png'); ?>" alt="Profile" class="h-20 w-20 rounded-full object-cover">
        <div>
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h2>
            <div class="text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
            <div class="text-sm text-gray-400 capitalize">Role: <?php echo htmlspecialchars($user['role']); ?></div>
        </div>
    </div>
    <div class="mb-8">
        <form method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
            <input type="file" name="profile_pic" accept="image/*" class="px-3 py-2 border rounded">
            <button type="submit" name="change_profile_pic" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Change Picture</button>
        </form>
        <?php if (isset($upload_error)): ?><div class="mt-2 text-red-600"><?php echo $upload_error; ?></div><?php endif; ?>
        <?php if (isset($_GET['msg'])): ?><div class="mt-2 text-green-600"><?php echo htmlspecialchars($_GET['msg']); ?></div><?php endif; ?>
    </div>
    <div class="mb-8">
        <form method="POST" class="flex items-center gap-3">
            <label class="block">
                <span class="text-sm">College</span>
                <select name="college_id" class="px-3 py-2 border rounded">
                    <option value="">No college (see all posts)</option>
                    <?php foreach ($colleges as $col): ?>
                        <option value="<?php echo (int)$col['id']; ?>" <?php echo ($user['college_id'] && (int)$user['college_id'] === (int)$col['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($col['name']); ?><?php echo isset($col['code']) && $col['code'] ? ' ('.htmlspecialchars($col['code']).')' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="change_college" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Change College</button>
        </form>
    </div>
    <h3 class="text-xl font-semibold mb-2">Your Posts</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($posts as $post): ?>
            <div class="bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
                <img src="<?php echo $post['image'] ? $post['image'] : 'assets/images/default_spot.jpg'; ?>" alt="Spot Image" class="h-32 w-full object-cover rounded mb-2">
                <div class="font-bold text-lg mb-1"><?php echo htmlspecialchars($post['name']); ?></div>
                <div class="text-sm text-gray-500 mb-1"><?php echo htmlspecialchars($post['category_name']); ?></div>
                <div class="text-sm mb-2"><?php echo htmlspecialchars($post['description']); ?></div>
                <div class="mt-auto flex justify-between items-center">
                    <a href="edit_spot.php?id=<?php echo $post['id']; ?>" class="text-blue-600 hover:underline text-xs">Edit</a>
                    <a href="profile.php?delete=<?php echo $post['id']; ?>" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete this spot?');">Delete</a>
                    <a href="spot_details.php?id=<?php echo $post['id']; ?>" class="text-green-600 hover:underline text-xs">View</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <div class="col-span-2 text-center text-gray-500">No posts yet.</div>
        <?php endif; ?>
    </div>
    <h3 class="text-xl font-semibold mb-2">Your Favorites</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($favorites as $fav): ?>
            <div class="bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
                <img src="<?php echo $fav['image'] ? $fav['image'] : 'assets/images/default_spot.jpg'; ?>" alt="Spot Image" class="h-32 w-full object-cover rounded mb-2">
                <div class="font-bold text-lg mb-1"><?php echo htmlspecialchars($fav['name']); ?></div>
                <div class="text-sm text-gray-500 mb-1"><?php echo htmlspecialchars($fav['category_name']); ?></div>
                <div class="text-sm mb-2"><?php echo htmlspecialchars($fav['description']); ?></div>
                <div class="mt-auto flex justify-between items-center">
                    <a href="spot_details.php?id=<?php echo $fav['id']; ?>" class="text-green-600 hover:underline text-xs">View</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($favorites)): ?>
            <div class="col-span-2 text-center text-gray-500">No favorites yet.</div>
        <?php endif; ?>
    </div>
    <h3 class="text-xl font-semibold mt-10 mb-2">Recent Activity</h3>
    <div class="bg-white dark:bg-gray-800 rounded shadow divide-y">
        <?php foreach ($activities as $a): ?>
            <div class="p-3 flex items-start justify-between text-sm">
                <div>
                    <div class="font-medium capitalize"><?php echo htmlspecialchars($a['action']); ?></div>
                    <div class="text-gray-500 dark:text-gray-400">
                        <?php echo htmlspecialchars($a['entity_type'] ?: 'app'); ?>
                        <?php if (!empty($a['entity_id'])): ?>#<?php echo (int)$a['entity_id']; ?><?php endif; ?>
                    </div>
                    <?php if (!empty($a['details'])): ?>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate max-w-[60ch]">
                            <?php echo htmlspecialchars(is_string($a['details']) ? $a['details'] : json_encode($a['details'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-xs text-gray-400 ml-4 whitespace-nowrap"><?php echo htmlspecialchars($a['created_at']); ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($activities)): ?>
            <div class="p-4 text-center text-gray-500">No recent activity.</div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 