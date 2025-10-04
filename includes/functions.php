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
?> 