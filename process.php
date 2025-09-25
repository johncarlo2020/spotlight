<?php
// process.php: Handles image upload, overlays template, and adds customer name

// Set paths
$templatePath = __DIR__ . '/template/template.png';
$fontPath = __DIR__ . '/font/BodoniFLF.ttf';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['customer_name'])) {
    $customerName = $_POST['customer_name'];
    $uploadedFile = $_FILES['image']['tmp_name'];
    $uploadedType = mime_content_type($uploadedFile);

    // Load uploaded image
    switch ($uploadedType) {
        case 'image/jpeg':
            $userImg = imagecreatefromjpeg($uploadedFile);
            break;
        case 'image/png':
            $userImg = imagecreatefrompng($uploadedFile);
            break;
        case 'image/gif':
            $userImg = imagecreatefromgif($uploadedFile);
            break;
        default:
            die('Unsupported image type.');
    }

    $width = imagesx($userImg);
    $height = imagesy($userImg);

    // Load and resize template to match uploaded image
    if (!file_exists($templatePath)) {
        die('Template file not found at: ' . $templatePath);
    }
    $templateImg = imagecreatefrompng($templatePath);
    if (!$templateImg) {
        die('Failed to load template image.');
    }
    
    $resizedTemplate = imagecreatetruecolor($width, $height);
    imagealphablending($resizedTemplate, false);
    imagesavealpha($resizedTemplate, true);
    $transparent = imagecolorallocatealpha($resizedTemplate, 0, 0, 0, 127);
    imagefill($resizedTemplate, 0, 0, $transparent);
    imagecopyresampled($resizedTemplate, $templateImg, 0, 0, 0, 0, $width, $height, imagesx($templateImg), imagesy($templateImg));

    // Enable alpha blending for the main image
    imagealphablending($userImg, true);
    imagesavealpha($userImg, true);
    
    // Overlay template onto uploaded image
    imagecopy($userImg, $resizedTemplate, 0, 0, 0, 0, $width, $height);

    // Add "with [Customer Name]" at the bottom
    if (!file_exists($fontPath)) {
        die('Font file not found at: ' . $fontPath);
    }
    
    $textColor = imagecolorallocate($userImg, 255, 255, 255); // White text
    $shadowColor = imagecolorallocate($userImg, 0, 0, 0); // Black shadow
    
    // First add "with" in smaller text
    $withText = "with ";
    $withFontSize = max(10, ($width / 32) * 0.8); // Increased "with" font size and decreased overall by 20%
    
    // Then add customer name in larger text
    $customerText = strtoupper($customerName);
    $nameFontSize = max(16, ($width / 18) * 0.8); // Larger font for customer name - decreased by 20% total
    
    // Calculate total width to center both texts together
    $withBbox = imagettfbbox($withFontSize, 0, $fontPath, $withText);
    $withWidth = $withBbox[2] - $withBbox[0];
    
    $nameBbox = imagettfbbox($nameFontSize, 0, $fontPath, $customerText);
    $nameWidth = $nameBbox[2] - $nameBbox[0];
    
    $totalWidth = $withWidth + $nameWidth;
    $startX = ($width - $totalWidth) / 2;
    $textY = $height - ($height * 0.09) - 40; // Move down 3% more (from 12% to 9% from bottom)
    
    // Add "with" text with shadow
    imagettftext($userImg, $withFontSize, 0, $startX + 2, $textY + 2, $shadowColor, $fontPath, $withText);
    imagettftext($userImg, $withFontSize, 0, $startX, $textY, $textColor, $fontPath, $withText);
    
    // Add customer name text with shadow
    $nameX = $startX + $withWidth;
    imagettftext($userImg, $nameFontSize, 0, $nameX + 2, $textY + 2, $shadowColor, $fontPath, $customerText);
    imagettftext($userImg, $nameFontSize, 0, $nameX, $textY, $textColor, $fontPath, $customerText);

    // Save final image to output folder
    $outputDir = __DIR__ . '/output/';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    $outputFile = 'output_' . time() . '_' . rand(1000,9999) . '.png';
    $outputPath = $outputDir . $outputFile;
    imagepng($userImg, $outputPath);

    imagedestroy($userImg);
    imagedestroy($templateImg);
    imagedestroy($resizedTemplate);

    // Redirect back to index.php with image filename as GET parameter
    header('Location: index.php?output=' . urlencode($outputFile) . '&name=' . urlencode($customerName));
    exit;
} else {
    echo 'Invalid request.';
}
?>
