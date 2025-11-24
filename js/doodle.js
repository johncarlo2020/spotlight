// Doodle Page JavaScript
// Handles PixiJS canvas drawing and image processing

let app, baseTexture, baseSprite, drawingContainer, graphics;
let isDrawing = false;
let currentTool = 'draw';
let currentColor = 0xFFFFFF;
let brushSize = 10;
let startX, startY;
let drawingHistory = [];
let currentStroke = null;
let uploadedImage = null;
let tempGraphics = null;
let originalImageData = null;
let customerName = '';
let instructionText = null;
let hasDrawn = false;
let templateSprite = null;
let animatedLine = null;
let borderGraphics = null;
let drawingMask = null;

// Drawing area bounds (red box region)
const DRAW_AREA = {
    x: 50,
    y: 350,
    width: 1100,
    height: 1000,
    radius: 20  // Border radius for rounded corners
};

// Check if point is within drawing area
function isInDrawArea(x, y) {
    return x >= DRAW_AREA.x && 
           x <= DRAW_AREA.x + DRAW_AREA.width && 
           y >= DRAW_AREA.y && 
           y <= DRAW_AREA.y + DRAW_AREA.height;
}

// Clamp point to drawing area
function clampToDrawArea(x, y) {
    return {
        x: Math.max(DRAW_AREA.x, Math.min(DRAW_AREA.x + DRAW_AREA.width, x)),
        y: Math.max(DRAW_AREA.y, Math.min(DRAW_AREA.y + DRAW_AREA.height, y))
    };
}

