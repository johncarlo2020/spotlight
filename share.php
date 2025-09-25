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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #000000;
            min-height: 100vh;
            padding: 20px;
            color: #ffffff;
        }
        
        .container {
            background: #111111;
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.1);
            border: 1px solid #333;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            max-width: 200px;
            height: auto;
            filter: drop-shadow(0 0 15px rgba(255, 215, 0, 0.3));
        }
        
        h2 {
            color: #fff;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: none;
        }
        
        .customer-name {
            color: #ccc;
            font-size: 1.2rem;
            margin-bottom: 30px;
            padding: 15px;
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
        }
        
        .qr-code {
            margin: 30px 0;
            padding: 20px;
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
            display: inline-block;
        }
        
        .qr-code img {
            border-radius: 8px;
        }
        
        .share-url {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            margin: 20px 0;
            border: 2px solid #333;
        }
        
        .share-url input {
            width: 100%;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 14px;
            color: #fff;
            padding: 5px;
        }
        
        .copy-btn {
            background: #666;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
            font-weight: 700;
            transition: all 0.3s ease;
            font-size: 16px;
            text-transform: uppercase;
        }
        
        .copy-btn:hover {
            background: #888;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.1);
        }
        
        .close-btn {
            background: #333;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .close-btn:hover {
            background: #555;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(85, 85, 85, 0.3);
        }
        
        .instructions {
            color: #ccc;
            margin: 20px 0;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 25px;
            }
            
            h2 {
                font-size: 1.8rem;
            }
            
            .copy-btn, .close-btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        <h2>SHARE YOUR SPOTLIGHT</h2>
        
        <div class="customer-name">
            <strong>Featuring: <?php echo htmlspecialchars($customerName); ?></strong>
        </div>
        
        <div class="qr-code">
            <img src="<?php echo $qrUrl; ?>" alt="QR Code" />
        </div>
        
        <p class="instructions">Scan the QR code above or copy the link below to share your spotlight image:</p>
        
        <div class="share-url">
            <input type="text" id="shareUrl" value="<?php echo htmlspecialchars($shareUrl); ?>" readonly>
        </div>
        
        <button class="copy-btn" onclick="copyToClipboard()">Copy Link</button>
        <button class="close-btn" onclick="window.close()">Close</button>
    </div>
    
    <script>
        function copyToClipboard() {
            const urlInput = document.getElementById('shareUrl');
            urlInput.select();
            document.execCommand('copy');
            alert('Link copied to clipboard!');
        }
    </script>
</body>
</html>