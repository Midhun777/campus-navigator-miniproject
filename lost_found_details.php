<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { echo '<div class="p-6">Invalid ID</div>'; include 'includes/footer.php'; exit(); }

// Fetch item
$stmt = $conn->prepare('SELECT lf.*, u.name as user_name FROM lost_found lf LEFT JOIN users u ON lf.user_id = u.id WHERE lf.id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) { echo '<div class="p-6">Not found</div>'; include 'includes/footer.php'; exit(); }

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $response_type = isset($_POST['response_type']) && in_array($_POST['response_type'], ['found_report','lost_report','comment']) ? $_POST['response_type'] : 'comment';
    $contact = trim($_POST['contact']);
    if ($message !== '') {
        $ins = $conn->prepare('INSERT INTO lost_found_responses (lf_id, user_id, response_type, message, contact) VALUES (?, ?, ?, ?, ?)');
        $ins->bind_param('iisss', $id, $_SESSION['user_id'], $response_type, $message, $contact);
        $ins->execute();
        audit_log($conn, 'lf_response', 'lost_found', $id, ['type' => $response_type]);
        header('Location: lost_found_details.php?id=' . $id);
        exit();
    }
}

// Handle resolve (owner only)
if (isset($_GET['resolve']) && $_SESSION['user_id'] == $item['user_id']) {
    $conn->query('UPDATE lost_found SET status="resolved" WHERE id=' . $id);
    audit_log($conn, 'lf_resolve', 'lost_found', $id, null);
    header('Location: lost_found_details.php?id=' . $id);
    exit();
}

// Fetch responses
$responses = [];
$res = $conn->query('SELECT r.*, u.name FROM lost_found_responses r LEFT JOIN users u ON r.user_id = u.id WHERE lf_id=' . $id . ' ORDER BY r.created_at DESC');
while ($row = $res->fetch_assoc()) { $responses[] = $row; }
?>
<div class="max-w-3xl mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xs uppercase tracking-wide mb-1 <?php echo $item['type']==='lost'?'text-red-600':'text-green-600'; }?>"><?php echo strtoupper($item['type']); ?></div>
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($item['title']); ?></h2>
            <div class="text-sm text-gray-500">by <?php echo htmlspecialchars($item['user_name']); ?> • <?php echo htmlspecialchars($item['created_at']); ?> • <span class="px-2 py-0.5 rounded-full <?php echo $item['status']==='open'?'bg-yellow-200 text-yellow-800':'bg-green-200 text-green-800'; }?>"><?php echo htmlspecialchars($item['status']); ?></span></div>
        </div>
        <?php if ($_SESSION['user_id'] == $item['user_id'] && $item['status'] !== 'resolved'): ?>
            <a href="lost_found_details.php?id=<?php echo $id; }?>&resolve=1" class="px-3 py-1.5 bg-green-600 text-white rounded">Mark Resolved</a>
        <?php endif; ?>
    </div>
    <?php if ($item['image']): ?><img src="<?php echo $item['image']; }?>" alt="Item" class="h-64 w-full object-cover rounded mb-4"><?php endif; ?>
    <div class="mb-3"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
    <div class="mb-4 text-sm flex flex-wrap gap-2">
        <?php if ($item['location']): ?><span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700">📍 <?php echo htmlspecialchars($item['location']); ?></span><?php endif; ?>
        <?php if ($item['event_date']): ?><span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700">🗓 <?php echo htmlspecialchars($item['event_date']); ?></span><?php endif; ?>
        <?php if ($item['contact']): ?><span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700">☎ <?php echo htmlspecialchars($item['contact']); ?></span><?php endif; ?>
    </div>

    <h3 class="font-semibold mb-2">Responses</h3>
    <div class="space-y-2 mb-6">
        <?php foreach ($responses as $r): ?>
        <div class="bg-gray-100 dark:bg-gray-700 rounded p-2">
            <div class="text-xs text-gray-500 mb-1"><?php echo htmlspecialchars($r['created_at']); ?> • <?php echo htmlspecialchars($r['name']); ?> • <?php echo htmlspecialchars($r['response_type']); ?></div>
            <div><?php echo nl2br(htmlspecialchars($r['message'])); ?></div>
            <?php if ($r['contact']): ?><div class="text-xs mt-1">Contact: <?php echo htmlspecialchars($r['contact']); ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($responses)): ?><div class="text-gray-500">No responses yet.</div><?php endif; ?>
    </div>

    <?php if ($item['status'] !== 'resolved'): ?>
    <h3 class="font-semibold mb-2">Add a response</h3>
    <form method="POST" class="space-y-3">
        <label class="block">
            <span class="text-sm">Response Type</span>
            <select name="response_type" class="px-3 py-2 border rounded">
                <option value="comment">Comment</option>
                <option value="found_report" <?php echo $item['type']==='lost'?'':'disabled'; ?>>I found it</option>
                <option value="lost_report" <?php echo $item['type']==='found'?'':'disabled'; ?>>I lost it</option>
            </select>
        </label>
        <label class="block">
            <span class="text-sm">Message</span>
            <textarea name="message" required class="w-full px-3 py-2 border rounded"></textarea>
        </label>
        <label class="block">
            <span class="text-sm">Your contact (optional)</span>
            <input type="text" name="contact" class="w-full px-3 py-2 border rounded">
        </label>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Submit</button>
    </form>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>


