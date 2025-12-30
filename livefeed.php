<?php
require_once 'config.php';
require_once 'pusher_helper.php';

// Initialize Pusher helper for client configuration
$pusherHelper = new PusherHelper();
$pusherClientConfig = $pusherHelper->getClientConfig();

// Get all images from output folder
$outputDir = __DIR__ . '/output/';
$images = [];

if (is_dir($outputDir)) {
    $files = scandir($outputDir);
    foreach ($files as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif'])) {
            $images[] = [
                'filename' => $file,
                'timestamp' => filemtime($outputDir . $file),
                'path' => 'output/' . $file
            ];
        }
    }
}

// Sort by timestamp (newest first)
usort($images, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Get the 15 latest images
$latestImages = array_slice($images, 0, 15);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Feed - Spotlight</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('asset/Background.webp');
            background-size: cover;
            background-position: center;
            overflow: hidden;
            width: 1080px;
            height: 1920px;
            position: relative;
        }
        
        .header {
            position: absolute;
            top: 40px;
            left: 0;
            width: 100%;
            height: 180px;
            background-image: url('asset/Header.webp');
            background-size: contain;
            background-position: center top;
            background-repeat: no-repeat;
            z-index: 100;
        }
        
        .animated-decorations {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .decoration {
            position: absolute;
            width: 120px;
            height: 120px;
        }
        
        .lantern {
            width: 100px;
            height: 140px;
        }
        
        .firework {
            width: 200px;
            height: 200px;
        }
        
        /* Decorations positioning */
        .decoration.lantern { 
            top: 50px; 
            right: 100px; 
        }
        
        .decoration.flower { 
            top: 130px; 
            left: 80px; 
        }
        
        .decoration.firework { 
            top: 450px; 
            right: 120px; 
        }
        
        #pixiCanvas {
            position: absolute;
            top: 240px;
            left: 0;
            z-index: 10;
        }
        
        #pixiCanvas canvas {
            display: block;
        }
    </style>
</head>
<body>

<div class="header"></div>

<div class="animated-decorations">
    <img src="asset/lantern.gif" class="decoration lantern" alt="">
    <img src="asset/flower.gif" class="decoration flower" alt="">
    <img src="asset/firework.gif" class="decoration firework" alt="">
</div>

<div id="pixiCanvas"></div>

