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

// Simple filters
$limit = isset($_GET['limit']) ? max(10, intval($_GET['limit'])) : 100;
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

$sql = "SELECT audit_logs.*, users.name as user_name FROM audit_logs LEFT JOIN users ON audit_logs.user_id = users.id";
$params = [];
$types = '';
if ($action !== '') {
    $sql .= " WHERE audit_logs.action = ?";
    $params[] = $action;
    $types .= 's';
}
$sql .= " ORDER BY audit_logs.created_at DESC LIMIT ?";
$params[] = $limit;
$types .= 'i';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$logs = [];
while ($row = $res->fetch_assoc()) { $logs[] = $row; }
?>
<div class="max-w-5xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Audit Logs</h2>
    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="action" value="<?php echo htmlspecialchars($action); ?>" placeholder="Filter by action (e.g. login)" class="px-3 py-2 border rounded">
        <input type="number" name="limit" value="<?php echo htmlspecialchars($limit); ?>" class="px-3 py-2 border rounded w-32">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
    </form>
    <div class="overflow-x-auto">
    <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow text-sm">
        <thead>
            <tr>
                <th class="px-3 py-2">Time</th>
                <th class="px-3 py-2">User</th>
                <th class="px-3 py-2">Action</th>
                <th class="px-3 py-2">Entity</th>
                <th class="px-3 py-2">Entity ID</th>
                <th class="px-3 py-2">Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr class="border-t">
                <td class="px-3 py-2 whitespace-nowrap"><?php echo htmlspecialchars($log['created_at']); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars($log['user_name'] ?? 'Guest'); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars($log['action']); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars($log['entity_type']); ?></td>
                <td class="px-3 py-2"><?php echo htmlspecialchars((string)$log['entity_id']); ?></td>
                <td class="px-3 py-2 max-w-lg break-words"><?php echo htmlspecialchars((string)$log['details']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
            <tr><td colspan="6" class="text-center text-gray-500 py-4">No logs.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>


