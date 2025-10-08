<?php
function get_user_name() {
    return isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
}

function get_weather_kochi() {
    $apiKey = '0eb2b840d53255297795ddcca37052e5';
    $city = 'Kochi';
    $url = "https://api.openweathermap.org/data/2.5/weather?&units=metric&appid=$apiKey&q=$city";
    $response = @file_get_contents($url);
    if ($response === FALSE) return 'Weather unavailable';
    $data = json_decode($response, true);
    if (isset($data['main']['temp'])) {
        return $data['main']['temp'] . '°C, ' . $data['weather'][0]['main'];
    }
    return 'Weather unavailable';
}

/**
 * Write an audit log entry.
 *
 * @param mysqli $conn
 * @param string $action Short action name, e.g., 'login', 'spot_approve'
 * @param string|null $entityType Entity type, e.g., 'spot', 'user'
 * @param int|null $entityId Entity id
 * @param array|string|null $details Optional associative array or string with extra details
 */
function audit_log($conn, $action, $entityType = null, $entityId = null, $details = null) {
    if (!$conn) { return; }
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if (is_array($details)) {
        $details = json_encode($details);
    }
    $stmt = $conn->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    if ($stmt) {
        if ($userId === null) {
            $null = null;
            $stmt->bind_param('issis', $null, $action, $entityType, $entityId, $details);
        } else {
            $stmt->bind_param('issis', $userId, $action, $entityType, $entityId, $details);
        }
        $stmt->execute();
    }
}

/**
 * Return HTML for a category icon using an asset from assets/icons/category if available.
 * Falls back to provided icon field (emoji/text) or a default pin.
 *
 * @param string $categoryName
 * @param string|null $iconField
 * @return string HTML markup for the icon
 */
function get_category_icon_html($categoryName, $iconField = null) {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $categoryName), '-'));
    $relativeDir = 'assets/icons/category/';
    $absDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativeDir;
    $candidates = [
        $slug . '.png',
        $slug . '.svg',
    ];
    foreach ($candidates as $filename) {
        $absPath = $absDir . $filename;
        if (file_exists($absPath)) {
            $src = $relativeDir . $filename;
            return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($categoryName) . ' icon" class="object-cover" style="width:34px;height:34px" />';
        }
    }
    if (!empty($iconField)) {
        return '<span style="font-size:34px;line-height:34px;display:inline-block;">' . htmlspecialchars($iconField) . '</span>';
    }
    return '<span style="font-size:34px;line-height:34px;display:inline-block;">📍</span>';
}
?> 