// Initialize PixiJS application
async function initPixi(imageData) {
    originalImageData = imageData; // Store original for reset
    const canvasContainer = document.getElementById('pixiCanvas');
    
    // Template dimensions (2:3 aspect ratio)
    const TARGET_WIDTH = 1200;
    const TARGET_HEIGHT = 1800;
    const TARGET_RATIO = TARGET_WIDTH / TARGET_HEIGHT;
    
    // Create image element
    const img = new Image();
    img.onload = async () => {
        const uploadedWidth = img.width;
        const uploadedHeight = img.height;
        const uploadedRatio = uploadedWidth / uploadedHeight;
        
        // Calculate dimensions to match template aspect ratio
        let width, height, cropX, cropY, resizeWidth, resizeHeight;
        
        if (uploadedRatio > TARGET_RATIO) {
            // Image is wider, fit to height and crop width
            resizeHeight = TARGET_HEIGHT;
            resizeWidth = Math.round(uploadedWidth * (TARGET_HEIGHT / uploadedHeight));
            cropX = Math.round((resizeWidth - TARGET_WIDTH) / 2);
            cropY = 0;
        } else {
            // Image is taller, fit to width and crop height
            resizeWidth = TARGET_WIDTH;
            resizeHeight = Math.round(uploadedHeight * (TARGET_WIDTH / uploadedWidth));
            cropX = 0;
            cropY = Math.round((resizeHeight - TARGET_HEIGHT) / 2);
        }
        
        // Create temporary canvas for resizing and cropping
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = TARGET_WIDTH;
        tempCanvas.height = TARGET_HEIGHT;
        const tempCtx = tempCanvas.getContext('2d');
        
        // Draw resized and cropped image
        tempCtx.drawImage(
            img,
            0, 0, uploadedWidth, uploadedHeight,
            -cropX, -cropY, resizeWidth, resizeHeight
        );
        
        // Create Pixi app using v8 API with template dimensions
        app = new PIXI.Application();
        await app.init({
            width: TARGET_WIDTH,
            height: TARGET_HEIGHT,
            backgroundColor: 0x1a1a1a,
            antialias: true
        });
        
        canvasContainer.innerHTML = '';
        canvasContainer.appendChild(app.canvas);
        
        // Create base sprite from processed image
        baseTexture = PIXI.Texture.from(tempCanvas);
        baseSprite = new PIXI.Sprite(baseTexture);
        baseSprite.width = TARGET_WIDTH;
        baseSprite.height = TARGET_HEIGHT;
        app.stage.addChild(baseSprite);
        
        // Create drawing container (middle layer)
        drawingContainer = new PIXI.Container();
        // Add mask to restrict drawing to the red box area
        const mask = new PIXI.Graphics();
        mask.roundRect(DRAW_AREA.x, DRAW_AREA.y, DRAW_AREA.width, DRAW_AREA.height, DRAW_AREA.radius);
        mask.fill(0xffffff);
        drawingContainer.mask = mask;
        app.stage.addChild(drawingContainer);
        app.stage.addChild(mask); // Mask needs to be on stage
        drawingMask = mask; // Store reference
        
        // Add dashed border to show drawing area with rounded corners
        const border = new PIXI.Graphics();
        border.setStrokeStyle({
            width: 3,
            color: 0xffffff,
            alpha: 0.6
        });
        
        // Draw dashed rounded rectangle using path segments
        const dashLength = 15;
        const gapLength = 10;
        const totalDashLength = dashLength + gapLength;
        const r = DRAW_AREA.radius;
        
        // Calculate perimeter segments (excluding rounded corners)
        const topLength = DRAW_AREA.width - 2 * r;
        const rightLength = DRAW_AREA.height - 2 * r;
        const bottomLength = DRAW_AREA.width - 2 * r;
        const leftLength = DRAW_AREA.height - 2 * r;
        
        // Top edge (with dashes)
        let x = DRAW_AREA.x + r;
        const topY = DRAW_AREA.y;
        while (x < DRAW_AREA.x + DRAW_AREA.width - r) {
            border.moveTo(x, topY);
            border.lineTo(Math.min(x + dashLength, DRAW_AREA.x + DRAW_AREA.width - r), topY);
            x += totalDashLength;
        }
        
        // Top-right corner arc (dashed)
        const cornerSegments = 8;
        for (let i = 0; i < cornerSegments; i++) {
            if (i % 2 === 0) {
                const startAngle = -Math.PI / 2 + (i / cornerSegments) * (Math.PI / 2);
                const endAngle = -Math.PI / 2 + ((i + 0.5) / cornerSegments) * (Math.PI / 2);
                const centerX = DRAW_AREA.x + DRAW_AREA.width - r;
                const centerY = DRAW_AREA.y + r;
                border.arc(centerX, centerY, r, startAngle, endAngle);
            }
        }
        
        // Right edge (with dashes)
        let y = DRAW_AREA.y + r;
        const rightX = DRAW_AREA.x + DRAW_AREA.width;
        while (y < DRAW_AREA.y + DRAW_AREA.height - r) {
            border.moveTo(rightX, y);
            border.lineTo(rightX, Math.min(y + dashLength, DRAW_AREA.y + DRAW_AREA.height - r));
            y += totalDashLength;
        }
        
        // Bottom-right corner arc (dashed)
        for (let i = 0; i < cornerSegments; i++) {
            if (i % 2 === 0) {
                const startAngle = 0 + (i / cornerSegments) * (Math.PI / 2);
                const endAngle = 0 + ((i + 0.5) / cornerSegments) * (Math.PI / 2);
                const centerX = DRAW_AREA.x + DRAW_AREA.width - r;
                const centerY = DRAW_AREA.y + DRAW_AREA.height - r;
                border.arc(centerX, centerY, r, startAngle, endAngle);
            }
        }
        
        // Bottom edge (with dashes)
        x = DRAW_AREA.x + DRAW_AREA.width - r;
        const bottomY = DRAW_AREA.y + DRAW_AREA.height;
        while (x > DRAW_AREA.x + r) {
            border.moveTo(x, bottomY);
            border.lineTo(Math.max(x - dashLength, DRAW_AREA.x + r), bottomY);
            x -= totalDashLength;
        }
        
        // Bottom-left corner arc (dashed)
        for (let i = 0; i < cornerSegments; i++) {
            if (i % 2 === 0) {
                const startAngle = Math.PI / 2 + (i / cornerSegments) * (Math.PI / 2);
                const endAngle = Math.PI / 2 + ((i + 0.5) / cornerSegments) * (Math.PI / 2);
                const centerX = DRAW_AREA.x + r;
                const centerY = DRAW_AREA.y + DRAW_AREA.height - r;
                border.arc(centerX, centerY, r, startAngle, endAngle);
            }
        }
        
        // Left edge (with dashes)
        y = DRAW_AREA.y + DRAW_AREA.height - r;
        const leftX = DRAW_AREA.x;
        while (y > DRAW_AREA.y + r) {
            border.moveTo(leftX, y);
            border.lineTo(leftX, Math.max(y - dashLength, DRAW_AREA.y + r));
            y -= totalDashLength;
        }
        
        // Top-left corner arc (dashed)
        for (let i = 0; i < cornerSegments; i++) {
            if (i % 2 === 0) {
                const startAngle = Math.PI + (i / cornerSegments) * (Math.PI / 2);
                const endAngle = Math.PI + ((i + 0.5) / cornerSegments) * (Math.PI / 2);
                const centerX = DRAW_AREA.x + r;
                const centerY = DRAW_AREA.y + r;
                border.arc(centerX, centerY, r, startAngle, endAngle);
            }
        }
        
        border.stroke();
        app.stage.addChild(border);
        borderGraphics = border; // Store reference for hiding during save
        
        // Add floating instruction text at the top of the draw area
        instructionText = new PIXI.Text({
            text: '✏️ Draw Here',
            style: {
                fontFamily: 'Arial',
                fontSize: 36,
                fontWeight: 'normal',
                fill: 0xffffff,
                align: 'center',
            }
        });
        instructionText.anchor.set(0.5);
        instructionText.x = DRAW_AREA.x + DRAW_AREA.width / 2;
        instructionText.y = DRAW_AREA.y + 80; // Position near top of draw area
        instructionText.alpha = 0.8;
        app.stage.addChild(instructionText);
        
        // Add animated drawing line below the text
        animatedLine = new PIXI.Graphics();
        app.stage.addChild(animatedLine);
        
        // Animate the instruction text (floating and blinking)
        let floatDirection = 1;
        let blinkTime = 0;
        let lineProgress = 0;
        let lineDirection = 1;
        
        app.ticker.add((delta) => {
            if (instructionText && !hasDrawn) {
                // Floating animation
                instructionText.y += floatDirection * 0.2 * delta.deltaTime;
                if (instructionText.y > DRAW_AREA.y + 90) {
                    floatDirection = -1;
                } else if (instructionText.y < DRAW_AREA.y + 70) {
                    floatDirection = 1;
                }
                
                // Blinking animation
                blinkTime += delta.deltaTime;
                instructionText.alpha = 0.4 + Math.abs(Math.sin(blinkTime * 0.05)) * 0.4;
                
                // Animated drawing line
                lineProgress += lineDirection * 2 * delta.deltaTime;
                if (lineProgress > 100) {
                    lineDirection = -1;
                } else if (lineProgress < 0) {
                    lineDirection = 1;
                }
                
                // Draw wavy line animation
                animatedLine.clear();
                animatedLine.setStrokeStyle({
                    width: 3,
                    color: 0xffffff,
                    alpha: 0.4 + Math.abs(Math.sin(blinkTime * 0.05)) * 0.4
                });
                
                const lineY = DRAW_AREA.y + 120;
                const lineStartX = DRAW_AREA.x + DRAW_AREA.width / 2 - 60;
                const lineEndX = lineStartX + (lineProgress * 1.2);
                
                animatedLine.moveTo(lineStartX, lineY);
                for (let i = 0; i < lineProgress; i += 5) {
                    const x = lineStartX + (i * 1.2);
                    const y = lineY + Math.sin(i * 0.2 + blinkTime * 0.1) * 5;
                    animatedLine.lineTo(x, y);
                }
                animatedLine.stroke();
            } else if (animatedLine && hasDrawn) {
                animatedLine.visible = false;
            }
        });
        
        // Load and overlay template on top with reduced opacity
        const templateImg = new Image();
        templateImg.onload = () => {
            console.log('✅ Template loaded successfully');
            const templateTexture = PIXI.Texture.from(templateImg);
            templateSprite = new PIXI.Sprite(templateTexture);
            templateSprite.width = TARGET_WIDTH;
            templateSprite.height = TARGET_HEIGHT;
            templateSprite.alpha = 0.4; // 40% opacity for better visibility
            templateSprite.interactive = false; // Don't block mouse events
            templateSprite.eventMode = 'none'; // Pass events through
            app.stage.addChild(templateSprite);
            console.log('Template sprite added to stage');
        };
        templateImg.onerror = (error) => {
            console.error('❌ Failed to load template:', error);
        };
        templateImg.src = 'template/template.png';
        
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
    const rect = app.canvas.getBoundingClientRect();
    const scaleX = app.renderer.width / rect.width;
    const scaleY = app.renderer.height / rect.height;
    const x = (e.clientX - rect.left) * scaleX;
    const y = (e.clientY - rect.top) * scaleY;
    
    // Only start drawing if within the allowed area
    if (!isInDrawArea(x, y)) return;
    
    isDrawing = true;
    startX = x;
    startY = y;
    
    graphics = new PIXI.Graphics();
    drawingContainer.addChild(graphics);
    graphics.lineStyle(brushSize, currentColor, 1);
    graphics.moveTo(startX, startY);
    
    // Hide instruction text on first draw
    if (!hasDrawn && instructionText) {
        hasDrawn = true;
        instructionText.visible = false;
    }
}

function onMouseMove(e) {
    if (!isDrawing || !app || !app.canvas) return;
    
    const rect = app.canvas.getBoundingClientRect();
    const scaleX = app.renderer.width / rect.width;
    const scaleY = app.renderer.height / rect.height;
    let currentX = (e.clientX - rect.left) * scaleX;
    let currentY = (e.clientY - rect.top) * scaleY;
    
    // Clamp coordinates to drawing area
    const clamped = clampToDrawArea(currentX, currentY);
    currentX = clamped.x;
    currentY = clamped.y;
    
    graphics.lineTo(currentX, currentY);
    graphics.stroke(); // Force the line to be drawn
}

function onMouseUp(e) {
    if (!isDrawing) return;
    
    if (graphics) {
        graphics.stroke(); // Finalize the stroke
        drawingHistory.push(graphics);
        currentStroke = null;
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
    const rect = app.canvas.getBoundingClientRect();
    console.log('Canvas rect:', rect);
    console.log('Renderer size:', app.renderer.width, app.renderer.height);
    const scaleX = app.renderer.width / rect.width;
    const scaleY = app.renderer.height / rect.height;
    const x = (touch.clientX - rect.left) * scaleX;
    const y = (touch.clientY - rect.top) * scaleY;
    
    // Only start drawing if within the allowed area
    if (!isInDrawArea(x, y)) return;
    
    isDrawing = true;
    startX = x;
    startY = y;
    console.log('Touch position (scaled):', startX, startY);
    console.log('Touch position (raw):', touch.clientX, touch.clientY);
    
    graphics = new PIXI.Graphics();
    drawingContainer.addChild(graphics);
    graphics.lineStyle(brushSize, currentColor, 1);
    graphics.moveTo(startX, startY);
    graphics.lineTo(startX + 1, startY + 1); // Force a small line to make it visible
    console.log('Graphics added to container, children count:', drawingContainer.children.length);
    console.log('Drawing started with color:', currentColor, 'brush size:', brushSize);
    
    // Hide instruction text on first draw
    if (!hasDrawn && instructionText) {
        hasDrawn = true;
        instructionText.visible = false;
    }
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
    let currentX = (touch.clientX - rect.left) * scaleX;
    let currentY = (touch.clientY - rect.top) * scaleY;
    
    // Clamp coordinates to drawing area
    const clamped = clampToDrawArea(currentX, currentY);
    currentX = clamped.x;
    currentY = clamped.y;
    
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

function undoLastStroke() {
    if (drawingHistory.length > 0) {
        const lastStroke = drawingHistory.pop();
        drawingContainer.removeChild(lastStroke);
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
    
    // Temporarily hide UI elements before rendering
    const templateWasVisible = templateSprite && templateSprite.visible;
    const borderWasVisible = borderGraphics && borderGraphics.visible;
    const textWasVisible = instructionText && instructionText.visible;
    const lineWasVisible = animatedLine && animatedLine.visible;
    const maskWasVisible = drawingMask && drawingMask.visible;
    
    if (templateSprite) templateSprite.visible = false;
    if (borderGraphics) borderGraphics.visible = false;
    if (instructionText) instructionText.visible = false;
    if (animatedLine) animatedLine.visible = false;
    if (drawingMask) drawingMask.visible = false;
    
    // Render the canvas to a data URL
    const renderer = app.renderer;
    const canvas = renderer.extract.canvas(app.stage);
    
    // Restore UI elements visibility
    if (templateSprite && templateWasVisible) templateSprite.visible = true;
    if (borderGraphics && borderWasVisible) borderGraphics.visible = true;
    if (instructionText && textWasVisible) instructionText.visible = true;
    if (animatedLine && lineWasVisible) animatedLine.visible = true;
    if (drawingMask && maskWasVisible) drawingMask.visible = true;
    
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
