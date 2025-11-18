<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
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
        audit_log($conn, 'faculty_delete', 'user', $id, null);
    }
}
// Approve a pending faculty request
if (isset($_GET['approve_request'])) {
    $id = intval($_GET['approve_request']);
    if ($id !== $admin_id) {
        // Only approve if user had a pending request
        $stmt = $conn->prepare('UPDATE users SET role="faculty", faculty_status="none" WHERE id=? AND faculty_status="pending"');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        audit_log($conn, 'faculty_approved', 'user', $id, null);
    }
}
// Deny/cancel a pending faculty request
if (isset($_GET['deny_request'])) {
    $id = intval($_GET['deny_request']);
    if ($id !== $admin_id) {
        $stmt = $conn->prepare('UPDATE users SET faculty_status="none" WHERE id=? AND faculty_status="pending"');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        audit_log($conn, 'faculty_request_denied', 'user', $id, null);
    }
}
if (isset($_GET['promote']) && isset($_GET['role'])) {
    $id = intval($_GET['promote']);
    $role = $_GET['role'];
    if (in_array($role, ['user','faculty','admin']) && $id !== $admin_id) {
        // Allow direct role changes from admin panel; if promoting to faculty, clear any faculty_status
        if ($role === 'faculty') {
            $stmt = $conn->prepare('UPDATE users SET role=?, faculty_status="none" WHERE id=?');
            $stmt->bind_param('si', $role, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
            $stmt->bind_param('si', $role, $id);
            $stmt->execute();
        }
        audit_log($conn, 'faculty_role_change', 'user', $id, ['role' => $role]);
    }
}
// Fetch pending faculty requests and approved faculties
$pendingRes = $conn->query("SELECT * FROM users WHERE faculty_status='pending' ORDER BY name");
$pending = [];
while ($row = $pendingRes->fetch_assoc()) { $pending[] = $row; }

$res = $conn->query("SELECT * FROM users WHERE role='faculty' ORDER BY name");
$faculties = [];
while ($row = $res->fetch_assoc()) { $faculties[] = $row; }
?>
<div class="max-w-3xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Manage Faculties</h2>
    <?php if (!empty($pending)): ?>
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Pending Faculty Requests</h3>
        <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow mb-4">
            <thead>
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $p): ?>
                <tr class="border-t">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($p['name']); ?></td>
                    <td class="px-4 py-2"><?php echo htmlspecialchars($p['email']); ?></td>
                    <td class="px-4 py-2 space-x-2">
                        <a href="manage_faculties.php?approve_request=<?php echo $p['id']; ?>" class="text-green-600 hover:underline text-xs">Approve</a>
                        <a href="manage_faculties.php?deny_request=<?php echo $p['id']; ?>" class="text-red-600 hover:underline text-xs">Deny</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
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