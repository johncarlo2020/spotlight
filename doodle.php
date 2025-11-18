<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doodle on Your Image - Spotlight</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/8.13.2/pixi.min.js" integrity="sha512-rOMqai9NIPaFWpmvHUjdOa2dSuaaYo6i7E19jS1ZW9rjnrl4qAOOtsOeTk0QgIflFCe2ZYi/7or3CRF6VfBk9g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
            right: 0;-*---------------
            padding: 15px;
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
            margin-top:30px;
        }
        
        h1 {
            display: none;
        }
        
        .subtitle {
            display: none;
        }
        
        .canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            padding: 80px 20px 100px 20px;
            pointer-events: none;
        }
        
        #pixiCanvas {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 100%;
            max-height: 100%;
            pointer-events: none;
        }
        
        #pixiCanvas canvas {
            cursor: crosshair;
            display: block;
            max-width: calc(100vw - 120px);
            max-height: calc(100vh - 180px);
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            pointer-events: auto;
            touch-action: none;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }
        
        /* Unified Bottom Controls */
        .controls-left {
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
            padding: 8px 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .control-panel label {
            display: none;
        }
        
        .color-palette {
            display: flex;
            flex-direction: row;
            gap: 10px;
            align-items: center;
        }
        
        .color-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .color-btn:hover {
            transform: scale(1.15);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.4);
        }
        
        .color-btn.active {
            border-color: #fff;
            border-width: 3px;
            transform: scale(1.1);
        }
        
        .color-btn.active::after {
            display: none;
        }
        
        /* Action Buttons Inside Control Panel */
        .button-group {
            display: flex;
            gap: 8px;
            padding-left: 15px;
            border-left: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 20px;
            padding: 8px 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .action-btn.btn-clear {
            background: linear-gradient(135deg, rgba(240, 147, 251, 0.9) 0%, rgba(245, 87, 108, 0.9) 100%);
        }
        
        .action-btn.btn-clear:hover {
            background: linear-gradient(135deg, rgba(240, 147, 251, 1) 0%, rgba(245, 87, 108, 1) 100%);
        }
        
        .action-btn.btn-process {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
        }
        
        .action-btn.btn-process:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 1) 0%, rgba(118, 75, 162, 1) 100%);
        }
        
        /* Hide the separate controls-right container */
        .controls-right {
            display: none;
        }
        
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-content {
            text-align: center;
        }
        
        .spinner {
            border: 4px solid #333;
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.98) 0%, rgba(16, 21, 62, 0.98) 100%);
            border-radius: 20px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .modal-content h3 {
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .modal-content p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 100px;
        }
        
        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .btn-confirm:hover {
            background: linear-gradient(135deg, #7c92f5 0%, #8b5cb8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .modal.show {
            display: flex;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 10px;
            }
            
            .logo img {
                max-width: 140px;
            }
            
            .canvas-container {
                padding: 60px 10px 80px 10px;
            }
            
            .controls-left {
                bottom: 80px;
            }
            
            .control-panel {
                padding: 6px 12px;
                gap: 12px;
            }
            
            .color-palette {
                gap: 8px;
            }
            
            .color-btn {
                width: 28px;
                height: 28px;
            }
            
            .button-group {
                gap: 6px;
                padding-left: 12px;
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
        <h1>Draw & Customize</h1>
        <p class="subtitle">Draw on your image - Pick a color and start doodling!</p>
    </div>
    
    <div class="canvas-container">
        <div id="pixiCanvas"></div>
    </div>
    
    <div class="controls-left">
        <div class="control-panel">
            <div class="color-palette">
                <div class="color-btn active" style="background: #ffffff;" onclick="setColor('#ffffff', event)" title="White"></div>
                <div class="color-btn" style="background: #ff0000;" onclick="setColor('#ff0000', event)" title="Red"></div>
                <div class="color-btn" style="background: #00ff00;" onclick="setColor('#00ff00', event)" title="Green"></div>
                <div class="color-btn" style="background: #0000ff;" onclick="setColor('#0000ff', event)" title="Blue"></div>
            </div>
            <div class="button-group">
                <button class="action-btn btn-clear" onclick="clearCanvas()">Clear</button>
                <button class="action-btn btn-process" onclick="processImage()">Save</button>
            </div>
        </div>
    </div>
    
    <div class="controls-right">
    </div>
    
    <div id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h2 style="color: #fff;">Processing your image...</h2>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Confirm Action</h3>
            <p id="modalMessage">Are you sure?</p>
            <div class="modal-buttons">
                <button class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="modal-btn btn-confirm" onclick="confirmAction()">Confirm</button>
            </div>
        </div>
    </div>
    
    <script>
        let app, baseTexture, baseSprite, drawingContainer, graphics;
        let isDrawing = false;
        let currentTool = 'draw';
        let currentColor = 0xFFFFFF;
        let brushSize = 10;
        let startX, startY;
        let drawingHistory = [];
        let uploadedImage = null;
        let tempGraphics = null;
        let originalImageData = null;
        let customerName = '';
        
        // Initialize PixiJS application
        async function initPixi(imageData) {
            originalImageData = imageData; // Store original for reset
            const canvasContainer = document.getElementById('pixiCanvas');
            
            // Create image element
            const img = new Image();
            img.onload = async () => {
                // Use the actual image dimensions without any scaling
                const width = img.width;
                const height = img.height;
                
                // Create Pixi app using v8 API
                app = new PIXI.Application();
                await app.init({
                    width: width,
                    height: height,
                    backgroundColor: 0x1a1a1a,
                    antialias: true
                });
                
                canvasContainer.innerHTML = '';
                canvasContainer.appendChild(app.canvas);
                
                // Create base sprite from uploaded image
                baseTexture = PIXI.Texture.from(img);
                baseSprite = new PIXI.Sprite(baseTexture);
                baseSprite.width = width;
                baseSprite.height = height;
                app.stage.addChild(baseSprite);
                
                // Create drawing container
                drawingContainer = new PIXI.Container();
                app.stage.addChild(drawingContainer);
                
                // Setup interaction
                setupInteraction();
            };
            img.src = imageData;
        }
        
        function setupInteraction() {
            const canvas = app.view;
            canvas.addEventListener('mousedown', onMouseDown);
            canvas.addEventListener('mousemove', onMouseMove);
            canvas.addEventListener('mouseup', onMouseUp);
            canvas.addEventListener('mouseleave', onMouseUp); // End drawing if mouse leaves canvas
            canvas.addEventListener('touchstart', onTouchStart, { passive: false });
            canvas.addEventListener('touchmove', onTouchMove, { passive: false });
            canvas.addEventListener('touchend', onTouchEnd);
            
            // Also listen on document for mouseup to catch events outside canvas
            document.addEventListener('mouseup', onMouseUp);
        }
        
        function onMouseDown(e) {
            if (!app || !app.canvas) return;
            isDrawing = true;
            const rect = app.canvas.getBoundingClientRect();
            const scaleX = app.renderer.width / rect.width;
            const scaleY = app.renderer.height / rect.height;
            startX = (e.clientX - rect.left) * scaleX;
            startY = (e.clientY - rect.top) * scaleY;
            
            graphics = new PIXI.Graphics();
            drawingContainer.addChild(graphics);
            graphics.lineStyle(brushSize, currentColor, 1);
            graphics.moveTo(startX, startY);
        }
        
        function onMouseMove(e) {
            if (!isDrawing || !app || !app.canvas) return;
            
            const rect = app.canvas.getBoundingClientRect();
            const scaleX = app.renderer.width / rect.width;
            const scaleY = app.renderer.height / rect.height;
            const currentX = (e.clientX - rect.left) * scaleX;
            const currentY = (e.clientY - rect.top) * scaleY;
            
            // Always draw freely
            graphics.lineTo(currentX, currentY);
            graphics.stroke(); // Force the line to be drawn
        }
        
        function onMouseUp(e) {
            if (!isDrawing) return;
            
            if (graphics) {
                graphics.stroke(); // Finalize the stroke
                drawingHistory.push(graphics);
            }
            
            isDrawing = false;
        }
        
        function onTouchStart(e) {
            e.preventDefault();
            console.log('Touch start detected');
            if (!app || !app.canvas) {
                console.log('App or canvas not ready');
                return;
            }
            const touch = e.touches[0];
            isDrawing = true;
            const rect = app.canvas.getBoundingClientRect();
            console.log('Canvas rect:', rect);
            console.log('Renderer size:', app.renderer.width, app.renderer.height);
            const scaleX = app.renderer.width / rect.width;
            const scaleY = app.renderer.height / rect.height;
            startX = (touch.clientX - rect.left) * scaleX;
            startY = (touch.clientY - rect.top) * scaleY;
            console.log('Touch position (scaled):', startX, startY);
            console.log('Touch position (raw):', touch.clientX, touch.clientY);
            
            graphics = new PIXI.Graphics();
            drawingContainer.addChild(graphics);
            graphics.lineStyle(brushSize, currentColor, 1);
            graphics.moveTo(startX, startY);
            graphics.lineTo(startX + 1, startY + 1); // Force a small line to make it visible
            console.log('Graphics added to container, children count:', drawingContainer.children.length);
            console.log('Drawing started with color:', currentColor, 'brush size:', brushSize);
        }
        
        function onTouchMove(e) {
            e.preventDefault();
            console.log('Touch move');
            if (!isDrawing || !app || !app.canvas) {
                console.log('Not drawing or app not ready');
                return;
            }
            const touch = e.touches[0];
            const rect = app.canvas.getBoundingClientRect();
            const scaleX = app.renderer.width / rect.width;
            const scaleY = app.renderer.height / rect.height;
            const currentX = (touch.clientX - rect.left) * scaleX;
            const currentY = (touch.clientY - rect.top) * scaleY;
            
            graphics.lineTo(currentX, currentY);
            graphics.stroke(); // Force the line to be drawn
        }
        
        function onTouchEnd(e) {
            e.preventDefault();
            console.log('Touch end');
            if (!isDrawing) return;
            
            if (graphics) {
                graphics.stroke(); // Finalize the stroke
                drawingHistory.push(graphics);
            }
            
            isDrawing = false;
        }
        
        function setColor(color, event) {
            currentColor = parseInt(color.replace('#', '0x'));
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            if (event && event.target) {
                event.target.classList.add('active');
            }
        }
        
        let pendingAction = null;
        
        function showModal(title, message, callback) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            pendingAction = callback;
            document.getElementById('confirmModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('show');
            pendingAction = null;
        }
        
        function confirmAction() {
            if (pendingAction) {
                pendingAction();
            }
            closeModal();
        }
        
        function clearCanvas() {
            showModal('Clear Doodles', 'Are you sure you want to clear all doodles?', () => {
                drawingContainer.removeChildren();
                drawingHistory = [];
            });
        }
        
        async function processImage() {
            if (!customerName) {
                alert('Error: Customer name is missing!');
                window.location.href = 'index.php';
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Render the canvas to a data URL
            const renderer = app.renderer;
            const canvas = renderer.extract.canvas(app.stage);
            canvas.toBlob(async (blob) => {
                // Create form data
                const formData = new FormData();
                formData.append('image', blob, 'doodled_image.png');
                formData.append('customer_name', customerName);
                formData.append('from_doodle', '1');
                
                try {
                    const response = await fetch('process.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.redirected) {
                        // Clear sessionStorage after processing
                        sessionStorage.removeItem('uploadedImage');
                        sessionStorage.removeItem('customerName');
                        // Follow the redirect to show the result
                        window.location.href = response.url;
                    } else if (response.ok) {
                        sessionStorage.removeItem('uploadedImage');
                        sessionStorage.removeItem('customerName');
                        window.location.href = 'index.php?success=1';
                    } else {
                        alert('Error processing image. Please try again.');
                        document.getElementById('loadingOverlay').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error processing image. Please try again.');
                    document.getElementById('loadingOverlay').style.display = 'none';
                }
            }, 'image/png');
        }
        
        // Load image from sessionStorage
        function loadImageFromInput() {
            const imageData = sessionStorage.getItem('uploadedImage');
            customerName = sessionStorage.getItem('customerName');
            
            if (imageData && customerName) {
                // Load from base64 data
                initPixi(imageData);
            } else {
                alert('No image selected. Redirecting to upload page...');
                window.location.href = 'index.php';
            }
        }
        
        // Initialize on page load
        window.onload = () => {
            loadImageFromInput();
        };
    </script>
</body>
</html>
