<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

$type = isset($_GET['type']) && in_array($_GET['type'], ['lost','found']) ? $_GET['type'] : '';
$status = isset($_GET['status']) && in_array($_GET['status'], ['open','resolved']) ? $_GET['status'] : '';

$sql = "SELECT lf.*, u.name as user_name FROM lost_found lf LEFT JOIN users u ON lf.user_id = u.id WHERE 1=1";
if ($type) { $sql .= " AND lf.type='" . $conn->real_escape_string($type) . "'"; }
if ($status) { $sql .= " AND lf.status='" . $conn->real_escape_string($status) . "'"; }
$sql .= " ORDER BY lf.created_at DESC";
$res = $conn->query($sql);
$items = [];
while ($row = $res->fetch_assoc()) { $items[] = $row; }
?>
<div class="max-w-5xl mx-auto mt-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold">Lost &amp; Found</h2>
        <a href="lost_found_add.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">+ New Report</a>
    </div>
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <select name="type" class="px-3 py-2 border rounded">
            <option value="">All Types</option>
            <option value="lost" <?php echo $type==='lost'?'selected':''; ?>>Lost</option>
            <option value="found" <?php echo $type==='found'?'selected':''; ?>>Found</option>
        </select>
        <select name="status" class="px-3 py-2 border rounded">
            <option value="">All Status</option>
            <option value="open" <?php echo $status==='open'?'selected':''; ?>>Open</option>
            <option value="resolved" <?php echo $status==='resolved'?'selected':''; ?>>Resolved</option>
        </select>
        <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($items as $it): ?>
        <div class="bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
            <div class="text-xs uppercase tracking-wide mb-1 <?php echo $it['type']==='lost'?'text-red-600':'text-green-600'; ?>"><?php echo strtoupper($it['type']); ?></div>
            <div class="font-semibold text-lg mb-1"><?php echo htmlspecialchars($it['title']); ?></div>
            <div class="text-sm text-gray-500 mb-2">by <?php echo htmlspecialchars($it['user_name']); ?> • <?php echo htmlspecialchars($it['created_at']); ?></div>
            <?php if ($it['image']): ?><img src="<?php echo $it['image']; }?>" alt="Item" class="h-40 w-full object-cover rounded mb-2"><?php endif; ?>
            <div class="text-sm mb-2"><?php echo nl2br(htmlspecialchars($it['description'])); ?></div>
            <div class="text-xs mb-2 flex flex-wrap gap-2">
                <?php if ($it['location']): ?><span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700">📍 <?php echo htmlspecialchars($it['location']); ?></span><?php endif; ?>
                <?php if ($it['event_date']): ?><span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700">🗓 <?php echo htmlspecialchars($it['event_date']); ?></span><?php endif; ?>
                <?php if ($it['contact']): ?><span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700">☎ <?php echo htmlspecialchars($it['contact']); ?></span><?php endif; ?>
                <span class="px-2 py-0.5 rounded-full <?php echo $it['status']==='open'?'bg-yellow-200 text-yellow-800':'bg-green-200 text-green-800'; }?>"><?php echo htmlspecialchars($it['status']); ?></span>
            </div>
            <div class="mt-auto flex justify-end">
                <a href="lost_found_details.php?id=<?php echo $it['id']; }?>" class="text-blue-600 hover:underline">View</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
        <div class="text-gray-500">No reports yet.</div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>