<script src="https://cdn.jsdelivr.net/npm/pixi.js@7.3.2/dist/pixi.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.4/dist/gsap.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
// Initial images data
const initialImages = <?php echo json_encode($latestImages, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
console.log('Initial images loaded:', initialImages);

// Maximum 18 images in the gallery (6 per carousel: 5 showing + 1 buffer)
const maxImages = 18;
const maxPerCarousel = 6;

// Carousel state - 3 carousels with 6 images each
// Global image queue - all images flow through this queue
let imageQueue = [];

const carousels = [
    { images: [], sprites: [], container: null, direction: -1 }, // Move left
    { images: [], sprites: [], container: null, direction: 1 },  // Move right
    { images: [], sprites: [], container: null, direction: -1 }  // Move left
];

let nextCarouselIndex = 0;
let app = null;
const CARD_WIDTH = 240;
const CARD_HEIGHT = 360;
const CARD_GAP = -40; // Negative gap for overlap
const CARD_RADIUS = 15;

// Initialize global image queue and distribute to carousels
function distributeImages(images) {
    if (!images || images.length === 0) return;
    
    // Add all images to global queue
    imageQueue = [...images];
    
    // If we have less than 18 images, duplicate them to fill
    while (imageQueue.length < 18) {
        imageQueue = [...imageQueue, ...images].slice(0, 18);
    }
    
    console.log(`📦 Global queue initialized with ${imageQueue.length} images`);
    
    // Assign first 18 images to carousels (6 each)
    for (let i = 0; i < 3; i++) {
        const startIdx = i * 6;
        carousels[i].images = imageQueue.slice(startIdx, startIdx + 6);
    }
    
    console.log(`📊 Distribution: Carousel 0: ${carousels[0].images.length}, Carousel 1: ${carousels[1].images.length}, Carousel 2: ${carousels[2].images.length}`);
}

// Initialize PixiJS Application
function initPixiApp() {
    console.log('🎨 Initializing PixiJS...');
    
    app = new PIXI.Application({
        width: 1080,
        height: 1680,
        backgroundColor: 0x000000,
        backgroundAlpha: 0,
        antialias: true,
        resolution: window.devicePixelRatio || 1,
        autoDensity: true
    });
    
    const canvasContainer = document.getElementById('pixiCanvas');
    canvasContainer.appendChild(app.view);
    console.log('✅ PixiJS canvas added to DOM');
    
    // Create containers for each carousel
    const rowHeight = 1680 / 3;
    carousels.forEach((carousel, index) => {
        carousel.container = new PIXI.Container();
        carousel.container.y = index * rowHeight + rowHeight / 2;
        carousel.container.sortableChildren = true; // Enable z-index sorting
        app.stage.addChild(carousel.container);
    });
    
    console.log('✅ Created 3 carousel containers');
}

// Create rounded rectangle mask for card
function createRoundedRectTexture(width, height, radius) {
    const graphics = new PIXI.Graphics();
    graphics.beginFill(0xFFFFFF);
    graphics.drawRoundedRect(0, 0, width, height, radius);
    graphics.endFill();
    return app.renderer.generateTexture(graphics);
}

// Create a photo sprite with effects
async function createPhotoSprite(imagePath, carouselIndex) {
    return new Promise((resolve, reject) => {
        console.log('📸 Loading image:', imagePath);
        
        const texture = PIXI.Texture.from(imagePath);
        
        const onLoaded = () => {
            console.log('✅ Image loaded:', imagePath);
            
            const sprite = new PIXI.Sprite(texture);
            // Use left-center anchor to match CSS object-position: left center
            sprite.anchor.set(0, 0.5);
            
            // Calculate aspect ratio and scale to cover card area
            const imgRatio = texture.width / texture.height;
            const cardRatio = CARD_WIDTH / CARD_HEIGHT;
            
            if (imgRatio > cardRatio) {
                // Image is wider - fit height and crop width (from left side)
                sprite.height = CARD_HEIGHT;
                sprite.width = CARD_HEIGHT * imgRatio;
            } else {
                // Image is taller - fit width and crop height (centered vertically)
                sprite.width = CARD_WIDTH;
                sprite.height = CARD_WIDTH / imgRatio;
            }
            
            // Position sprite at left edge of card
            sprite.x = -CARD_WIDTH / 2;
            sprite.y = 0;
            
            // Create container for effects
            const container = new PIXI.Container();
            container.addChild(sprite);
            
            // Add rounded corners mask
            const mask = new PIXI.Graphics();
            mask.beginFill(0xFFFFFF);
            mask.drawRoundedRect(-CARD_WIDTH / 2, -CARD_HEIGHT / 2, CARD_WIDTH, CARD_HEIGHT, CARD_RADIUS);
            mask.endFill();
            container.addChild(mask);
            sprite.mask = mask;
            
            container.userData = { imagePath, carouselIndex };
            resolve(container);
        };
        
        const onError = (err) => {
            console.error('❌ Failed to load image:', imagePath, err);
            reject(new Error('Failed to load: ' + imagePath));
        };
        
        if (texture.baseTexture.valid) {
            onLoaded();
        } else {
            texture.baseTexture.once('loaded', onLoaded);
            texture.baseTexture.once('error', onError);
        }
    });
}

// Get scale and alpha based on position for symmetric effect (5 visible + 1 buffer off-screen)
function getPositionProperties(index, total, direction = -1) {
    // Handle buffer position for 6-sprite system
    if (total === 6 && index === 5) {
        // Buffer position (off-screen, ready to animate in)
        if (direction === -1) {
            // Moving left, buffer waits on the right
            return { scale: 0.8, alpha: 0, offScreenOffset: 250 };
        } else {
            // Moving right, buffer waits on the left
            return { scale: 0.8, alpha: 0, offScreenOffset: -250 };
        }
    }
    
    const positions = [
        { scale: 0.8, alpha: 0.7, offScreenOffset: 0 },     // far-left (index 0) - reduced opacity
        { scale: 0.9, alpha: 1.0, offScreenOffset: 0 },     // left (index 1) - full opacity
        { scale: 1.15, alpha: 1.0, offScreenOffset: 0 },    // CENTER (biggest) - index 2 - full opacity
        { scale: 0.9, alpha: 1.0, offScreenOffset: 0 },     // right (index 3) - full opacity
        { scale: 0.8, alpha: 0.7, offScreenOffset: 0 }      // far-right (index 4) - reduced opacity
    ];
    
    return positions[index] || { scale: 0.8, alpha: 0.7, offScreenOffset: 0 };
}

// Position sprites in carousel with center card (index 2) in middle of screen
function positionSprites(carouselIndex) {
    const carousel = carousels[carouselIndex];
    const centerIndex = 2; // Fixed center for 5 visible cards (0,1,2,3,4)
    const centerX = 540; // Middle of 1080px screen
    const direction = carousel.direction;
    
    carousel.sprites.forEach((sprite, index) => {
        const props = getPositionProperties(index, carousel.sprites.length, direction);
        
        if (carousel.sprites.length === 6 && index === 5) {
            // Buffer: position off-screen based on direction with lowest z-index
            if (direction === -1) {
                // Moving left, buffer on the right
                sprite.x = centerX + (4 - centerIndex) * (CARD_WIDTH + CARD_GAP) + props.offScreenOffset;
            } else {
                // Moving right, buffer on the left
                sprite.x = centerX + (0 - centerIndex) * (CARD_WIDTH + CARD_GAP) + props.offScreenOffset;
            }
            sprite.visible = false;
            sprite.zIndex = 0; // Lowest z-index for buffer
        } else {
            // Visible cards (0-4) - stack like cards in a deck
            const offsetFromCenter = (index - centerIndex) * (CARD_WIDTH + CARD_GAP);
            sprite.x = centerX + offsetFromCenter;
            sprite.visible = true;
            
            // Sequential z-index: card 0 at bottom, card 4 at top
            sprite.zIndex = 100 + (index * 10);
        }
        
        sprite.scale.set(props.scale);
        sprite.alpha = props.alpha;
    });
    
    console.log(`📍 Carousel ${carouselIndex}: ${carousel.sprites.length} sprites, center at index ${centerIndex}, buffer at index 5`);
}

// Render carousel with PixiJS
async function renderCarousel(carouselIndex) {
    const carousel = carousels[carouselIndex];
    console.log(`🎠 Rendering carousel ${carouselIndex} with ${carousel.images.length} images`);
    
    // Clear existing sprites
    carousel.sprites.forEach(sprite => sprite.destroy({ children: true }));
    carousel.sprites = [];
    carousel.container.removeChildren();
    
    // Create sprites for each image
    for (const image of carousel.images) {
        try {
            const sprite = await createPhotoSprite(image.path, carouselIndex);
            carousel.container.addChild(sprite);
            carousel.sprites.push(sprite);
        } catch (error) {
            console.error('❌ Failed to load image:', image.path, error);
        }
    }
    
    positionSprites(carouselIndex);
    console.log(`✅ Carousel ${carouselIndex} rendered with ${carousel.sprites.length} sprites`);
}

// Render all carousels
async function renderGallery() {
    console.log('🖼️ Rendering all carousels...');
    for (let i = 0; i < 3; i++) {
        await renderCarousel(i);
    }
    console.log('✅ All carousels rendered');
}

// Animate sprite to new position with scale effect
function animateSpriteToPosition(sprite, index, carouselIndex, duration = 2) {
    const carousel = carousels[carouselIndex];
    const direction = carousel.direction;
    const props = getPositionProperties(index, carousel.sprites.length, direction);
    const centerIndex = 2;
    const centerX = 540;
    
    let targetX;
    
    if (index === 5) {
        // Buffer position - set lowest z-index immediately, fade out in place, then teleport
        sprite.zIndex = 0; // Lowest z-index so it goes behind everything
        sprite.visible = true;
        
        // Fade out in current position (don't move)
        gsap.to(sprite, {
            alpha: 0,
            duration: duration * 0.5,
            ease: 'power2.in',
            onComplete: () => {
                // After fade out, teleport to buffer position off-screen
                if (direction === -1) {
                    sprite.x = centerX + (4 - centerIndex) * (CARD_WIDTH + CARD_GAP) + props.offScreenOffset;
                } else {
                    sprite.x = centerX + (0 - centerIndex) * (CARD_WIDTH + CARD_GAP) + props.offScreenOffset;
                }
                sprite.scale.set(0.8);
                sprite.visible = false;
            }
        });
        
        gsap.to(sprite.scale, {
            x: 0.6,
            y: 0.6,
            duration: duration * 0.5,
            ease: 'power2.in'
        });
        
    } else {
        // Visible positions (0-4) - stack like cards in a deck
        targetX = centerX + (index - centerIndex) * (CARD_WIDTH + CARD_GAP);
        
        // Sequential z-index: card 0 at bottom, card 4 at top
        sprite.zIndex = 100 + (index * 10);
        sprite.visible = true;
        
        gsap.to(sprite, {
            x: targetX,
            alpha: props.alpha,
            duration: duration,
            ease: 'power2.inOut'
        });
        
        gsap.to(sprite.scale, {
            x: props.scale,
            y: props.scale,
            duration: duration,
            ease: 'power2.inOut'
        });
    }
}

// Animate all sprites in carousel
function animateCarousel(carouselIndex) {
    const carousel = carousels[carouselIndex];
    if (carousel.sprites.length < 2) return;
    
    carousel.sprites.forEach((sprite, index) => {
        animateSpriteToPosition(sprite, index, carouselIndex, 2);
    });
}

// Rotate carousel - shift images and pull new image from queue
function rotateCarousel(carouselIndex) {
    const carousel = carousels[carouselIndex];
    // Need exactly 6 images for buffer system to work
    if (carousel.images.length !== 6 || carousel.sprites.length !== 6) {
        console.warn(`⚠️ Carousel ${carouselIndex} has ${carousel.sprites.length} sprites, needs 6 for proper animation`);
        return;
    }
    
    console.log(`🔄 Rotating carousel ${carouselIndex} with ${carousel.sprites.length} sprites`);
    
    if (carousel.direction === -1) {
        // Moving left: shift first to last (buffer position)
        carousel.images.push(carousel.images.shift());
        carousel.sprites.push(carousel.sprites.shift());
    } else {
        // Moving right: shift last to first (buffer position)
        carousel.images.unshift(carousel.images.pop());
        carousel.sprites.unshift(carousel.sprites.pop());
    }
    
    // Animate all sprites to their new positions
    carousel.sprites.forEach((sprite, index) => {
        if (sprite && !sprite.destroyed) {
            animateSpriteToPosition(sprite, index, carouselIndex, 1.5);
        }
    });
    
    // After animation completes, swap the buffer sprite with next image from queue
    setTimeout(() => {
        const exitingImage = carousel.images[5];
        const exitingSprite = carousel.sprites[5];
        
        // Add exiting image back to end of global queue
        imageQueue.push(exitingImage);
        
        // Get next image from queue start
        const nextImage = imageQueue.shift();
        
        // Kill animations and destroy exiting sprite
        gsap.killTweensOf(exitingSprite);
        gsap.killTweensOf(exitingSprite.scale);
        exitingSprite.parent?.removeChild(exitingSprite);
        exitingSprite.destroy({ children: true });
        
        // Replace with new image
        carousel.images[5] = nextImage;
        
        // Create new sprite for the image
        createPhotoSprite(nextImage.path, carouselIndex).then(newSprite => {
            carousel.sprites[5] = newSprite;
            carousel.container.addChild(newSprite);
            positionSprites(carouselIndex);
            console.log(`✅ Carousel ${carouselIndex} refreshed with new image from queue`);
        });
        
    }, 1500); // Wait for animation to complete
}

// Start auto-rotation with interval
function startAutoRotation(carouselIndex) {
    const carousel = carousels[carouselIndex];
    // Need exactly 6 images for buffer system
    if (carousel.images.length < 6) {
        console.warn(`⚠️ Carousel ${carouselIndex} has only ${carousel.images.length} images, needs 6. Skipping auto-rotation.`);
        return;
    }
    
    console.log(`▶️ Starting auto-rotation for carousel ${carouselIndex} with ${carousel.images.length} images`);
    
    // Initial animation to set positions
    animateCarousel(carouselIndex);
    
    // Rotate every 3 seconds (stagger start time to avoid simultaneous rotations)
    const staggerDelay = carouselIndex * 1000; // 0ms, 1000ms, 2000ms
    setTimeout(() => {
        setInterval(() => {
            if (carousel.images.length === 6) {
                rotateCarousel(carouselIndex);
            }
        }, 3000);
    }, staggerDelay);
}

// Start all carousels
function startAllAutoRotation() {
    for (let i = 0; i < 3; i++) {
        startAutoRotation(i);
    }
}

// Add new image to global queue
async function addNewImage(imageData) {
    console.log('New image received:', imageData);
    
    // Create new image object
    const newImage = {
        filename: imageData.filename || imageData.path.split('/').pop(),
        timestamp: imageData.timestamp || Math.floor(Date.now() / 1000),
        path: imageData.path || imageData.url
    };
    
    // Add to global queue
    imageQueue.push(newImage);
    console.log(`📦 New image added to queue (queue size: ${imageQueue.length})`);
}

// Initialize gallery
async function initializeGallery() {
    console.log('Initializing gallery with images:', initialImages);
    initPixiApp();
    distributeImages(initialImages);
    await renderGallery();
}

// Pusher setup for real-time updates
<?php if ($pusherClientConfig): ?>
const pusher = new Pusher('<?php echo $pusherClientConfig['key']; ?>', {
    cluster: '<?php echo $pusherClientConfig['cluster']; ?>',
    forceTLS: true
});

const channel = pusher.subscribe('<?php echo $pusherClientConfig['channel']; ?>');

channel.bind('<?php echo $pusherClientConfig['event']; ?>', function(data) {
    console.log('Pusher event received:', data);
    addNewImage(data);
});

channel.bind('pusher:subscription_succeeded', function() {
    console.log('✅ Successfully subscribed to Pusher channel');
});

channel.bind('pusher:subscription_error', function(status) {
    console.error('❌ Pusher subscription error:', status);
});

console.log('🚀 Pusher initialized');
<?php else: ?>
console.warn('⚠️ Pusher not configured');

// Simulate new images every 10 seconds for testing
setInterval(() => {
    const mockImage = {
        filename: 'test_' + Date.now() + '.png',
        timestamp: Math.floor(Date.now() / 1000),
        path: initialImages[Math.floor(Math.random() * initialImages.length)].path
    };
    addNewImage(mockImage);
}, 10000);
<?php endif; ?>

// Initialize on page load
window.addEventListener('DOMContentLoaded', async () => {
    await initializeGallery();
    // Start auto-rotation after a short delay
    setTimeout(() => {
        startAllAutoRotation();
        console.log('✨ Auto-rotation started for all carousels with PixiJS');
    }, 2000);
});
</script>

</body>
</html>
