<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image and Customer Name</title>
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
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo img {
            max-width: 300px;
            height: auto;
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
        }
        
        .subtitle {
            text-align: center;
            color: #ccc;
            margin-bottom: 40px;
            font-size: 1.1rem;
            display: none;
        }
        
        h2 {
            display: none;
        }
        
        form {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #333;
        }
        
        label {
            display: block;
            margin: 20px 0 8px 0;
            font-weight: 600;
            color: #fff;
            font-size: 1.1rem;
        }
        
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #444;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #222;
            color: #fff;
        }
        
        input[type="text"]:focus, input[type="file"]:focus {
            outline: none;
            border-color: #666;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }
        
        input[type="submit"] {
            background: #fff;
            color: #000;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        input[type="submit"]:hover {
            background: #ddd;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
        }
        
        .print-btn {
            background: #666666;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .print-btn:hover {
            background: #555;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 102, 102, 0.3);
        }
        
        .share-btn {
            background: #888888;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .share-btn:hover {
            background: #777;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(136, 136, 136, 0.3);
        }
        
        .result {
            text-align: center;
            margin-top: 40px;
            padding: 30px;
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
        }
        
        .result h3 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 1.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .result img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.1);
            margin: 20px 0;
            transition: transform 0.3s ease;
        }
        
        .result img:hover {
            transform: scale(1.02);
        }
        
        .result p {
            font-size: 1.2rem;
            color: #ccc;
            margin: 15px 0;
        }
        
        .button-group {
            margin-top: 25px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 25px;
            }
            
            h2 {
                font-size: 2rem;
            }
            
            .print-btn, .share-btn {
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
        
        <h2>SPOTLIGHT GENERATOR</h2>
        <p class="subtitle">Create stunning spotlight images with custom overlays</p>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="gallery.php" style="background: #666; color: #fff; padding: 10px 20px; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.background='#555'; this.style.color='#fff';" onmouseout="this.style.background='#666'; this.style.color='#fff';">📸 View Gallery</a>
        </div>
    <?php
    $outputImg = isset($_GET['output']) ? $_GET['output'] : '';
    $customerName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
    ?>
        <form action="process.php" method="post" enctype="multipart/form-data">
            <label for="customer_name">Name:</label>
            <input type="text" id="customer_name" name="customer_name" required value="<?php echo $customerName; ?>">
            
            <label for="image">Select image to upload:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            
            <input type="submit" value="Upload and Process">
        </form>
        
        <?php if ($outputImg): ?>
            <div class="result">
                <h3>Processed Image:</h3>
                <img id="processedImage" src="output/<?php echo htmlspecialchars($outputImg); ?>" alt="Processed Image">
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($customerName); ?></p>
                <div class="button-group">
                    <button class="print-btn" onclick="printImage()">🖨️ Print Image</button>
                    <button class="share-btn" onclick="shareImage('<?php echo htmlspecialchars($outputImg); ?>', '<?php echo htmlspecialchars($customerName); ?>')">📱 Share Image</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function printImage() {
            var imgSrc = document.getElementById('processedImage').src;
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print Image</title></head><body style="margin:0;padding:20px;text-align:center;">');
            printWindow.document.write('<img src="' + imgSrc + '" style="max-width:100%;height:auto;" onload="window.print();window.close();">');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
        
        function shareImage(filename, customerName) {
            window.open('share.php?img=' + encodeURIComponent(filename) + '&name=' + encodeURIComponent(customerName), '_blank');
        }
    </script>
</body>
</html>