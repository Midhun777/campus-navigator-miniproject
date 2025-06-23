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
?> 