<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
require_login();
if (!is_admin()) {
    echo '<div class="text-center mt-8 text-red-600">Access denied.</div>';
    include 'includes/footer.php';
    exit();
}
$admin_id = $_SESSION['user_id'];
// Handle actions
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id !== $admin_id) {
        $conn->query("DELETE FROM users WHERE id=$id");
    }
}
if (isset($_GET['promote']) && isset($_GET['role'])) {
    $id = intval($_GET['promote']);
    $role = $_GET['role'];
    if (in_array($role, ['user','faculty','admin']) && $id !== $admin_id) {
        $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
        $stmt->bind_param('si', $role, $id);
        $stmt->execute();
    }
}
// Fetch users
$res = $conn->query("SELECT * FROM users WHERE id != $admin_id ORDER BY role, name");
$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
?>
<div class="max-w-3xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Manage Users</h2>
    <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Role</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-4 py-2 capitalize"><?php echo htmlspecialchars($user['role']); ?></td>
                <td class="px-4 py-2 space-x-2">
                    <?php if ($user['role'] !== 'admin'): ?>
                        <a href="manage_users.php?promote=<?php echo $user['id']; ?>&role=admin" class="text-blue-600 hover:underline text-xs">Promote to Admin</a>
                    <?php endif; ?>
                    <?php if ($user['role'] !== 'faculty'): ?>
                        <a href="manage_users.php?promote=<?php echo $user['id']; ?>&role=faculty" class="text-green-600 hover:underline text-xs">Promote to Faculty</a>
                    <?php endif; ?>
                    <?php if ($user['role'] !== 'user'): ?>
                        <a href="manage_users.php?promote=<?php echo $user['id']; ?>&role=user" class="text-yellow-600 hover:underline text-xs">Demote to User</a>
                    <?php endif; ?>
                    <a href="manage_users.php?delete=<?php echo $user['id']; ?>" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="4" class="text-center text-gray-500 py-4">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?> 