<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/functions.php';
require_login();

// Only faculty or admin may view
if (!(isset($_SESSION['role']) && in_array($_SESSION['role'], ['faculty','admin']))) {
    include 'includes/header.php';
    echo '<div class="text-center mt-8 text-red-600">Access denied.</div>';
    include 'includes/footer.php';
    exit();
}

// Filters
$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['reports','suggestions']) ? $_GET['tab'] : 'reports';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Reports
$reports = [];
$whereReports = [];
if ($q !== '') {
    $safeQ = $conn->real_escape_string($q);
    $whereReports[] = "(spots.name LIKE '%$safeQ%' OR reports.reason LIKE '%$safeQ%' OR users.name LIKE '%$safeQ%')";
}
$whereSqlReports = empty($whereReports) ? '' : ('WHERE ' . implode(' AND ', $whereReports));
$sqlReports = "SELECT reports.*, spots.name AS spot_name, users.name AS user_name FROM reports LEFT JOIN spots ON reports.spot_id = spots.id LEFT JOIN users ON reports.user_id = users.id $whereSqlReports ORDER BY reports.created_at DESC LIMIT 200";
$resReports = $conn->query($sqlReports);
if ($resReports) {
    while ($row = $resReports->fetch_assoc()) { $reports[] = $row; }
}

// Suggestions
$suggestions = [];
$whereSug = [];
if ($q !== '') {
    $safeQ = $conn->real_escape_string($q);
    $whereSug[] = "(spots.name LIKE '%$safeQ%' OR suggested_edits.suggestion LIKE '%$safeQ%' OR users.name LIKE '%$safeQ%')";
}
$whereSqlSug = empty($whereSug) ? '' : ('WHERE ' . implode(' AND ', $whereSug));
$sqlSug = "SELECT suggested_edits.*, spots.name AS spot_name, users.name AS user_name FROM suggested_edits LEFT JOIN spots ON suggested_edits.spot_id = spots.id LEFT JOIN users ON suggested_edits.user_id = users.id $whereSqlSug ORDER BY suggested_edits.created_at DESC LIMIT 200";
$resSug = $conn->query($sqlSug);
if ($resSug) {
    while ($row = $resSug->fetch_assoc()) { $suggestions[] = $row; }
}

include 'includes/header.php';
?>
<div class="max-w-6xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Moderation</h2>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>" />
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs mb-1">Search</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="w-full px-2 py-1 border rounded text-sm" placeholder="Spot, user, reason/suggestion" />
        </div>
        <div class="flex items-center gap-2">
            <a href="moderation.php?tab=reports" class="px-3 py-2 rounded text-sm <?php echo $tab==='reports'?'bg-blue-600 text-white':'bg-gray-200 dark:bg-gray-700'; ?>">Reports (<?php echo count($reports); ?>)</a>
            <a href="moderation.php?tab=suggestions" class="px-3 py-2 rounded text-sm <?php echo $tab==='suggestions'?'bg-blue-600 text-white':'bg-gray-200 dark:bg-gray-700'; ?>">Suggestions (<?php echo count($suggestions); ?>)</a>
        </div>
        <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Apply</button>
    </form>

    <?php if ($tab === 'reports'): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Spot</th>
                        <th class="px-4 py-2">Reported By</th>
                        <th class="px-4 py-2">Reason</th>
                        <th class="px-4 py-2">Created</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2">
                            <a href="spot_details.php?id=<?php echo (int)$r['spot_id']; ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($r['spot_name'] ?: ('#' . $r['spot_id'])); ?></a>
                        </td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($r['user_name'] ?: 'Unknown'); ?></td>
                        <td class="px-4 py-2 max-w-[40ch] truncate" title="<?php echo htmlspecialchars($r['reason']); ?>"><?php echo htmlspecialchars($r['reason']); ?></td>
                        <td class="px-4 py-2 text-sm text-gray-500"><?php echo htmlspecialchars($r['created_at']); ?></td>
                        <td class="px-4 py-2 text-sm">
                            <a class="text-green-600 hover:underline" href="spot_details.php?id=<?php echo (int)$r['spot_id']; ?>">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reports)): ?>
                    <tr><td colspan="5" class="text-center text-gray-500 py-4">No reports found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Spot</th>
                        <th class="px-4 py-2">Suggested By</th>
                        <th class="px-4 py-2">Suggestion</th>
                        <th class="px-4 py-2">Created</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suggestions as $s): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2">
                            <a href="spot_details.php?id=<?php echo (int)$s['spot_id']; ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($s['spot_name'] ?: ('#' . $s['spot_id'])); ?></a>
                        </td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($s['user_name'] ?: 'Unknown'); ?></td>
                        <td class="px-4 py-2 max-w-[40ch] truncate" title="<?php echo htmlspecialchars($s['suggestion']); ?>"><?php echo htmlspecialchars($s['suggestion']); ?></td>
                        <td class="px-4 py-2 text-sm text-gray-500"><?php echo htmlspecialchars($s['created_at']); ?></td>
                        <td class="px-4 py-2 text-sm">
                            <a class="text-green-600 hover:underline" href="spot_details.php?id=<?php echo (int)$s['spot_id']; ?>">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($suggestions)): ?>
                    <tr><td colspan="5" class="text-center text-gray-500 py-4">No suggestions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>


