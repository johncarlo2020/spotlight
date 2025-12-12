<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Spotlight Camera</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #000;
            color: #fff;
            overflow: hidden;
            width: 100%;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        /* Camera View */
        #cameraView {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #000;
        }
        
        .camera-video-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            overflow: hidden;
        }
        
        #video {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            background: #000;
            max-height: calc(100vh - 150px);
        }
        
        .camera-controls {
            padding: 20px;
            padding-bottom: max(60px, env(safe-area-inset-bottom));
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            min-height: 120px;
            flex-shrink: 0;
        }
        
        .capture-btn {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #fff;
            border: 5px solid rgba(255,255,255,0.3);
            cursor: pointer;
            transition: transform 0.1s;
            box-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }
        
        .capture-btn:active {
            transform: scale(0.95);
        }
        
        .switch-camera-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Editor View */
        #editorView {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            flex-direction: column;
            background: #000;
            overflow: hidden;
        }
        
        .editor-canvas-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
            touch-action: none;
            background: #000;
            flex: 1;
            max-height: calc(100vh - 250px);
        }
        
        #editorCanvas {
            width: 100%;
            aspect-ratio: 1 / 1;
            max-width: 100%;
            max-height: 100%;
            display: block;
            touch-action: none;
            object-fit: contain;
        }
        
        .editor-toolbar {
            background: #1a1a1a;
            padding: 8px 10px;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            flex-shrink: 0;
            height: 80px;
        }
        
        .toolbar-btn {
            min-width: 55px;
            height: 55px;
            border-radius: 10px;
            background: #2a2a2a;
            border: 2px solid #3a3a3a;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            padding: 4px;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .toolbar-btn span {
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .toolbar-btn:active {
            transform: scale(0.95);
            background: #3a3a3a;
        }
        
        .toolbar-btn.active {
            background: #4CAF50;
            border-color: #4CAF50;
        }
        
        /* Sticker Panel */
        .sticker-panel {
            display: none;
            background: #2a2a2a;
            padding: 8px 10px;
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            flex-shrink: 0;
            height: 80px;
        }
        
        .sticker-panel.active {
            display: block;
        }
        
        .sticker-item {
            display: inline-block;
            width: 55px;
            height: 55px;
            margin: 3px;
            font-size: 35px;
            cursor: pointer;
            text-align: center;
            line-height: 55px;
            border-radius: 8px;
            background: #3a3a3a;
            transition: transform 0.2s;
        }
        
        .sticker-item:active {
            transform: scale(1.2);
        }
        
        /* Filter Panel */
        .filter-panel {
            display: none;
            background: #2a2a2a;
            padding: 15px;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        
        .filter-panel.active {
            display: block;
        }
        
        .filter-item {
            display: inline-block;
            width: 80px;
            margin: 5px;
            cursor: pointer;
            text-align: center;
        }
        
        .filter-preview {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            border: 3px solid transparent;
            margin-bottom: 5px;
            transition: border-color 0.2s;
        }
        
        .filter-item.active .filter-preview {
            border-color: #4CAF50;
        }
        
        .filter-name {
            font-size: 11px;
            color: #ccc;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            padding: 12px 15px;
            padding-bottom: 15px;
            background: #1a1a1a;
            flex-shrink: 0;
            height: 65px;
        }
        
        .action-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-cancel {
            background: #555;
            color: #fff;
        }
        
        .btn-done {
            background: #4CAF50;
            color: #fff;
        }
        
        .action-btn:active {
            transform: scale(0.97);
        }
        
        /* Success View */
        #successView {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #000;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9998;
        }
        
        .success-content {
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .success-title {
            font-size: 28px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        
        .success-message {
            font-size: 16px;
            color: #ccc;
            margin-bottom: 30px;
        }
        
        .success-preview {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            margin: 0 auto 30px;
            box-shadow: 0 8px 30px rgba(76, 175, 80, 0.3);
        }
        
        .success-buttons {
            display: flex;
            gap: 15px;
            width: 100%;
        }
        
        .success-btn {
            flex: 1;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-new-photo {
            background: #4CAF50;
            color: #fff;
        }
        
        .btn-view-gallery {
            background: #2196F3;
            color: #fff;
        }
        
        .success-btn:active {
            transform: scale(0.97);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: #4CAF50;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 18px;
            color: #fff;
        }
        
        /* Header */
        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), transparent);
            z-index: 10;
            text-align: center;
        }
        
        .logo-small {
            height: 40px;
        }
        
        /* Draggable Stickers */
        .placed-sticker {
            position: absolute;
            cursor: move;
            touch-action: none;
            user-select: none;
            font-size: 60px;
            z-index: 100;
            transition: transform 0.1s;
            padding: 10px;
        }
        
        .placed-sticker.selected {
            transform: scale(1.1);
            border: 3px solid #4CAF50;
            border-radius: 8px;
            background: rgba(76, 175, 80, 0.1);
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
        }
        
        .placed-sticker.selected::after {
            content: '⇲';
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 25px;
            height: 25px;
            background: #4CAF50;
            border: 2px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .sticker-controls {
            position: absolute;
            top: -45px;
            right: -10px;
            display: none;
            gap: 5px;
            flex-direction: row;
        }
        
        .placed-sticker.selected .sticker-controls {
            display: flex;
        }
        
        .sticker-control-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid #fff;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            pointer-events: auto;
            touch-action: auto;
            z-index: 1000;
        }
        
        .sticker-btn-delete {
            background: #f44336;
        }
        
        .sticker-btn-increase {
            background: #4CAF50;
        }
        
        .sticker-btn-decrease {
            background: #FF9800;
        }
        
        .sticker-control-btn:active {
            transform: scale(0.9);
        }
    </style>
</head>
<body>
    <!-- Camera View -->
    <div id="cameraView">
        <div class="header">
            <img src="logo.png" alt="Spotlight" class="logo-small">
        </div>
        <div class="camera-video-container">
            <video id="video" autoplay playsinline></video>
        </div>
        <div class="camera-controls">
            <button class="switch-camera-btn" id="switchCamera">🔄</button>
            <button class="capture-btn" id="captureBtn"></button>
        </div>
    </div>
    
    <!-- Editor View -->
    <div id="editorView">
        <div class="editor-canvas-container" id="canvasContainer">
            <canvas id="editorCanvas"></canvas>
        </div>
        
        <div class="sticker-panel" id="stickerPanel">
            <!-- Emojis/Stickers -->
            <div class="sticker-item" data-sticker="😀">😀</div>
            <div class="sticker-item" data-sticker="😎">😎</div>
            <div class="sticker-item" data-sticker="🥳">🥳</div>
            <div class="sticker-item" data-sticker="😍">😍</div>
            <div class="sticker-item" data-sticker="🤩">🤩</div>
            <div class="sticker-item" data-sticker="🔥">🔥</div>
            <div class="sticker-item" data-sticker="⭐">⭐</div>
            <div class="sticker-item" data-sticker="❤️">❤️</div>
            <div class="sticker-item" data-sticker="💯">💯</div>
            <div class="sticker-item" data-sticker="✨">✨</div>
            <div class="sticker-item" data-sticker="🎉">🎉</div>
            <div class="sticker-item" data-sticker="🎊">🎊</div>
            <div class="sticker-item" data-sticker="🏆">🏆</div>
            <div class="sticker-item" data-sticker="👑">👑</div>
            <div class="sticker-item" data-sticker="🌟">🌟</div>
            <div class="sticker-item" data-sticker="💪">💪</div>
            <div class="sticker-item" data-sticker="👍">👍</div>
            <div class="sticker-item" data-sticker="🙌">🙌</div>
        </div>
        
        <div class="filter-panel" id="filterPanel">
            <div class="filter-item active" data-filter="none">
                <div class="filter-preview" style="background: #888;"></div>
                <div class="filter-name">Original</div>
            </div>
            <div class="filter-item" data-filter="grayscale">
                <div class="filter-preview" style="background: #666;"></div>
                <div class="filter-name">B&W</div>
            </div>
            <div class="filter-item" data-filter="sepia">
                <div class="filter-preview" style="background: #a0826d;"></div>
                <div class="filter-name">Sepia</div>
            </div>
            <div class="filter-item" data-filter="saturate">
                <div class="filter-preview" style="background: linear-gradient(45deg, #ff6b6b, #4ecdc4);"></div>
                <div class="filter-name">Vibrant</div>
            </div>
            <div class="filter-item" data-filter="brightness">
                <div class="filter-preview" style="background: #bbb;"></div>
                <div class="filter-name">Bright</div>
            </div>
            <div class="filter-item" data-filter="contrast">
                <div class="filter-preview" style="background: linear-gradient(to right, #000, #fff);"></div>
                <div class="filter-name">Contrast</div>
            </div>
        </div>
        
        <div class="editor-toolbar">
            <button class="toolbar-btn" id="stickerBtn">
                😀
                <span>Stickers</span>
            </button>
            <button class="toolbar-btn" id="filterBtn">
                🎨
                <span>Filters</span>
            </button>
            <button class="toolbar-btn" id="undoBtn">
                ↶
                <span>Undo</span>
            </button>
        </div>
        
        <div class="action-buttons">
            <button class="action-btn btn-cancel" id="cancelBtn">Cancel</button>
            <button class="action-btn btn-done" id="doneBtn">✓ Done</button>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Processing your photo...</div>
    </div>
    
    <!-- Success View -->
    <div id="successView">
        <div class="success-content">
            <div class="success-title">✓ Photo Saved!</div>
            <div class="success-message">Your photo has been processed successfully</div>
            <img id="successPreview" class="success-preview" alt="Processed photo">
            <div class="success-buttons">
                <button class="success-btn btn-new-photo" id="newPhotoBtn">📷 New Photo</button>
                <button class="success-btn btn-view-gallery" id="viewGalleryBtn">🖼️ View Gallery</button>
            </div>
        </div>
    </div>

    <script>
        // Camera functionality
        let stream = null;
        let currentCamera = 'environment'; // Start with back camera
        let capturedImage = null;
        let canvas = null;
        let ctx = null;
        let stickers = [];
        let currentFilter = 'none';
        let selectedSticker = null;
        
        const video = document.getElementById('video');
        const cameraView = document.getElementById('cameraView');
        const editorView = document.getElementById('editorView');
        const editorCanvas = document.getElementById('editorCanvas');
        const captureBtn = document.getElementById('captureBtn');
        const switchCameraBtn = document.getElementById('switchCamera');
        const stickerBtn = document.getElementById('stickerBtn');
        const filterBtn = document.getElementById('filterBtn');
        const stickerPanel = document.getElementById('stickerPanel');
        const filterPanel = document.getElementById('filterPanel');
        const cancelBtn = document.getElementById('cancelBtn');
        const doneBtn = document.getElementById('doneBtn');
        const undoBtn = document.getElementById('undoBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const canvasContainer = document.getElementById('canvasContainer');
        
        let placedStickers = []; // Track placed sticker DOM elements
        let selectedStickerIndex = -1;
        
        // Initialize camera
        async function initCamera() {
            try {
                console.log('Initializing camera...');
                
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
                
                // Check if getUserMedia is supported
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Camera API not supported in this browser');
                }
                
                const constraints = {
                    video: {
                        facingMode: currentCamera,
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    },
                    audio: false
                };
                
                console.log('Requesting camera access with constraints:', constraints);
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = stream;
                
                // Wait for video to be ready
                await new Promise((resolve) => {
                    video.onloadedmetadata = () => {
                        video.play();
                        console.log('Camera ready');
                        resolve();
                    };
                });
                
            } catch (error) {
                console.error('Error accessing camera:', error);
                let errorMessage = 'Unable to access camera. ';
                
                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    errorMessage += 'Please allow camera permissions in your browser settings.';
                } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                    errorMessage += 'No camera found on this device.';
                } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                    errorMessage += 'Camera is already in use by another application.';
                } else {
                    errorMessage += error.message;
                }
                
                alert(errorMessage);
            }
        }
        
        // Switch camera
        switchCameraBtn.addEventListener('click', async () => {
            currentCamera = currentCamera === 'user' ? 'environment' : 'user';
            await initCamera();
        });
        
        // Capture photo
        captureBtn.addEventListener('click', () => {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = video.videoWidth;
            tempCanvas.height = video.videoHeight;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(video, 0, 0);
            
            capturedImage = new Image();
            capturedImage.onload = () => {
                showEditor();
            };
            capturedImage.src = tempCanvas.toDataURL('image/jpeg', 0.95);
            
            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
        
        // Show editor
        function showEditor() {
            cameraView.style.display = 'none';
            editorView.style.display = 'flex';
            
            canvas = editorCanvas;
            ctx = canvas.getContext('2d');
            
            // Set canvas to square (1:1 ratio) using the smaller dimension
            const size = Math.min(capturedImage.width, capturedImage.height);
            canvas.width = size;
            canvas.height = size;
            
            drawCanvas();
            
            // Clear any existing stickers
            placedStickers.forEach(sticker => sticker.element.remove());
            placedStickers = [];
            stickers = [];
        }
        
        // Draw canvas with image and filter
        function drawCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Calculate crop for center square
            const size = Math.min(capturedImage.width, capturedImage.height);
            const sourceX = (capturedImage.width - size) / 2;
            const sourceY = (capturedImage.height - size) / 2;
            
            // Apply filter and draw cropped square image
            ctx.filter = getFilterStyle(currentFilter);
            ctx.drawImage(
                capturedImage,
                sourceX, sourceY, size, size,  // Source: center square crop
                0, 0, canvas.width, canvas.height  // Destination: full canvas
            );
            ctx.filter = 'none';
        }
        
        // Get filter CSS
        function getFilterStyle(filter) {
            switch(filter) {
                case 'grayscale': return 'grayscale(100%)';
                case 'sepia': return 'sepia(100%)';
                case 'saturate': return 'saturate(200%)';
                case 'brightness': return 'brightness(120%)';
                case 'contrast': return 'contrast(130%)';
                default: return 'none';
            }
        }
        
        // Toggle sticker panel
        stickerBtn.addEventListener('click', () => {
            stickerPanel.classList.toggle('active');
            filterPanel.classList.remove('active');
            stickerBtn.classList.toggle('active');
            filterBtn.classList.remove('active');
        });
        
        // Toggle filter panel
        filterBtn.addEventListener('click', () => {
            filterPanel.classList.toggle('active');
            stickerPanel.classList.remove('active');
            filterBtn.classList.toggle('active');
            stickerBtn.classList.remove('active');
        });
        
        // Add sticker
        document.querySelectorAll('.sticker-item').forEach(item => {
            item.addEventListener('click', () => {
                const emoji = item.dataset.sticker;
                addStickerToCanvas(emoji);
            });
        });
        
        // Add sticker as draggable DOM element
        function addStickerToCanvas(emoji) {
            const stickerElement = document.createElement('div');
            stickerElement.className = 'placed-sticker';
            stickerElement.textContent = emoji;
            stickerElement.style.fontSize = '60px';
            
            // Position in center of canvas
            const rect = canvas.getBoundingClientRect();
            stickerElement.style.left = (rect.width / 2 - 30) + 'px';
            stickerElement.style.top = (rect.height / 2 - 30) + 'px';
            
            // Add controls
            const controls = document.createElement('div');
            controls.className = 'sticker-controls';
            
            const decreaseBtn = document.createElement('button');
            decreaseBtn.className = 'sticker-control-btn sticker-btn-decrease';
            decreaseBtn.textContent = '−';
            
            const increaseBtn = document.createElement('button');
            increaseBtn.className = 'sticker-control-btn sticker-btn-increase';
            increaseBtn.textContent = '+';
            
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'sticker-control-btn sticker-btn-delete';
            deleteBtn.textContent = '×';
            
            controls.appendChild(decreaseBtn);
            controls.appendChild(increaseBtn);
            controls.appendChild(deleteBtn);
            stickerElement.appendChild(controls);
            
            // Add to container
            canvasContainer.appendChild(stickerElement);
            
            // Store sticker data
            const stickerIndex = placedStickers.length;
            placedStickers.push({
                element: stickerElement,
                emoji: emoji,
                size: 60,
                x: parseFloat(stickerElement.style.left),
                y: parseFloat(stickerElement.style.top)
            });
            
            // Add button event listeners
            decreaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                resizeSticker(stickerIndex, -10);
            });
            decreaseBtn.addEventListener('touchstart', (e) => {
                e.stopPropagation();
            });
            decreaseBtn.addEventListener('touchend', (e) => {
                e.stopPropagation();
                e.preventDefault();
                resizeSticker(stickerIndex, -10);
            });
            
            increaseBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                resizeSticker(stickerIndex, 10);
            });
            increaseBtn.addEventListener('touchstart', (e) => {
                e.stopPropagation();
            });
            increaseBtn.addEventListener('touchend', (e) => {
                e.stopPropagation();
                e.preventDefault();
                resizeSticker(stickerIndex, 10);
            });
            
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                deleteSticker(stickerIndex);
            });
            deleteBtn.addEventListener('touchstart', (e) => {
                e.stopPropagation();
            });
            deleteBtn.addEventListener('touchend', (e) => {
                e.stopPropagation();
                e.preventDefault();
                deleteSticker(stickerIndex);
            });
            
            // Make draggable
            makeStickerDraggable(stickerElement, stickerIndex);
            
            // Select it
            selectSticker(stickerIndex);
        }
        
        // Make sticker draggable
        function makeStickerDraggable(element, index) {
            let isDragging = false;
            let startX, startY, initialX, initialY;
            
            const onStart = (e) => {
                // Don't drag if clicking on a button
                if (e.target.classList.contains('sticker-control-btn')) {
                    return;
                }
                
                // Only handle single touch or mouse
                if (e.touches && e.touches.length > 1) {
                    return;
                }
                
                // Handle drag
                if (e.touches && e.touches.length === 1 || e.type === 'mousedown') {
                    e.preventDefault();
                    e.stopPropagation();
                    isDragging = true;
                    
                    const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                    const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
                    
                    startX = clientX;
                    startY = clientY;
                    initialX = parseFloat(element.style.left) || 0;
                    initialY = parseFloat(element.style.top) || 0;
                    
                    selectSticker(index);
                }
            };
            
            const onMove = (e) => {
                // Handle drag
                if (!isDragging) return;
                e.preventDefault();
                
                const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
                
                const deltaX = clientX - startX;
                const deltaY = clientY - startY;
                
                const newX = initialX + deltaX;
                const newY = initialY + deltaY;
                
                element.style.left = newX + 'px';
                element.style.top = newY + 'px';
                
                placedStickers[index].x = newX;
                placedStickers[index].y = newY;
            };
            
            const onEnd = (e) => {
                isDragging = false;
            };
            
            element.addEventListener('mousedown', onStart);
            element.addEventListener('touchstart', onStart, { passive: false });
            document.addEventListener('mousemove', onMove);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('mouseup', onEnd);
            document.addEventListener('touchend', onEnd);
        }
        
        // Select sticker
        function selectSticker(index) {
            placedStickers.forEach((s, i) => {
                s.element.classList.toggle('selected', i === index);
            });
            selectedStickerIndex = index;
        }
        
        // Resize sticker
        function resizeSticker(index, delta) {
            if (placedStickers[index]) {
                const currentSize = placedStickers[index].size;
                const newSize = Math.max(20, Math.min(200, currentSize + delta));
                placedStickers[index].size = newSize;
                placedStickers[index].element.style.fontSize = newSize + 'px';
            }
        }
        
        // Delete sticker
        function deleteSticker(index) {
            if (placedStickers[index]) {
                placedStickers[index].element.remove();
                placedStickers.splice(index, 1);
                selectedStickerIndex = -1;
                
                // Re-index remaining stickers and update buttons
                placedStickers.forEach((sticker, i) => {
                    const controls = sticker.element.querySelector('.sticker-controls');
                    if (controls) {
                        // Clear old buttons
                        controls.innerHTML = '';
                        
                        // Create new buttons
                        const decreaseBtn = document.createElement('button');
                        decreaseBtn.className = 'sticker-control-btn sticker-btn-decrease';
                        decreaseBtn.textContent = '−';
                        decreaseBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            resizeSticker(i, -10);
                        });
                        decreaseBtn.addEventListener('touchstart', (e) => {
                            e.stopPropagation();
                        });
                        decreaseBtn.addEventListener('touchend', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            resizeSticker(i, -10);
                        });
                        
                        const increaseBtn = document.createElement('button');
                        increaseBtn.className = 'sticker-control-btn sticker-btn-increase';
                        increaseBtn.textContent = '+';
                        increaseBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            resizeSticker(i, 10);
                        });
                        increaseBtn.addEventListener('touchstart', (e) => {
                            e.stopPropagation();
                        });
                        increaseBtn.addEventListener('touchend', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            resizeSticker(i, 10);
                        });
                        
                        const deleteBtn = document.createElement('button');
                        deleteBtn.className = 'sticker-control-btn sticker-btn-delete';
                        deleteBtn.textContent = '×';
                        deleteBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            deleteSticker(i);
                        });
                        deleteBtn.addEventListener('touchstart', (e) => {
                            e.stopPropagation();
                        });
                        deleteBtn.addEventListener('touchend', (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            deleteSticker(i);
                        });
                        
                        controls.appendChild(decreaseBtn);
                        controls.appendChild(increaseBtn);
                        controls.appendChild(deleteBtn);
                    }
                });
            }
        }
        
        // Apply filter
        document.querySelectorAll('.filter-item').forEach(item => {
            item.addEventListener('click', () => {
                document.querySelectorAll('.filter-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                currentFilter = item.dataset.filter;
                drawCanvas();
            });
        });
        
        // Undo last sticker
        undoBtn.addEventListener('click', () => {
            if (placedStickers.length > 0) {
                const lastSticker = placedStickers.pop();
                lastSticker.element.remove();
                selectedStickerIndex = -1;
            }
        });
        
        // Cancel and go back to camera
        cancelBtn.addEventListener('click', async () => {
            editorView.style.display = 'none';
            cameraView.style.display = 'flex';
            placedStickers.forEach(sticker => sticker.element.remove());
            placedStickers = [];
            stickers = [];
            currentFilter = 'none';
            await initCamera();
        });
        
        // Done - process and upload
        doneBtn.addEventListener('click', async () => {
            loadingOverlay.classList.add('active');
            
            console.log('Starting image processing...');
            console.log('Placed stickers:', placedStickers);
            
            // Create a temporary canvas to merge everything
            const finalCanvas = document.createElement('canvas');
            const targetSize = 820; // Square size 820x820
            finalCanvas.width = targetSize;
            finalCanvas.height = targetSize;
            const finalCtx = finalCanvas.getContext('2d');
            
            // Fill with black background
            finalCtx.fillStyle = '#000000';
            finalCtx.fillRect(0, 0, targetSize, targetSize);
            
            // Calculate aspect ratio and crop to square
            const sourceSize = Math.min(capturedImage.width, capturedImage.height);
            const sourceX = (capturedImage.width - sourceSize) / 2;
            const sourceY = (capturedImage.height - sourceSize) / 2;
            
            // Draw the cropped and filtered image as square
            finalCtx.filter = getFilterStyle(currentFilter);
            finalCtx.drawImage(
                capturedImage,
                sourceX, sourceY, sourceSize, sourceSize, // Source crop to square
                0, 0, targetSize, targetSize // Destination square
            );
            finalCtx.filter = 'none';
            
            console.log('Image drawn, now drawing stickers...');
            
            // Calculate scale factors for stickers
            const rect = canvas.getBoundingClientRect();
            const displayWidth = rect.width;
            const displayHeight = rect.height;
            
            // Scale factor from display size to final canvas size
            const scaleX = targetSize / displayWidth;
            const scaleY = targetSize / displayHeight;
            
            console.log('Canvas rect:', rect);
            console.log('Scale factors:', scaleX, scaleY);
            
            // Draw all stickers on the final canvas
            placedStickers.forEach((sticker, index) => {
                const x = sticker.x * scaleX;
                const y = sticker.y * scaleY;
                const fontSize = sticker.size * Math.min(scaleX, scaleY);
                
                console.log(`Drawing sticker ${index}:`, {
                    emoji: sticker.emoji,
                    displayPos: { x: sticker.x, y: sticker.y },
                    canvasPos: { x, y },
                    displaySize: sticker.size,
                    canvasSize: fontSize
                });
                
                finalCtx.font = `${fontSize}px Arial`;
                finalCtx.textBaseline = 'top';
                finalCtx.fillStyle = '#000000'; // Draw black first for testing
                finalCtx.fillText(sticker.emoji, x + 2, y + 2); // Shadow
                finalCtx.fillStyle = '#FFFFFF'; // Then white
                finalCtx.fillText(sticker.emoji, x, y);
            });
            
            console.log('All stickers drawn, creating blob...');
            
            // Get final image
            const finalImageDataURL = finalCanvas.toDataURL('image/png', 1.0);
            
            // Convert to blob
            const response = await fetch(finalImageDataURL);
            const blob = await response.blob();
            
            // Upload to server
            const formData = new FormData();
            formData.append('image', blob, 'photo.jpg');
            
            try {
                const uploadResponse = await fetch('process.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (uploadResponse.ok) {
                    // Hide loading and show success view
                    loadingOverlay.classList.remove('active');
                    
                    // Show preview in success view
                    const successPreview = document.getElementById('successPreview');
                    successPreview.src = finalImageDataURL;
                    
                    // Hide editor and show success
                    editorView.style.display = 'none';
                    document.getElementById('successView').style.display = 'flex';
                } else {
                    throw new Error('Upload failed');
                }
            } catch (error) {
                console.error('Error uploading:', error);
                alert('Error uploading photo. Please try again.');
                loadingOverlay.classList.remove('active');
            }
        });
        
        // New Photo button - refresh to camera
        document.getElementById('newPhotoBtn').addEventListener('click', async () => {
            document.getElementById('successView').style.display = 'none';
            cameraView.style.display = 'flex';
            placedStickers.forEach(sticker => sticker.element.remove());
            placedStickers = [];
            stickers = [];
            currentFilter = 'none';
            await initCamera();
        });
        
        // View Gallery button
        document.getElementById('viewGalleryBtn').addEventListener('click', () => {
            window.location.href = 'gallery.php';
        });
        
        // Check for HTTPS or localhost
        function checkSecureContext() {
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                alert('Camera requires HTTPS or localhost. Please access this page via HTTPS or localhost.');
                return false;
            }
            return true;
        }
        
        // Initialize camera on load
        if (checkSecureContext()) {
            initCamera();
        }
    </script>
</body>
</html>
