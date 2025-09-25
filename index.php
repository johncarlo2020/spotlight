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
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.1);
            border: 1px solid #333;
        }
        
        h2 {
            color: #FFD700;
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5), 0 0 20px rgba(255, 215, 0, 0.3), 0 0 30px rgba(255, 215, 0, 0.1);
        }
        
        .subtitle {
            text-align: center;
            color: #ccc;
            margin-bottom: 40px;
            font-size: 1.1rem;
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
            border: 2px solid #333;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #000;
            color: #fff;
        }
        
        input[type="text"]:focus, input[type="file"]:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }
        
        input[type="submit"] {
            background: #FFD700;
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
            background: #FFA500;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }
        
        .print-btn {
            background: #28a745;
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
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .share-btn {
            background: #007bff;
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
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
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
            color: #FFD700;
            margin-bottom: 20px;
            font-size: 1.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .result img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(255, 215, 0, 0.2);
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
        <h2>SPOTLIGHT GENERATOR</h2>
        <p class="subtitle">Create stunning spotlight images with custom overlays</p>
    <?php
    $outputImg = isset($_GET['output']) ? $_GET['output'] : '';
    $customerName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
    ?>
        <form action="process.php" method="post" enctype="multipart/form-data">
            <label for="customer_name">Customer Name:</label>
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