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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            padding: 20px;
            color: #ffffff;
            overflow-x: hidden;
            max-width: 100vw;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
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
        
        .container {
            background: rgba(26, 26, 46, 0.4);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.1),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            width: 100%;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
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
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
            font-size: 1.1rem;
            display: block;
            font-weight: 400;
            letter-spacing: 0.5px;
        }
        
        h2 {
            text-align: center;
            color: #fff;
            font-size: 2.8rem;
            margin-bottom: 15px;
            font-weight: 800;
            letter-spacing: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        form {
            background: rgba(30, 30, 50, 0.3);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 16px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1),
                        0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        label {
            display: block;
            margin: 20px 0 12px 0;
            font-weight: 600;
            color: #fff;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }
        
        label::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 16px 20px;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-weight: 500;
        }
        
        input[type="file"] {
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            cursor: pointer;
            text-align: center;
            position: relative;
        }
        
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 15px;
        }
        
        input[type="file"]::file-selector-button:hover {
            background: linear-gradient(135deg, #7c92f5 0%, #8b5cb8 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        input[type="text"]:focus, input[type="file"]:focus {
            outline: none;
            border-color: rgba(120, 119, 198, 0.8);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(120, 119, 198, 0.15);
            transform: translateY(-2px);
        }
        
        input[type="file"]:hover {
            border-color: rgba(120, 119, 198, 0.5);
            background: rgba(255, 255, 255, 0.1);
        }
        
        input[type="submit"], input[type="button"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 18px 40px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 17px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        input[type="submit"]::before, input[type="button"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        input[type="submit"]:hover::before, input[type="button"]:hover::before {
            left: 100%;
        }
        
        input[type="submit"]:hover, input[type="button"]:hover {
            background: linear-gradient(135deg, #7c92f5 0%, #8b5cb8 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
        }
        
        input[type="submit"]:active, input[type="button"]:active {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
            body {
                padding: 5px;
            }
            
            .container {
                margin: 5px;
                padding: 15px;
            }
            
            .logo img {
                max-width: 200px;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            form {
                padding: 15px;
            }
            
            label {
                font-size: 1rem;
                margin: 15px 0 6px 0;
            }
            
            input[type="text"], input[type="file"] {
                padding: 12px;
                font-size: 14px;
            }
            
            input[type="submit"], input[type="button"] {
                padding: 12px 30px;
                font-size: 16px;
            }
            
            /* Make form buttons stack on mobile */
            #continueBtn {
                width: 100%;
            }
            
            .result {
                padding: 15px;
                margin-top: 20px;
            }
            
            .result h3 {
                font-size: 1.4rem;
            }
            
            .result p {
                font-size: 1rem;
            }
            
            .print-btn, .share-btn {
                width: 100%;
                margin: 5px 0;
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .button-group {
                margin-top: 15px;
            }
            
            /* Make button group responsive */
            div[style*="text-align: center"] {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            div[style*="text-align: center"] a,
            div[style*="text-align: center"] button {
                width: 100%;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="gallery.php" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%); color: #fff; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-block; border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);" onmouseover="this.style.background='linear-gradient(135deg, rgba(120, 119, 198, 0.3) 0%, rgba(120, 119, 198, 0.2) 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(120, 119, 198, 0.3)';" onmouseout="this.style.background='linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">📸 View Gallery</a>
        </div>
        
        <form id="uploadForm">
            <label for="customer_name">Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
            
            <label for="image">Select image to upload:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            
            <input type="button" id="continueBtn" value="Upload and Continue" onclick="goToDoodle()">
        </form>
    </div>
    
    <script>
        function goToDoodle() {
            const nameInput = document.getElementById('customer_name');
            const imageInput = document.getElementById('image');
            
            const customerName = nameInput.value.trim();
            const file = imageInput.files[0];
            
            if (!customerName) {
                alert('Please enter a customer name!');
                nameInput.focus();
                return;
            }
            
            if (!file) {
                alert('Please select an image!');
                imageInput.click();
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                // Store data in sessionStorage instead of URL
                sessionStorage.setItem('uploadedImage', event.target.result);
                sessionStorage.setItem('customerName', customerName);
                window.location.href = 'doodle.php';
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>