<?php
// debug_logs.php - Show recent PHP error logs to debug Pusher issues
header('Content-Type: text/html; charset=utf-8');

function getTailOfFile($file, $lines = 50) {
    if (!file_exists($file)) {
        return "Log file not found: " . $file;
    }
    
    $handle = fopen($file, "r");
    if (!$handle) {
        return "Cannot read log file: " . $file;
    }
    
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if (fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true;
                break;
            }
            $t = fgetc($handle);
            $pos--;
        }
        $linecounter--;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines - $linecounter - 1] = fgets($handle);
        if ($beginning) break;
    }
    fclose($handle);
    
    return implode("", array_reverse($text));
}

// Try different possible log locations
$possibleLogFiles = [
    ini_get('error_log'),
    __DIR__ . '/error.log',
    __DIR__ . '/../logs/error.log',
    $_SERVER['DOCUMENT_ROOT'] . '/error.log',
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/php/logs/php_error_log'
];

$logContent = null;
$logFile = null;

foreach ($possibleLogFiles as $file) {
    if ($file && file_exists($file)) {
        $logFile = $file;
        $logContent = getTailOfFile($file, 100);
        break;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Pusher Debug Logs</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 20px;
            background: #1a1a1a;
            color: #ffffff;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .log-container {
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 8px;
            padding: 20px;
            white-space: pre-wrap;
            font-size: 12px;
            line-height: 1.4;
            max-height: 600px;
            overflow-y: auto;
        }
        .refresh-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .refresh-btn:hover {
            background: #0056b3;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .highlight {
            background: #ffff99;
            color: #000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Pusher Debug Logs</h1>
        
        <?php if ($logFile): ?>
            <div class="info">
                <strong>📁 Reading from:</strong> <?php echo htmlspecialchars($logFile); ?>
            </div>
            
            <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Logs</button>
            
            <div class="log-container"><?php 
                $highlighted = str_replace(
                    ['Pusher', '✅', '❌', '🚨', '🔧', '📤', '🌐', '📦', '📡', '📄'],
                    ['<span class="highlight">Pusher</span>', '<span class="highlight">✅</span>', '<span class="highlight">❌</span>', '<span class="highlight">🚨</span>', '<span class="highlight">🔧</span>', '<span class="highlight">📤</span>', '<span class="highlight">🌐</span>', '<span class="highlight">📦</span>', '<span class="highlight">📡</span>', '<span class="highlight">📄</span>'],
                    htmlspecialchars($logContent)
                );
                echo $highlighted;
            ?></div>
            
        <?php else: ?>
            <div class="error">
                <strong>❌ No log file found</strong><br>
                Searched in:<br>
                <?php foreach ($possibleLogFiles as $file): ?>
                    - <?php echo htmlspecialchars($file ? $file : 'null'); ?><br>
                <?php endforeach; ?>
                <br>
                <strong>To enable logging:</strong><br>
                1. Make sure PHP error logging is enabled<br>
                2. Check your php.ini for log_errors and error_log settings<br>
                3. Try uploading an image first to generate logs
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>💡 How to use:</strong><br>
            1. Open this page<br>
            2. Upload an image in another tab/window<br>
            3. Refresh this page to see new logs<br>
            4. Look for Pusher-related messages (highlighted)
        </div>
        
        <div class="info">
            <strong>🎯 What to look for:</strong><br>
            • <span class="highlight">🔧 Pusher configured</span> - Shows your app_id and cluster<br>
            • <span class="highlight">📤 Preparing to send data</span> - Shows the image data being sent<br>
            • <span class="highlight">🌐 Pusher API URL</span> - The URL being called<br>
            • <span class="highlight">📡 HTTP Response Code</span> - Should be 200 for success<br>
            • <span class="highlight">✅ Pusher notification sent successfully</span> - Success!<br>
            • <span class="highlight">❌</span> or <span class="highlight">🚨</span> - Indicates errors
        </div>
    </div>
</body>
</html>