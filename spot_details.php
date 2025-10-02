<?php
include 'includes/header.php';
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$spot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$spot_id) {
    echo '<div class="text-center mt-8 text-red-600">Invalid spot ID.</div>';
    include 'includes/footer.php';
    exit();
}

// Fetch spot details
$stmt = $conn->prepare('SELECT spots.*, categories.name AS category_name FROM spots LEFT JOIN categories ON spots.category_id = categories.id WHERE spots.id = ?');
$stmt->bind_param('i', $spot_id);
$stmt->execute();
$spot = $stmt->get_result()->fetch_assoc();
if (!$spot) {
    echo '<div class="text-center mt-8 text-red-600">Spot not found.</div>';
    include 'includes/footer.php';
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle favorite
if (isset($_POST['favorite'])) {
    $fav_check = $conn->prepare('SELECT id FROM favorites WHERE user_id = ? AND spot_id = ?');
    $fav_check->bind_param('ii', $user_id, $spot_id);
    $fav_check->execute();
    $fav_check->store_result();
    if ($fav_check->num_rows == 0) {
        $fav = $conn->prepare('INSERT INTO favorites (user_id, spot_id) VALUES (?, ?)');
        $fav->bind_param('ii', $user_id, $spot_id);
        $fav->execute();
    }
}
if (isset($_POST['unfavorite'])) {
    $unfav = $conn->prepare('DELETE FROM favorites WHERE user_id = ? AND spot_id = ?');
    $unfav->bind_param('ii', $user_id, $spot_id);
    $unfav->execute();
}
// Check if favorited
$fav_check = $conn->prepare('SELECT id FROM favorites WHERE user_id = ? AND spot_id = ?');
$fav_check->bind_param('ii', $user_id, $spot_id);
$fav_check->execute();
$fav_check->store_result();
$is_favorited = $fav_check->num_rows > 0;

// Handle rating
if (isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    // Upsert rating
    $check = $conn->prepare('SELECT id FROM ratings WHERE user_id = ? AND spot_id = ?');
    $check->bind_param('ii', $user_id, $spot_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $upd = $conn->prepare('UPDATE ratings SET rating = ? WHERE user_id = ? AND spot_id = ?');
        $upd->bind_param('iii', $rating, $user_id, $spot_id);
        $upd->execute();
    } else {
        $ins = $conn->prepare('INSERT INTO ratings (user_id, spot_id, rating) VALUES (?, ?, ?)');
        $ins->bind_param('iii', $user_id, $spot_id, $rating);
        $ins->execute();
    }
}
// Get average rating
$avg_res = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM ratings WHERE spot_id = $spot_id");
$avg_data = $avg_res->fetch_assoc();
$avg_rating = $avg_data['avg_rating'] ? round($avg_data['avg_rating'],1) : 'N/A';
$rating_count = $avg_data['count'];

// Handle comment
if (isset($_POST['comment']) && trim($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $ins = $conn->prepare('INSERT INTO comments (user_id, spot_id, comment) VALUES (?, ?, ?)');
    $ins->bind_param('iis', $user_id, $spot_id, $comment);
    $ins->execute();
}
// Fetch comments
$comments = [];
$com_res = $conn->query("SELECT comments.*, users.name FROM comments LEFT JOIN users ON comments.user_id = users.id WHERE spot_id = $spot_id ORDER BY created_at DESC");
while ($row = $com_res->fetch_assoc()) {
    $comments[] = $row;
}

// Handle report
if (isset($_POST['report']) && trim($_POST['report_reason'])) {
    $reason = trim($_POST['report_reason']);
    $ins = $conn->prepare('INSERT INTO reports (user_id, spot_id, reason) VALUES (?, ?, ?)');
    $ins->bind_param('iis', $user_id, $spot_id, $reason);
    $ins->execute();
    $report_msg = 'Reported. Thank you!';
}
// Handle suggest edit
if (isset($_POST['suggest_edit']) && trim($_POST['suggestion'])) {
    $suggestion = trim($_POST['suggestion']);
    $ins = $conn->prepare('INSERT INTO suggested_edits (user_id, spot_id, suggestion) VALUES (?, ?, ?)');
    $ins->bind_param('iis', $user_id, $spot_id, $suggestion);
    $ins->execute();
    $suggest_msg = 'Suggestion submitted!';
}
?>
<div class="max-w-3xl mx-auto mt-8 p-6 bg-white dark:bg-gray-800 rounded shadow">
    <div class="flex flex-col md:flex-row md:space-x-8">
        <img src="<?php echo $spot['image'] ? $spot['image'] : 'assets/images/default_spot.jpg'; ?>" alt="Spot Image" class="h-48 w-full md:w-64 object-cover rounded mb-4 md:mb-0">
        <div class="flex-1">
            <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($spot['name']); ?></h2>
            <div class="mb-2 text-gray-500"><?php echo htmlspecialchars($spot['category_name']); ?></div>
            <div class="mb-2"><?php echo htmlspecialchars($spot['description']); ?></div>
            <div class="mb-2 text-sm">Timing: <?php echo htmlspecialchars($spot['timing']); ?></div>
            <div class="mb-2 text-sm">Direction: <?php echo htmlspecialchars($spot['direction']); ?></div>
            <div class="mb-2 text-sm">Distance: <?php echo htmlspecialchars($spot['distance']); ?></div>
            <div class="mb-2 text-sm">Average Rating: <span class="font-semibold"><?php echo $avg_rating; ?></span> (<?php echo $rating_count; ?> ratings)</div>
            <form method="POST" class="inline">
                <?php if ($is_favorited): ?>
                    <button name="unfavorite" class="text-yellow-500">★ Unfavorite</button>
                <?php else: ?>
                    <button name="favorite" class="text-gray-400">☆ Favorite</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="mt-6">
        <h3 class="font-semibold mb-2">Rate this spot:</h3>
        <form method="POST" class="flex items-center space-x-2">
            <select name="rating" class="px-2 py-1 border rounded text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">
                <option value="" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">Select</option>
                <?php for ($i=1; $i<=5; $i++): ?>
                    <option value="<?php echo $i; ?>" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800"><?php echo $i; ?> ★</option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Rate</button>
        </form>
    </div>
    <div class="mt-6">
        <h3 class="font-semibold mb-2">Add a comment:</h3>
        <form method="POST" class="flex space-x-2">
            <input type="text" name="comment" placeholder="Write a comment..." class="flex-1 px-2 py-1 border rounded">
            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded">Comment</button>
        </form>
        <div class="mt-4 space-y-2">
            <?php foreach ($comments as $com): ?>
                <div class="bg-gray-100 dark:bg-gray-700 rounded p-2">
                    <span class="font-semibold"><?php echo htmlspecialchars($com['name']); ?>:</span>
                    <span><?php echo htmlspecialchars($com['comment']); ?></span>
                    <span class="text-xs text-gray-500 float-right"><?php echo $com['created_at']; ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($comments)): ?>
                <div class="text-gray-500">No comments yet.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-6">
        <h3 class="font-semibold mb-2">Report this post:</h3>
        <?php if (isset($report_msg)): ?><div class="text-green-600 mb-2"><?php echo $report_msg; ?></div><?php endif; ?>
        <form method="POST" class="flex space-x-2">
            <input type="text" name="report_reason" placeholder="Reason..." class="flex-1 px-2 py-1 border rounded">
            <button type="submit" name="report" class="bg-red-600 text-white px-3 py-1 rounded">Report</button>
        </form>
    </div>
    <div class="mt-6">
        <h3 class="font-semibold mb-2">Suggest an edit:</h3>
        <?php if (isset($suggest_msg)): ?><div class="text-green-600 mb-2"><?php echo $suggest_msg; ?></div><?php endif; ?>
        <form method="POST" class="flex space-x-2">
            <input type="text" name="suggestion" placeholder="Your suggestion..." class="flex-1 px-2 py-1 border rounded">
            <button type="submit" name="suggest_edit" class="bg-blue-600 text-white px-3 py-1 rounded">Suggest</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 