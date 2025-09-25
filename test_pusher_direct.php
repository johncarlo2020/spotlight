<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Pusher Direct Test</h2>";

// Include the config
require_once 'config.php';

echo "<h3>Configuration Check</h3>";
echo "<pre>";
print_r($pusherConfig);
echo "</pre>";

echo "<h3>Testing Direct API Call</h3>";

// Test data
$testData = [
    'filename' => 'test-image.jpg',
    'path' => 'output/test-image.jpg',
    'customer_name' => 'Test Customer',
    'timestamp' => time(),
    'formatted_date' => date('M j, Y g:i A')
];

$data = json_encode($testData);
$timestamp = time();
$auth_version = '1.0';

echo "Data to send: " . $data . "<br>";

// Prepare query parameters for signing
$query_params = [
    'auth_key' => $pusherConfig['key'],
    'auth_timestamp' => $timestamp,
    'auth_version' => $auth_version,
    'body_md5' => md5($data)
];

// Sort parameters by key
ksort($query_params);

// Build query string
$query_string = http_build_query($query_params);
echo "Query string: " . $query_string . "<br>";

// Create string to sign
$string_to_sign = "POST\n/apps/{$pusherConfig['app_id']}/events\n{$query_string}";
echo "String to sign: <pre>" . htmlspecialchars($string_to_sign) . "</pre>";

// Generate signature
$auth_signature = hash_hmac('sha256', $string_to_sign, $pusherConfig['secret']);
echo "Signature: " . $auth_signature . "<br>";

// Add signature to query params
$query_params['auth_signature'] = $auth_signature;

// Prepare the payload
$payload = [
    'name' => $pusherEvent,
    'channel' => $pusherChannel,
    'data' => $data
];

// Pusher API URL with query parameters
$url = "https://api-{$pusherConfig['cluster']}.pusherapp.com/apps/{$pusherConfig['app_id']}/events?" . http_build_query($query_params);

echo "API URL: " . $url . "<br>";
echo "Payload: <pre>" . json_encode($payload, JSON_PRETTY_PRINT) . "</pre>";

// cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "<h3>Results</h3>";
echo "HTTP Code: " . $httpCode . "<br>";
echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";

if (curl_error($ch)) {
    echo "cURL Error: " . curl_error($ch) . "<br>";
}

$info = curl_getinfo($ch);
echo "<h3>cURL Info</h3>";
echo "<pre>";
print_r($info);
echo "</pre>";

curl_close($ch);

echo "<h3>Check if classes exist</h3>";
echo "PusherHelper class exists: " . (class_exists('PusherHelper') ? 'YES' : 'NO') . "<br>";

// Try to include and test the helper
echo "<h3>Testing PusherHelper Class</h3>";
try {
    require_once 'pusher_helper.php';
    echo "PusherHelper included successfully<br>";
    
    $pusher = new PusherHelper();
    echo "PusherHelper instantiated successfully<br>";
    
    $result = $pusher->notifyNewImage($testData);
    echo "notifyNewImage result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}
?>