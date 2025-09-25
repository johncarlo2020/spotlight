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
            max-width: 900px;
            margin: 0 auto;
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
            max-width: 250px;
            height: auto;
            filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.3));
        }
        
        h1 {
            color: #fff;
            font-size: 3rem;
            margin-bottom: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            display: none;
        }
        
        .image-container {
            margin: 30px 0;
            padding: 20px;
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
        }
        
        .spotlight-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(255, 215, 0, 0.2);
            transition: transform 0.3s ease;
        }
        
        .spotlight-image:hover {
            transform: scale(1.02);
        }
        
        .customer-info {
            margin: 25px 0;
            font-size: 1.5rem;
            color: #fff;
            padding: 15px;
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
        }
        
        .actions {
            margin: 30px 0;
        }
        
        .download-btn {
            background: #28a745;
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
            font-weight: 700;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        .download-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
            text-decoration: none;
            color: white;
        }
        
        .print-btn {
            background: #666;
            color: #fff;
            padding: 15px 35px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            margin: 10px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        
        .print-btn:hover {
            background: #888;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.1);
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #333;
            color: #ccc;
            font-size: 16px;
        }
        
        .footer p {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 10px;
            margin: 0;
            border: 1px solid #333;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 25px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .customer-info {
                font-size: 1.2rem;
            }
            
            .download-btn, .print-btn {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        <h1>IN THE SPOTLIGHT</h1>
        
        <div class="customer-info">
            <strong>Featuring: <?php echo htmlspecialchars($customerName); ?></strong>
        </div>
        
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Spotlight Image - <?php echo htmlspecialchars($customerName); ?>" class="spotlight-image" id="spotlightImage">
        </div>
        
        <div class="actions">
            <a href="<?php echo htmlspecialchars($imageUrl); ?>" download="spotlight_<?php echo htmlspecialchars($customerName); ?>.png" class="download-btn">
                Download Image
            </a>
            <button class="print-btn" onclick="printImage()">Print Image</button>
        </div>
        
        <div class="footer">
            <p>This stunning spotlight image was created using our premium Spotlight Generator</p>
        </div>
    </div>
    
    <script>
        function printImage() {
            var imgSrc = document.getElementById('spotlightImage').src;
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Spotlight Image</title></head><body style="margin:0;padding:20px;text-align:center;">');
            printWindow.document.write('<img src="' + imgSrc + '" style="max-width:100%;height:auto;" onload="window.print();window.close();">');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
    </script>
</body>
</html>