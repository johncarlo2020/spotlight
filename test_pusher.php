<?php
// test_pusher.php: Simple test to verify Pusher configuration
require_once 'pusher_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pusher = new PusherHelper();
        
        // Test data
        $testData = [
            'filename' => 'test_image.png',
            'path' => 'output/test_image.png',
            'customer_name' => 'Test Customer',
            'timestamp' => time(),
            'formatted_date' => date('M j, Y g:i A'),
            'test' => true
        ];
        
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $result = $pusher->notifyNewImage($testData);
        
        // Check PHP error log for additional info
        $errorLogPath = ini_get('error_log');
        $recentErrors = [];
        if ($errorLogPath && file_exists($errorLogPath)) {
            $errorLines = file($errorLogPath);
            $recentErrors = array_slice($errorLines, -10); // Get last 10 lines
        }
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Pusher test notification sent successfully!',
                'recent_errors' => $recentErrors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send Pusher notification. Check your configuration and recent errors below.',
                'recent_errors' => $recentErrors
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
} else {
    // Show test form
    $pusher = new PusherHelper();
    $config = $pusher->getClientConfig();
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Pusher Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; }
            .container { max-width: 600px; margin: 0 auto; }
            button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
            button:hover { background: #0056b3; }
            .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
            .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Pusher Configuration Test</h1>';
            
    if ($config) {
        echo '<div class="result info">
                <strong>✅ Pusher is configured!</strong><br>
                Key: ' . $config['key'] . '<br>
                Cluster: ' . $config['cluster'] . '<br>
                Channel: ' . $config['channel'] . '<br>
                Event: ' . $config['event'] . '
              </div>
              <br>
              <button onclick="testPusher()">Send Test Notification</button>';
    } else {
        echo '<div class="result error">
                <strong>❌ Pusher is not configured</strong><br>
                Please update your credentials in config.php
              </div>';
    }
    
    echo '
            <div id="testResult"></div>
        </div>
        
        <script>
        async function testPusher() {
            const button = event.target;
            button.disabled = true;
            button.textContent = "Testing...";
            
            try {
                const response = await fetch("test_pusher.php", {
                    method: "POST"
                });
                const result = await response.json();
                
                const resultDiv = document.getElementById("testResult");
                resultDiv.className = "result " + (result.success ? "success" : "error");
                
                let message = "<strong>" + (result.success ? "✅" : "❌") + " " + result.message + "</strong>";
                
                // Show recent errors if available
                if (result.recent_errors && result.recent_errors.length > 0) {
                    message += "<br><br><strong>Recent Error Log:</strong><br>";
                    message += "<pre style='font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
                    message += result.recent_errors.join('').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    message += "</pre>";
                }
                
                // Show stack trace if available
                if (result.trace) {
                    message += "<br><br><strong>Error Trace:</strong><br>";
                    message += "<pre style='font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
                    message += result.trace.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    message += "</pre>";
                }
                
                resultDiv.innerHTML = message;
                
            } catch (error) {
                const resultDiv = document.getElementById("testResult");
                resultDiv.className = "result error";
                resultDiv.innerHTML = "<strong>❌ JavaScript Error: " + error.message + "</strong>";
            }
            
            button.disabled = false;
            button.textContent = "Send Test Notification";
        }
        </script>
    </body>
    </html>';
}
?>