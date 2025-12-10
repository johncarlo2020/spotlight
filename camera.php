<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
            position: fixed;
            width: 100%;
            height: 100vh;
        }
        
        /* Camera View */
        #cameraView {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }
        
        .camera-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
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
            width: 100%;
            height: 100vh;
            flex-direction: column;
            background: #000;
        }
        
        .editor-canvas-container {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            touch-action: none;
        }
        
        #editorCanvas {
            max-width: 100%;
            max-height: 100%;
            display: block;
            touch-action: none;
        }
        
        .editor-toolbar {
            background: #1a1a1a;
            padding: 15px;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .toolbar-btn {
            min-width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #2a2a2a;
            border: 2px solid #3a3a3a;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            padding: 5px;
            transition: all 0.2s;
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
            padding: 15px;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        
        .sticker-panel.active {
            display: block;
        }
        
        .sticker-item {
            display: inline-block;
            width: 60px;
            height: 60px;
            margin: 5px;
            font-size: 40px;
            cursor: pointer;
            text-align: center;
            line-height: 60px;
            border-radius: 10px;
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
            padding: 15px;
            background: #1a1a1a;
        }
        
        .action-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
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
        }
        
        .placed-sticker.selected {
            transform: scale(1.1);
            filter: drop-shadow(0 0 10px rgba(76, 175, 80, 0.8));
        }
        
        .sticker-controls {
            position: absolute;
            top: -40px;
            right: -40px;
            display: none;
            gap: 5px;
            flex-direction: column;
        }
        
        .placed-sticker.selected .sticker-controls {
            display: flex;
        }
        
        .sticker-control-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f44336;
            border: 2px solid #fff;
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
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
        <video id="video" autoplay playsinline></video>
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
            
            // Set canvas size to match image
            canvas.width = capturedImage.width;
            canvas.height = capturedImage.height;
            
            drawCanvas();
            
            // Clear any existing stickers
            placedStickers.forEach(sticker => sticker.element.remove());
            placedStickers = [];
            stickers = [];
        }
        
        // Draw canvas with image and filter
        function drawCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Apply filter
            ctx.filter = getFilterStyle(currentFilter);
            ctx.drawImage(capturedImage, 0, 0, canvas.width, canvas.height);
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
            controls.innerHTML = `
                <button class="sticker-control-btn" onclick="deleteSticker(${placedStickers.length})">×</button>
            `;
            stickerElement.appendChild(controls);
            
            // Add to container
            canvasContainer.appendChild(stickerElement);
            
            // Store sticker data
            placedStickers.push({
                element: stickerElement,
                emoji: emoji,
                size: 60,
                x: parseFloat(stickerElement.style.left),
                y: parseFloat(stickerElement.style.top)
            });
            
            // Make draggable
            makeStickerDraggable(stickerElement, placedStickers.length - 1);
            
            // Select it
            selectSticker(placedStickers.length - 1);
        }
        
        // Make sticker draggable
        function makeStickerDraggable(element, index) {
            let isDragging = false;
            let startX, startY, initialX, initialY;
            
            // Pinch-to-zoom variables
            let initialDistance = 0;
            let currentScale = 1;
            
            const onStart = (e) => {
                // Handle pinch zoom
                if (e.touches && e.touches.length === 2) {
                    e.preventDefault();
                    e.stopPropagation();
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    initialDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    selectSticker(index);
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
                // Handle pinch zoom
                if (e.touches && e.touches.length === 2) {
                    e.preventDefault();
                    const touch1 = e.touches[0];
                    const touch2 = e.touches[1];
                    const currentDistance = Math.hypot(
                        touch2.clientX - touch1.clientX,
                        touch2.clientY - touch1.clientY
                    );
                    
                    if (initialDistance > 0) {
                        const scale = currentDistance / initialDistance;
                        const newSize = Math.max(20, Math.min(200, placedStickers[index].size * scale));
                        placedStickers[index].size = newSize;
                        element.style.fontSize = newSize + 'px';
                    }
                    return;
                }
                
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
                initialDistance = 0;
                
                // Update scale after pinch
                if (e.changedTouches && e.changedTouches.length > 0) {
                    // Store the final size
                    const currentSize = parseFloat(element.style.fontSize);
                    placedStickers[index].size = currentSize;
                }
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
        
        // Delete sticker
        window.deleteSticker = function(index) {
            if (placedStickers[index]) {
                placedStickers[index].element.remove();
                placedStickers.splice(index, 1);
                selectedStickerIndex = -1;
                
                // Re-index remaining stickers
                placedStickers.forEach((sticker, i) => {
                    const controls = sticker.element.querySelector('.sticker-controls');
                    if (controls) {
                        controls.innerHTML = `
                            <button class="sticker-control-btn" onclick="deleteSticker(${i})">×</button>
                        `;
                    }
                });
            }
        };
        
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
            const targetSize = 810; // Square size to fit template
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
                    // Redirect to gallery
                    window.location.href = 'gallery.php';
                } else {
                    throw new Error('Upload failed');
                }
            } catch (error) {
                console.error('Error uploading:', error);
                alert('Error uploading photo. Please try again.');
                loadingOverlay.classList.remove('active');
            }
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
