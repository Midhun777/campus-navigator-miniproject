<?php
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
    header('Location: manage_posts.php?msg=Post+approved');
    exit();
}
if (($role === 'admin' || $role === 'faculty') && isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("UPDATE spots SET status='pending' WHERE id=$id");
    audit_log($conn, 'spot_set_pending', 'spot', $id, null);
    header('Location: manage_posts.php?msg=Post+set+to+pending');
    exit();
}
if ((($role === 'admin' || $role === 'faculty') && isset($_GET['delete'])) || ($role === 'user' && isset($_GET['delete']))) {
    $id = intval($_GET['delete']);
    // Only allow delete if admin/faculty or user owns the post
    if ($role === 'admin' || $role === 'faculty') {
        $conn->query("DELETE FROM spots WHERE id=$id");
        audit_log($conn, 'spot_delete', 'spot', $id, ['by' => $role]);
        header('Location: manage_posts.php?msg=Post+deleted');
        exit();
    } else {
        $conn->query("DELETE FROM spots WHERE id=$id AND user_id=$user_id");
        audit_log($conn, 'spot_delete', 'spot', $id, ['by' => 'owner']);
        header('Location: manage_posts.php?msg=Post+deleted');
        exit();
    }
}

// Fetch filters
$status = isset($_GET['status']) && in_array($_GET['status'], ['pending','approved','all']) ? $_GET['status'] : 'all';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Sorting
$allowedSort = [
    'created_at' => 'spots.created_at',
    'name' => 'spots.name',
    'category' => 'categories.name',
    'user' => 'users.name',
    'status' => 'spots.status'
];
$sort = isset($_GET['sort']) && isset($allowedSort[$_GET['sort']]) ? $_GET['sort'] : 'created_at';
$dir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

// Pagination
$perPage = isset($_GET['per_page']) ? max(5, min(50, intval($_GET['per_page']))) : 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Build WHERE
$where = [];
if ($role === 'user') {
    $where[] = 'spots.user_id = ' . intval($user_id);
}
if ($status !== 'all') {
    $where[] = "spots.status = '" . ($status === 'pending' ? 'pending' : 'approved') . "'";
}
if ($categoryId > 0) {
    $where[] = 'spots.category_id = ' . $categoryId;
}
if ($q !== '') {
    $safeQ = $conn->real_escape_string($q);
    $where[] = "(spots.name LIKE '%$safeQ%' OR spots.description LIKE '%$safeQ%')";
}
$whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

// Counts
$countSql = "SELECT COUNT(*) AS cnt FROM spots LEFT JOIN users ON spots.user_id = users.id LEFT JOIN categories ON spots.category_id = categories.id $whereSql";
$countRes = $conn->query($countSql);
$total = $countRes ? intval($countRes->fetch_assoc()['cnt']) : 0;
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

// Data
$sql = "SELECT spots.*, users.name AS user_name, categories.name AS category_name FROM spots LEFT JOIN users ON spots.user_id = users.id LEFT JOIN categories ON spots.category_id = categories.id $whereSql ORDER BY " . $allowedSort[$sort] . " $dir LIMIT $perPage OFFSET $offset";
$res = $conn->query($sql);
$posts = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $posts[] = $r;
    }
}

// Categories for filter
$catOptions = [];
$catRes = $conn->query('SELECT id, name FROM categories ORDER BY name');
if ($catRes) {
    while ($c = $catRes->fetch_assoc()) { $catOptions[] = $c; }
}
include 'includes/header.php';
?>
<div class="max-w-5xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Manage Posts</h2>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs mb-1">Status</label>
            <select name="status" class="px-2 py-1 border rounded text-sm">
                <option value="all" <?php if ($status==='all') echo 'selected'; ?>>All</option>
                <option value="pending" <?php if ($status==='pending') echo 'selected'; ?>>Pending</option>
                <option value="approved" <?php if ($status==='approved') echo 'selected'; ?>>Approved</option>
            </select>
        </div>
        <div>
            <label class="block text-xs mb-1">Category</label>
            <select name="category" class="px-2 py-1 border rounded text-sm">
                <option value="0">All</option>
                <?php foreach ($catOptions as $opt): ?>
                    <option value="<?php echo $opt['id']; ?>" <?php if ($categoryId===$opt['id']) echo 'selected'; ?>><?php echo htmlspecialchars($opt['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs mb-1">Search</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="w-full px-2 py-1 border rounded text-sm" placeholder="Name or description" />
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
    <div class="overflow-x-auto">
    <table class="min-w-full bg-white dark:bg-gray-800 rounded shadow">
        <thead>
            <tr>
                <th class="px-4 py-2"><a href="<?php echo build_sort_url('name'); ?>" class="hover:underline">Name</a></th>
                <th class="px-4 py-2"><a href="<?php echo build_sort_url('category'); ?>" class="hover:underline">Category</a></th>
                <th class="px-4 py-2"><a href="<?php echo build_sort_url('user'); ?>" class="hover:underline">User</a></th>
                <th class="px-4 py-2"><a href="<?php echo build_sort_url('status'); ?>" class="hover:underline">Status</a></th>
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
    <div class="flex items-center justify-between mt-4 text-sm">
        <div>Showing <?php echo count($posts); ?> of <?php echo $total; ?> results</div>
        <div class="space-x-1">
            <?php
            function build_page_url($p) {
                $params = $_GET; $params['page'] = $p; return 'manage_posts.php?' . http_build_query($params);
            }
            function build_sort_url_local($field) {
                $params = $_GET; $params['sort'] = $field; $params['dir'] = (isset($_GET['sort']) && $_GET['sort']===$field && (isset($_GET['dir']) && strtolower($_GET['dir'])==='asc')) ? 'desc' : 'asc'; return 'manage_posts.php?' . http_build_query($params);
            }
            ?>
            <a class="px-2 py-1 border rounded <?php echo $page<=1?'opacity-50 pointer-events-none':''; ?>" href="<?php echo build_page_url(max(1,$page-1)); ?>">Prev</a>
            <span class="px-2">Page <?php echo $page; ?> / <?php echo $totalPages; ?></span>
            <a class="px-2 py-1 border rounded <?php echo $page>=$totalPages?'opacity-50 pointer-events-none':''; ?>" href="<?php echo build_page_url(min($totalPages,$page+1)); ?>">Next</a>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 
<?php
// Helper for header scope (used above)
function build_sort_url($field) {
    $params = $_GET; $params['sort'] = $field; $params['dir'] = (isset($_GET['sort']) && $_GET['sort']===$field && (isset($_GET['dir']) && strtolower($_GET['dir'])==='asc')) ? 'desc' : 'asc'; return 'manage_posts.php?' . http_build_query($params);
}
?>