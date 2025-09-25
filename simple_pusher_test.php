<?php
// Simple Pusher API test without classes
require_once 'config.php';

echo "<h2>Basic Pusher Test</h2>";

// Check if cURL is enabled
if (!function_exists('curl_init')) {
    echo "<p style='color: red;'>ERROR: cURL is not enabled!</p>";
    exit;
}

echo "<p style='color: green;'>cURL is enabled</p>";

// Check configuration
if (empty($pusherConfig) || empty($pusherConfig['app_id'])) {
    echo "<p style='color: red;'>ERROR: Pusher configuration is missing!</p>";
    exit;
}

echo "<p style='color: green;'>Pusher configuration found</p>";

// Prepare simple test data
$testData = ['test' => 'message', 'timestamp' => time()];
$data = json_encode($testData);

echo "<p>Test data: " . $data . "</p>";

// Build the API request
$timestamp = time();
$body_md5 = md5($data);

// Query parameters (must be sorted)
$params = [
    'auth_key' => $pusherConfig['key'],
    'auth_timestamp' => $timestamp,
    'auth_version' => '1.0',
    'body_md5' => $body_md5
];

ksort($params);
$query_string = http_build_query($params);

// String to sign
$path = "/apps/{$pusherConfig['app_id']}/events";
$string_to_sign = "POST\n{$path}\n{$query_string}";

// Generate signature
$signature = hash_hmac('sha256', $string_to_sign, $pusherConfig['secret']);
$params['auth_signature'] = $signature;

// Final URL
$url = "https://api-{$pusherConfig['cluster']}.pusherapp.com{$path}?" . http_build_query($params);

// Payload
$payload = json_encode([
    'name' => $pusherEvent,
    'channel' => $pusherChannel,
    'data' => $data
]);

echo "<p>URL: " . $url . "</p>";
echo "<p>Payload: " . $payload . "</p>";

// Make the request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false, // For testing only
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<h3>Results:</h3>";
echo "<p>HTTP Code: <strong>" . $http_code . "</strong></p>";
echo "<p>Response: <code>" . htmlspecialchars($response) . "</code></p>";

if ($curl_error) {
    echo "<p style='color: red;'>cURL Error: " . $curl_error . "</p>";
}

if ($http_code == 200) {
    echo "<p style='color: green;'>✅ SUCCESS! Pusher notification sent!</p>";
} else {
    echo "<p style='color: red;'>❌ FAILED! HTTP " . $http_code . "</p>";
}
?>