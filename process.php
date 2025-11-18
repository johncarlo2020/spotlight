<?php
// process.php: Handles image upload, overlays template, and adds customer name
error_log("=== PROCESS.PHP START ===");

// Load Composer autoloader and Pusher configuration
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Pusher\Pusher;

// Set paths
$templatePath = __DIR__ . '/template/template.png';
$fontPath = __DIR__ . '/font/Montserrat-SemiBold.ttf';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['customer_name'])) {
    $customerName = $_POST['customer_name'];
    $uploadedFile = $_FILES['image']['tmp_name'];
    $uploadedType = mime_content_type($uploadedFile);

    // Load template first to get the correct dimensions
    if (!file_exists($templatePath)) {
        die('Template file not found at: ' . $templatePath);
    }
    $templateImg = imagecreatefrompng($templatePath);
    if (!$templateImg) {
        die('Failed to load template image.');
    }
    
    // Get template dimensions (these are the target dimensions)
    $targetWidth = imagesx($templateImg);
    $targetHeight = imagesy($templateImg);
    
    // Load uploaded image
    switch ($uploadedType) {
        case 'image/jpeg':
            $uploadedImg = imagecreatefromjpeg($uploadedFile);
            break;
        case 'image/png':
            $uploadedImg = imagecreatefrompng($uploadedFile);
            break;
        case 'image/gif':
            $uploadedImg = imagecreatefromgif($uploadedFile);
            break;
        default:
            die('Unsupported image type.');
    }
    
    $uploadedWidth = imagesx($uploadedImg);
    $uploadedHeight = imagesy($uploadedImg);
    
    // Calculate aspect ratios
    $uploadedRatio = $uploadedWidth / $uploadedHeight;
    $targetRatio = $targetWidth / $targetHeight;
    
    // Resize and crop uploaded image to match template dimensions
    if ($uploadedRatio > $targetRatio) {
        // Image is wider, fit to height and crop width
        $resizeHeight = $targetHeight;
        $resizeWidth = intval($uploadedWidth * ($targetHeight / $uploadedHeight));
        $cropX = intval(($resizeWidth - $targetWidth) / 2);
        $cropY = 0;
    } else {
        // Image is taller, fit to width and crop height
        $resizeWidth = $targetWidth;
        $resizeHeight = intval($uploadedHeight * ($targetWidth / $uploadedWidth));
        $cropX = 0;
        $cropY = intval(($resizeHeight - $targetHeight) / 2);
    }
    
    // Create temporary resized image
    $resizedImg = imagecreatetruecolor($resizeWidth, $resizeHeight);
    imagecopyresampled($resizedImg, $uploadedImg, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $uploadedWidth, $uploadedHeight);
    
    // Create final image with correct dimensions
    $userImg = imagecreatetruecolor($targetWidth, $targetHeight);
    imagecopy($userImg, $resizedImg, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight);
    
    // Clean up temporary images
    imagedestroy($uploadedImg);
    imagedestroy($resizedImg);
    
    // Set final dimensions
    $width = $targetWidth;
    $height = $targetHeight;

    // Enable alpha blending for the main image
    imagealphablending($userImg, true);
    imagesavealpha($userImg, true);
    
    // Overlay template onto uploaded image (template is already the correct size)
    imagecopy($userImg, $templateImg, 0, 0, 0, 0, $width, $height);

    // Add "with [Customer Name]" at the bottom
    if (!file_exists($fontPath)) {
        die('Font file not found at: ' . $fontPath);
    }
    
    $textColor = imagecolorallocate($userImg, 255, 255, 255); // White text
    $shadowColor = imagecolorallocate($userImg, 0, 0, 0); // Black shadow

    // Add customer name (centered)
    $customerText = strtoupper($customerName);
    $nameFontSize = 46.08; // Fixed 72pt font size for customer name (reduced by 40%)
    
    // Calculate width to center the name
    $nameBbox = imagettfbbox($nameFontSize, 0, $fontPath, $customerText);
    $nameWidth = $nameBbox[2] - $nameBbox[0];
    
    $startX = ($width - $nameWidth) / 2;
    $textY = $height - ($height * 0.08) - 40; // Position 8% from bottom
    
    // Add customer name text with shadow
    imagettftext($userImg, $nameFontSize, 0, $startX + 2, $textY + 2, $shadowColor, $fontPath, $customerText);
    imagettftext($userImg, $nameFontSize, 0, $startX, $textY, $textColor, $fontPath, $customerText);

    // Save final image to output folder
    $outputDir = __DIR__ . '/output/';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    $outputFile = 'output_' . time() . '_' . rand(1000,9999) . '.png';
    $outputPath = $outputDir . $outputFile;
    imagepng($userImg, $outputPath);

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
            'customer_name' => $customerName,
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

    imagedestroy($userImg);
    imagedestroy($templateImg);

    // Redirect to preview page instead of back to index
    header('Location: preview.php?output=' . urlencode($outputFile) . '&name=' . urlencode($customerName));
    exit;
} else {
    echo 'Invalid request.';
}
?>
