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
        $conn->query("DELETE FROM users WHERE id=$id AND role='faculty'");
    }
}
if (isset($_GET['promote']) && isset($_GET['role'])) {
    $id = intval($_GET['promote']);
    $role = $_GET['role'];
    if (in_array($role, ['user','faculty','admin']) && $id !== $admin_id) {
        $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=? AND role="faculty"');
        $stmt->bind_param('si', $role, $id);
        $stmt->execute();
    }
}
// Fetch faculties
$res = $conn->query("SELECT * FROM users WHERE role='faculty' ORDER BY name");
$faculties = [];
while ($row = $res->fetch_assoc()) {
    $faculties[] = $row;
}
?>
<div class="max-w-3xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Manage Faculties</h2>
    <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faculties as $faculty): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?php echo htmlspecialchars($faculty['name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($faculty['email']); ?></td>
                <td class="px-4 py-2 space-x-2">
                    <a href="manage_faculties.php?promote=<?php echo $faculty['id']; ?>&role=admin" class="text-blue-600 hover:underline text-xs">Promote to Admin</a>
                    <a href="manage_faculties.php?promote=<?php echo $faculty['id']; ?>&role=user" class="text-yellow-600 hover:underline text-xs">Demote to User</a>
                    <a href="manage_faculties.php?delete=<?php echo $faculty['id']; ?>" class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete this faculty?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($faculties)): ?>
            <tr><td colspan="3" class="text-center text-gray-500 py-4">No faculties found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?> 