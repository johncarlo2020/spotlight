<?php
// share.php: Generates QR code for sharing the image

$imageFile = isset($_GET['img']) ? $_GET['img'] : '';
$customerName = isset($_GET['name']) ? $_GET['name'] : '';

if (!$imageFile || !file_exists('output/' . $imageFile)) {
    die('Image not found.');
}

// Create the shareable URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['REQUEST_URI']);
$shareUrl = $protocol . '://' . $host . $path . '/view.php?img=' . urlencode($imageFile) . '&name=' . urlencode($customerName);

// Generate QR code using Google Charts API (simple solution)
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($shareUrl);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Image - QR Code</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/share.css">
    <style>
        /* Animation styles */
        [data-animate] {
            opacity: 0;
        }
        
        .animate-fade-in-scale {
            animation: fadeInScale 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="logo" data-animate="fade-in-down">
        <img src="logo.png" alt="Spotlight Logo">
    </div>
    
    <div class="container" data-animate="fade-in-scale" data-delay="200">
        <h2>SHARE YOUR SPOTLIGHT</h2>
        
        <div class="customer-name" data-animate="fade-in-up" data-delay="200">
            <strong>Featuring: <?php echo htmlspecialchars($customerName); ?></strong>
        </div>
        
        <div class="qr-code" data-animate="fade-in-scale" data-delay="300">
            <img src="<?php echo $qrUrl; ?>" alt="QR Code" />
        </div>
        
        <p class="instructions" data-animate="fade-in-up" data-delay="400">Scan the QR code above or copy the link below to share your spotlight image:</p>
        
        <div class="share-url" data-animate="fade-in-up" data-delay="500">
            <input type="text" id="shareUrl" value="<?php echo htmlspecialchars($shareUrl); ?>" readonly>
        </div>
        
        <button class="copy-btn" onclick="copyToClipboard()">
            <i class="fas fa-copy"></i> Copy Link
        </button>
        <button class="close-btn" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
    
    <script src="js/animations.js"></script>
    <script src="js/share.js"></script>
</body>
</html>