<?php
// process.php: Handles image upload, overlays template, and adds customer name
error_log("=== PROCESS.PHP START ===");

// Load Composer autoloader and Pusher configuration
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Pusher\Pusher;

// Set paths
$templatePath = __DIR__ . '/template/template.png';
$fontPath = __DIR__ . '/font/Linotype - DidotLTPro-Headline.otf';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadedFile = $_FILES['image']['tmp_name'];
    $uploadedType = mime_content_type($uploadedFile);

    // Load template first to use its dimensions
    if (!file_exists($templatePath)) {
        die('Template file not found at: ' . $templatePath);
    }
    $templateImg = imagecreatefrompng($templatePath);
    if (!$templateImg) {
        die('Failed to load template image.');
    }
    
    $templateWidth = imagesx($templateImg);
    $templateHeight = imagesy($templateImg);
    
    // Load uploaded image - accept any image format
    $userImg = @imagecreatefromstring(file_get_contents($uploadedFile));
    
    if (!$userImg) {
        die('Failed to load uploaded image. Please ensure the file is a valid image format.');
    }
    
    $uploadedWidth = imagesx($userImg);
    $uploadedHeight = imagesy($userImg);
    
    // Crop uploaded image to square (center crop) to prevent stretching
    $squareSize = min($uploadedWidth, $uploadedHeight);
    $cropX = ($uploadedWidth - $squareSize) / 2;
    $cropY = ($uploadedHeight - $squareSize) / 2;
    
    $squareImg = imagecreatetruecolor($squareSize, $squareSize);
    imagecopy($squareImg, $userImg, 0, 0, $cropX, $cropY, $squareSize, $squareSize);
    imagedestroy($userImg);
    
    // Create final canvas with template dimensions
    $finalImg = imagecreatetruecolor($templateWidth, $templateHeight);
    imagealphablending($finalImg, true);
    imagesavealpha($finalImg, true);
    
    // Fill with black background
    $black = imagecolorallocate($finalImg, 0, 0, 0);
    imagefill($finalImg, 0, 0, $black);
    
    // Define transparent box positions (left and right)
    // Template dimensions: 1800x1200
    // Left box: starts at x=48, y=66, size 810x820
    // Right box: starts at x=945, y=66, size 810x820
    
    $boxWidth = 810;
    $boxHeight = 820;
    $leftBoxX = 48;
    $rightBoxX = 945;
    $boxY = 73;
    
    // Resize square image to fit box dimensions (820x820) - no stretching!
    $resizedSquare = imagecreatetruecolor($boxWidth, $boxHeight);
    imagecopyresampled($resizedSquare, $squareImg, 0, 0, 0, 0, $boxWidth, $boxHeight, $squareSize, $squareSize);
    imagedestroy($squareImg);
    
    // Place resized square image in LEFT transparent area
    imagecopy($finalImg, $resizedSquare, $leftBoxX, $boxY, 0, 0, $boxWidth, $boxHeight);
    
    // Place resized square image in RIGHT transparent area
    imagecopy($finalImg, $resizedSquare, $rightBoxX, $boxY, 0, 0, $boxWidth, $boxHeight);
    
    // Clean up resized image
    imagedestroy($resizedSquare);
    
    // Overlay template on top to show the design elements
    imagecopy($finalImg, $templateImg, 0, 0, 0, 0, $templateWidth, $templateHeight);
    
    // Clean up template
    imagedestroy($templateImg);

    // Save final image to output folder (no text overlay needed)
    $outputDir = __DIR__ . '/output/';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    $outputFile = 'output_' . time() . '_' . rand(1000,9999) . '.png';
    $outputPath = $outputDir . $outputFile;
    imagepng($finalImg, $outputPath);
    
    // Clean up final image
    imagedestroy($finalImg);

    // Send Pusher notification for real-time gallery update
    error_log("=== PUSHER DEBUG START ===");
    error_log("Starting Pusher notification for file: " . $outputFile);
    
    try {
        error_log("Creating Pusher instance with official SDK");
        
        // Create Pusher instance using official SDK
        $pusher = new Pusher(
            $pusherConfig['key'],
            $pusherConfig['secret'],
            $pusherConfig['app_id'],
            [
                'cluster' => $pusherConfig['cluster'],
                'useTLS' => $pusherConfig['use_tls']
            ]
        );
        
        error_log("Pusher instance created successfully");
        
        $imageData = [
            'filename' => $outputFile,
            'path' => 'output/' . $outputFile,
            'timestamp' => time(),
            'formatted_date' => date('M j, Y g:i A')
        ];
        error_log("Image data prepared: " . json_encode($imageData));
        
        error_log("About to trigger Pusher event: " . $pusherEvent . " on channel: " . $pusherChannel);
        
        // Send the notification using official SDK
        $result = $pusher->trigger($pusherChannel, $pusherEvent, $imageData);
        
        error_log("Pusher trigger response: " . json_encode($result));
        
        if ($result) {
            error_log("✅ Pusher notification sent successfully for: " . $outputFile);
        } else {
            error_log("❌ Pusher notification failed for: " . $outputFile);
        }
        
    } catch (Exception $e) {
        error_log("🚨 Exception in Pusher notification: " . $e->getMessage());
        error_log("Exception file: " . $e->getFile() . " line " . $e->getLine());
        error_log("Stack trace: " . $e->getTraceAsString());
    } catch (Error $e) {
        error_log("🚨 Fatal error in Pusher notification: " . $e->getMessage());
        error_log("Error file: " . $e->getFile() . " line " . $e->getLine());
        error_log("Stack trace: " . $e->getTraceAsString());
    }
    error_log("=== PUSHER DEBUG END ===");

    // Redirect back to index.php with image filename as GET parameter
    header('Location: index.php?output=' . urlencode($outputFile));
    exit;
} else {
    echo 'Invalid request.';
}
?>
