<?php
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
        // Begin transaction to safely delete with FK constraints
        $conn->begin_transaction();
        try {
            // Null out audit logs user reference (keep logs)
            $conn->query("UPDATE audit_logs SET user_id = NULL WHERE user_id = $id");
            // Remove dependent rows owned by the user
            $conn->query("DELETE FROM favorites WHERE user_id = $id");
            $conn->query("DELETE FROM comments WHERE user_id = $id");
            $conn->query("DELETE FROM ratings WHERE user_id = $id");
            $conn->query("DELETE FROM reports WHERE user_id = $id");
            $conn->query("DELETE FROM suggested_edits WHERE user_id = $id");
            // Delete user's spots (will cascade delete related rows that reference spots via their own FKs)
            $conn->query("DELETE FROM spots WHERE user_id = $id");
            // Finally delete the user
            $conn->query("DELETE FROM users WHERE id = $id");
            $conn->commit();
            audit_log($conn, 'user_delete', 'user', $id, null);
            header('Location: manage_users.php?msg=User+deleted');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: manage_users.php?msg=Failed+to+delete+user');
            exit();
        }
    }
}
if (isset($_GET['promote']) && isset($_GET['role'])) {
    $id = intval($_GET['promote']);
    $role = $_GET['role'];
    if (in_array($role, ['user','faculty','admin']) && $id !== $admin_id) {
        $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
        $stmt->bind_param('si', $role, $id);
        $stmt->execute();
        audit_log($conn, 'user_role_change', 'user', $id, ['role' => $role]);
        header('Location: manage_users.php?msg=Role+updated');
        exit();
    }
}
// Filters, sorting, pagination
$roleFilter = isset($_GET['role']) && in_array($_GET['role'], ['all','user','faculty','admin']) ? $_GET['role'] : 'all';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$allowedSort = [ 'name' => 'name', 'email' => 'email', 'role' => 'role' ];
$sort = isset($_GET['sort']) && isset($allowedSort[$_GET['sort']]) ? $_GET['sort'] : 'role';
$dir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc') ? 'DESC' : 'ASC';
$perPage = isset($_GET['per_page']) ? max(5, min(50, intval($_GET['per_page']))) : 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$where = [ 'id != ' . intval($admin_id) ];
if ($roleFilter !== 'all') { $where[] = "role = '" . $conn->real_escape_string($roleFilter) . "'"; }
if ($q !== '') { $safeQ = $conn->real_escape_string($q); $where[] = "(name LIKE '%$safeQ%' OR email LIKE '%$safeQ%')"; }
$whereSql = 'WHERE ' . implode(' AND ', $where);

$countRes = $conn->query("SELECT COUNT(*) AS cnt FROM users $whereSql");
$total = $countRes ? intval($countRes->fetch_assoc()['cnt']) : 0;
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

$sql = "SELECT id, name, email, role FROM users $whereSql ORDER BY " . $allowedSort[$sort] . " $dir LIMIT $perPage OFFSET $offset";
$res = $conn->query($sql);
$users = [];
if ($res) { while ($row = $res->fetch_assoc()) { $users[] = $row; } }
include 'includes/header.php';
?>
<div class="max-w-3xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Manage Users</h2>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs mb-1">Role</label>
            <select name="role" class="px-2 py-1 border rounded text-sm">
                <option value="all" <?php if ($roleFilter==='all') echo 'selected'; ?>>All</option>
                <option value="admin" <?php if ($roleFilter==='admin') echo 'selected'; ?>>Admin</option>
                <option value="faculty" <?php if ($roleFilter==='faculty') echo 'selected'; ?>>Faculty</option>
                <option value="user" <?php if ($roleFilter==='user') echo 'selected'; ?>>User</option>
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs mb-1">Search</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="w-full px-2 py-1 border rounded text-sm" placeholder="Name or email" />
        </div>
        <div>
            <label class="block text-xs mb-1">Per page</label>
            <select name="per_page" class="px-2 py-1 border rounded text-sm">
                <?php foreach ([10,20,30,50] as $pp): ?>
                    <option value="<?php echo $pp; ?>" <?php if ($perPage===$pp) echo 'selected'; ?>><?php echo $pp; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Apply</button>
    </form>
    <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2"><a href="<?php echo build_user_sort_url('name'); ?>" class="hover:underline">Name</a></th>
                <th class="px-4 py-2"><a href="<?php echo build_user_sort_url('email'); ?>" class="hover:underline">Email</a></th>
                <th class="px-4 py-2"><a href="<?php echo build_user_sort_url('role'); ?>" class="hover:underline">Role</a></th>
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
    <div class="flex items-center justify-between mt-4 text-sm">
        <div>Showing <?php echo count($users); ?> of <?php echo $total; ?> users</div>
        <div class="space-x-1">
            <?php
            function build_user_page_url($p) { $params = $_GET; $params['page'] = $p; return 'manage_users.php?' . http_build_query($params); }
            ?>
            <a class="px-2 py-1 border rounded <?php echo $page<=1?'opacity-50 pointer-events-none':''; ?>" href="<?php echo build_user_page_url(max(1,$page-1)); ?>">Prev</a>
            <span class="px-2">Page <?php echo $page; ?> / <?php echo $totalPages; ?></span>
            <a class="px-2 py-1 border rounded <?php echo $page>=$totalPages?'opacity-50 pointer-events-none':''; ?>" href="<?php echo build_user_page_url(min($totalPages,$page+1)); ?>">Next</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 
<?php
function build_user_sort_url($field) {
    $params = $_GET; $params['sort'] = $field; $params['dir'] = (isset($_GET['sort']) && $_GET['sort']===$field && (isset($_GET['dir']) && strtolower($_GET['dir'])==='asc')) ? 'desc' : 'asc'; return 'manage_users.php?' . http_build_query($params);
}
?>