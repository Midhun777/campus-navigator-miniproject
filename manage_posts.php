<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Handle approve/reject/delete
if (($role === 'admin' || $role === 'faculty') && isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE spots SET status='approved' WHERE id=$id");
    audit_log($conn, 'spot_approve', 'spot', $id, null);
}
if (($role === 'admin' || $role === 'faculty') && isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("UPDATE spots SET status='pending' WHERE id=$id");
    audit_log($conn, 'spot_set_pending', 'spot', $id, null);
}
if ((($role === 'admin' || $role === 'faculty') && isset($_GET['delete'])) || ($role === 'user' && isset($_GET['delete']))) {
    $id = intval($_GET['delete']);
    // Only allow delete if admin/faculty or user owns the post
    if ($role === 'admin' || $role === 'faculty') {
        $conn->query("DELETE FROM spots WHERE id=$id");
        audit_log($conn, 'spot_delete', 'spot', $id, ['by' => $role]);
    } else {
        $conn->query("DELETE FROM spots WHERE id=$id AND user_id=$user_id");
        audit_log($conn, 'spot_delete', 'spot', $id, ['by' => 'owner']);
    }
}

// Fetch posts
if ($role === 'admin') {
    $res = $conn->query("SELECT spots.*, users.name AS user_name, categories.name AS category_name FROM spots LEFT JOIN users ON spots.user_id = users.id LEFT JOIN categories ON spots.category_id = categories.id ORDER BY created_at DESC");
} elseif ($role === 'faculty') {
    $res = $conn->query("SELECT spots.*, users.name AS user_name, categories.name AS category_name FROM spots LEFT JOIN users ON spots.user_id = users.id LEFT JOIN categories ON spots.category_id = categories.id ORDER BY created_at DESC");
} else {
    $res = $conn->query("SELECT spots.*, users.name AS user_name, categories.name AS category_name FROM spots LEFT JOIN users ON spots.user_id = users.id LEFT JOIN categories ON spots.category_id = categories.id WHERE spots.user_id = $user_id ORDER BY created_at DESC");
}
$posts = [];
while ($row = $res->fetch_assoc()) {
    $posts[] = $row;
}
?>
<div class="max-w-5xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Manage Posts</h2>
    <div class="overflow-x-auto">
    <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Category</th>
                <th class="px-4 py-2">User</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?php echo htmlspecialchars($post['name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($post['category_name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($post['user_name']); ?></td>
                <td class="px-4 py-2 capitalize"><?php echo htmlspecialchars($post['status']); ?></td>
                <td class="px-4 py-2 space-x-2">
                    <a href="spot_details.php?id=<?php echo $post['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 rounded text-white bg-green-600 hover:bg-green-700 text-xs">View</a>
                    <?php if ($role === 'admin' || $role === 'faculty'): ?>
                        <?php if ($post['status'] === 'pending'): ?>
                            <a href="manage_posts.php?approve=<?php echo $post['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs">Approve</a>
                        <?php else: ?>
                            <a href="manage_posts.php?reject=<?php echo $post['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 rounded text-white bg-yellow-600 hover:bg-yellow-700 text-xs">Set Pending</a>
                        <?php endif; ?>
                        <a href="manage_posts.php?delete=<?php echo $post['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 rounded text-white bg-red-600 hover:bg-red-700 text-xs" onclick="return confirm('Delete this post?');">Delete</a>
                    <?php elseif ($role === 'user' && $post['user_id'] == $user_id): ?>
                        <a href="edit_spot.php?id=<?php echo $post['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs">Edit</a>
                        <a href="manage_posts.php?delete=<?php echo $post['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 rounded text-white bg-red-600 hover:bg-red-700 text-xs" onclick="return confirm('Delete this post?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
            <tr><td colspan="5" class="text-center text-gray-500 py-4">No posts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 