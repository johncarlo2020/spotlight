// Doodle Page JavaScript
// Handles PixiJS canvas drawing and image processing

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
