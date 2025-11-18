<?php
// preview.php: Preview the processed image with actions
$outputImg = isset($_GET['output']) ? $_GET['output'] : '';
$customerName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';

if (!$outputImg || !file_exists('output/' . $outputImg)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Spotlight</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            height: 100vh;
            overflow: hidden;
            color: #ffffff;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(252, 163, 17, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 30px 15px 20px 15px;
            text-align: center;
            z-index: 100;
            background: linear-gradient(180deg, rgba(10, 10, 10, 0.9) 0%, rgba(10, 10, 10, 0) 100%);
        }
        
        .logo {
            margin-bottom: 0;
        }
        
        .logo img {
            max-width: 180px;
            height: auto;
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
        }
        
        h1 {
            display: none;
        }
        
        .subtitle {
            display: none;
        }
        
        .image-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            padding: 110px 20px 150px 20px;
        }
        
        .image-container img {
            max-width: calc(100vw - 120px);
            max-height: calc(100vh - 230px);
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        
        .controls-bottom {
            position: fixed;
            left: 50%;
            bottom: 74px;
            transform: translateX(-50%);
            z-index: 200;
        }
        
        .control-panel {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 30px;
            padding: 16px 36px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            text-decoration: none;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-download {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.9) 0%, rgba(56, 239, 125, 0.9) 100%);
        }
        
        .btn-download:hover {
            background: linear-gradient(135deg, rgba(17, 153, 142, 1) 0%, rgba(56, 239, 125, 1) 100%);
        }
        
        .btn-share {
            background: linear-gradient(135deg, rgba(79, 172, 254, 0.9) 0%, rgba(0, 242, 254, 0.9) 100%);
        }
        
        .btn-share:hover {
            background: linear-gradient(135deg, rgba(79, 172, 254, 1) 0%, rgba(0, 242, 254, 1) 100%);
        }
        
        .btn-create {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
        }
        
        .btn-create:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 1) 0%, rgba(118, 75, 162, 1) 100%);
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 10px;
            }
            
            .logo img {
                max-width: 140px;
            }
            
            .image-container {
                padding: 60px 10px 130px 10px;
            }
            
            .controls-bottom {
                bottom: 10px;
            }
            
            .control-panel {
                padding: 6px 12px;
                gap: 6px;
            }
            
            .action-btn {
                padding: 6px 14px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
    </div>
    
    <div class="image-container">
        <img src="output/<?php echo htmlspecialchars($outputImg); ?>" alt="Processed Image" id="previewImage">
    </div>
    
    <div class="controls-bottom">
        <div class="control-panel">
            <button class="action-btn btn-download" onclick="downloadImage()">Download</button>
            <button class="action-btn btn-share" onclick="shareImage()">Share</button>
            <a href="index.php" class="action-btn btn-create">Create New</a>
        </div>
    </div>
    
    <script>
        const filename = '<?php echo htmlspecialchars($outputImg); ?>';
        const customerName = '<?php echo htmlspecialchars($customerName); ?>';
        
        function downloadImage() {
            const link = document.createElement('a');
            link.href = 'output/' + filename;
            link.download = 'spotlight_' + customerName.replace(/\s+/g, '_') + '.png';
            link.click();
        }
        
        function shareImage() {
            window.open('share.php?img=' + encodeURIComponent(filename) + '&name=' + encodeURIComponent(customerName), '_blank', 'width=600,height=700');
        }
    </script>
</body>
</html>
