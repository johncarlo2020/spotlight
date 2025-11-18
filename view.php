<?php
// view.php: Public page for viewing and downloading shared images

$imageFile = isset($_GET['img']) ? $_GET['img'] : '';
$customerName = isset($_GET['name']) ? $_GET['name'] : '';

if (!$imageFile || !file_exists('output/' . $imageFile)) {
    die('Image not found or has been removed.');
}

$imagePath = 'output/' . $imageFile;
$imageUrl = $imagePath;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotlight Image - <?php echo htmlspecialchars($customerName); ?></title>
    <link rel="stylesheet" href="css/view.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        <h1>IN THE SPOTLIGHT</h1>
        
        <div class="customer-info d-none">
            <strong>Featuring: <?php echo htmlspecialchars($customerName); ?></strong>
        </div>
        
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Spotlight Image - <?php echo htmlspecialchars($customerName); ?>" class="spotlight-image" id="spotlightImage">
        </div>
        
        <div class="actions">
            <a href="<?php echo htmlspecialchars($imageUrl); ?>" download="spotlight_<?php echo htmlspecialchars($customerName); ?>.png" class="download-btn">
                Download Image
            </a>
            <button class="print-btn d-none" onclick="printImage()">Print Image</button>
        </div>
        
        <div class="footer d-none">
            <p>This stunning spotlight image was created using our premium Spotlight Generator</p>
        </div>
    </div>
    
    <script src="js/view.js"></script>
</body>
</html>