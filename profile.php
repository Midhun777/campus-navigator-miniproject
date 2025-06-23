<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

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
?>
<div class="max-w-4xl mx-auto mt-8">
    <div class="flex items-center space-x-4 mb-8">
        <img src="<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/images/default_user.png'; ?>" alt="Profile" class="h-20 w-20 rounded-full object-cover">
        <div>
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h2>
            <div class="text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
            <div class="text-sm text-gray-400 capitalize">Role: <?php echo htmlspecialchars($user['role']); ?></div>
        </div>
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
</div>
<?php include 'includes/footer.php'; ?> 