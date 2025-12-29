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
        
        .feed-container {
            width: 1080px;
            height: 1920px;
            padding-top: 240px;
            display: flex;
            flex-direction: column;
            gap: 0;
            position: relative;
            z-index: 10;
        }
        
        .carousel-row {
            flex: 1;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .carousel-track {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            transition: all 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }
        
        /* Sliding animation for right-to-left movement */
        .carousel-track.sliding {
            animation: slideLeft 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        /* Sliding animation for left-to-right movement (middle carousel) */
        .carousel-track.sliding-right {
            animation: slideRight 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        @keyframes slideLeft {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-195px); /* card width + gap */
            }
        }
        
        @keyframes slideRight {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(195px); /* card width + gap */
            }
        }
        
        .card-wrapper {
            perspective: 1500px;
            transform-style: preserve-3d;
            width: 180px;
            height: 270px;
            flex-shrink: 0;
            transition: transform 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
                        opacity 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            opacity: 1;
        }
        
        /* Fade out leftmost card */
        .card-wrapper.fade-out-left {
            animation: fadeOutLeft 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        /* Fade out rightmost card (for middle carousel) */
        .card-wrapper.fade-out-right {
            animation: fadeOutRight 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes fadeOutLeft {
            0% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateX(-100px) scale(0.8);
            }
        }
        
        @keyframes fadeOutRight {
            0% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateX(100px) scale(0.8);
            }
        }
        
        /* New card slides in from right with bounce effect */
        .card-wrapper.slide-in {
            animation: slideFromRight 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        /* New image appearing from right during rotation */
        .card-wrapper.slide-in-right {
            animation: slideInFromRight 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        /* New image appearing from left during rotation (middle carousel) */
        .card-wrapper.slide-in-left {
            animation: slideInFromLeft 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }
        
        @keyframes slideFromRight {
            0% {
                opacity: 0;
                transform: translateX(300px) scale(0.8) rotateY(-25deg);
            }
            100% {
                opacity: 1;
                transform: translateX(0) scale(1) rotateY(0deg);
            }
        }
        
        @keyframes slideInFromRight {
            0% {
                opacity: 0;
                transform: translateX(400px) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        
        @keyframes slideInFromLeft {
            0% {
                opacity: 0;
                transform: translateX(-400px) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        
        /* Center card is larger and more prominent */
        .card-wrapper.center {
            z-index: 5;
        }
        
        /* Position-based styling for 5 images - scales applied via inline style for smooth transition */
        .card-wrapper.far-left {
            opacity: 0.85;
        }
        
        .card-wrapper.far-left .card {
            transform: rotateY(15deg);
            filter: brightness(0.85);
        }
        
        .card-wrapper.left {
            opacity: 0.9;
        }
        
        .card-wrapper.left .card {
            transform: rotateY(8deg);
            filter: brightness(0.92);
        }
        
        .card-wrapper.center .card {
            transform: rotateY(0deg);
            filter: brightness(1);
        }
        
        .card-wrapper.right {
            opacity: 0.9;
        }
        
        .card-wrapper.right .card {
            transform: rotateY(-8deg);
            filter: brightness(0.92);
        }
        
        .card-wrapper.far-right {
            opacity: 0.85;
        }
        
        .card-wrapper.far-right .card {
            transform: rotateY(-15deg);
            filter: brightness(0.85);
        }
        
        .card {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: all 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: left center;
            border-radius: 15px;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.3),
                0 2px 10px rgba(0, 0, 0, 0.2),
                inset 0 0 0 1px rgba(255, 255, 255, 0.1);
            transition: all 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        /* Remove old grid-based rotations */
        
        /* Remove old grid-based rotations */
        
        /* New image animation - removed old keyframes */
        
        /* Decorations positioning */
        .decoration.lantern { top: 50px; right: 100px; }
        .decoration.flower { top: 130px; left: 80px; }
        .decoration.firework { top: 450px; left: 100px; }
    </style>
</head>
<body>

<div class="header"></div>

<div class="animated-decorations">
    <img src="asset/lantern.gif" class="decoration lantern" alt="">
    <img src="asset/flower.gif" class="decoration flower" alt="">
    <img src="asset/firework.gif" class="decoration firework" alt="">
</div>

<div class="feed-container" id="gallery">
    <div class="carousel-row" data-carousel="0">
        <div class="carousel-track" id="track-0"></div>
    </div>
    <div class="carousel-row" data-carousel="1">
        <div class="carousel-track" id="track-1"></div>
    </div>
    <div class="carousel-row" data-carousel="2">
        <div class="carousel-track" id="track-2"></div>
    </div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
// Initial images data
const initialImages = <?php echo json_encode($latestImages, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
console.log('Initial images loaded:', initialImages);

// Maximum 15 images in the gallery (5 per carousel)
const maxImages = 15;
const maxPerCarousel = 5;

// Carousel state - 3 carousels with 5 images each
const carousels = [
    { images: [] },
    { images: [] },
    { images: [] }
];

let nextCarouselIndex = 0;
let autoRotateIntervals = []; // Store interval IDs for each carousel

// Distribute images across carousels
function distributeImages(images) {
    if (!images || images.length === 0) return;
    
    // Distribute evenly
    for (let i = 0; i < images.length && i < maxImages; i++) {
        const carouselIndex = Math.floor(i / maxPerCarousel);
        carousels[carouselIndex].images.push(images[i]);
    }
}

// Get scale based on position
function getScaleForPosition(position) {
    switch(position) {
        case 'far-left': return 1.0;
        case 'left': return 1.05;
        case 'center': return 1.2;
        case 'right': return 1.05;
        case 'far-right': return 1.0;
        default: return 1.0;
    }
}

// Render a single carousel
function renderCarousel(carouselIndex) {
    const track = document.getElementById(`track-${carouselIndex}`);
    const carousel = carousels[carouselIndex];
    
    track.innerHTML = '';
    
    carousel.images.forEach((image, index) => {
        // Position class for 5 images: far-left, left, center, right, far-right
        let position = '';
        if (index === 0) position = 'far-left';
        else if (index === 1) position = 'left';
        else if (index === 2) position = 'center';
        else if (index === 3) position = 'right';
        else if (index === 4) position = 'far-right';
        
        const scale = getScaleForPosition(position);
        
        const cardWrapper = document.createElement('div');
        cardWrapper.className = `card-wrapper ${position}`;
        cardWrapper.style.transform = `scale(${scale})`;
        cardWrapper.innerHTML = `
            <div class="card">
                <img src="${image.path}" alt="Image ${image.filename}">
            </div>
        `;
        track.appendChild(cardWrapper);
    });
}

// Render all carousels
function renderGallery() {
    for (let i = 0; i < 3; i++) {
        renderCarousel(i);
    }
}

// Auto-rotate carousel - continuously moves photos from right to left
function startAutoRotation(carouselIndex) {
    // Clear existing interval if any
    if (autoRotateIntervals[carouselIndex]) {
        clearInterval(autoRotateIntervals[carouselIndex]);
    }
    
    const carousel = carousels[carouselIndex];
    if (carousel.images.length < 2) return; // Need at least 2 images to rotate
    
    // Middle carousel (index 1) moves left-to-right, others move right-to-left
    const isMiddleCarousel = carouselIndex === 1;
    
    // Rotate every 4 seconds
    autoRotateIntervals[carouselIndex] = setInterval(() => {
        if (carousel.images.length < 2) return;
        
        const track = document.getElementById(`track-${carouselIndex}`);
        const cards = track.querySelectorAll('.card-wrapper');
        
        if (isMiddleCarousel) {
            // Middle carousel: move from LEFT TO RIGHT
            const movingImage = carousel.images[carousel.images.length - 1]; // Last image
            
            // Add the moving image to the left side temporarily
            const newCardWrapper = document.createElement('div');
            newCardWrapper.className = 'card-wrapper far-left slide-in-left temp-card';
            newCardWrapper.style.transform = 'scale(1.0)';
            newCardWrapper.innerHTML = `
                <div class="card">
                    <img src="${movingImage.path}" alt="Image ${movingImage.filename}">
                </div>
            `;
            track.insertBefore(newCardWrapper, track.firstChild);
            
            // Update scales for existing cards BEFORE animation starts
            // Cards shift right: far-left->left, left->center, center->right, right->far-right
            // Skip the last card as it's fading out
            for (let i = 0; i < cards.length - 1; i++) {
                let newPosition = '';
                if (i === 0) newPosition = 'left';
                else if (i === 1) newPosition = 'center';
                else if (i === 2) newPosition = 'right';
                else if (i === 3) newPosition = 'far-right';
                
                const scale = getScaleForPosition(newPosition);
                cards[i].style.transform = `scale(${scale})`;
            }
            
            // Add fade-out animation to rightmost card
            if (cards[cards.length - 1]) {
                cards[cards.length - 1].classList.add('fade-out-right');
            }
            
            // Add sliding animation to track (right direction)
            track.classList.add('sliding-right');
            
            // After animation completes, clean up without re-rendering
            setTimeout(() => {
                // Move last image to the beginning (rotate right)
                const lastImage = carousel.images.pop();
                carousel.images.unshift(lastImage);
                
                // Remove animation class
                track.classList.remove('sliding-right');
                
                // Remove the faded-out rightmost card
                const allCards = track.querySelectorAll('.card-wrapper');
                if (allCards[allCards.length - 1]) {
                    allCards[allCards.length - 1].remove();
                }
                
                // Update classes on remaining cards to match their new positions
                const remainingCards = track.querySelectorAll('.card-wrapper');
                const positions = ['far-left', 'left', 'center', 'right', 'far-right'];
                remainingCards.forEach((card, idx) => {
                    card.className = `card-wrapper ${positions[idx]}`;
                    card.classList.remove('slide-in-left', 'temp-card', 'fade-out-right');
                });
            }, 1500);
        } else {
            // Top and bottom carousels: move from RIGHT TO LEFT
            const movingImage = carousel.images[0]; // First image
            
            // Add the moving image to the right side temporarily
            const newCardWrapper = document.createElement('div');
            newCardWrapper.className = 'card-wrapper far-right slide-in-right temp-card';
            newCardWrapper.style.transform = 'scale(1.0)';
            newCardWrapper.innerHTML = `
                <div class="card">
                    <img src="${movingImage.path}" alt="Image ${movingImage.filename}">
                </div>
            `;
            track.appendChild(newCardWrapper);
            
            // Add fade-out animation to leftmost card
            if (cards[0]) {
                cards[0].classList.add('fade-out-left');
            }
            
            // Update scales for existing cards BEFORE animation starts
            // Cards shift left: left->far-left, center->left, right->center, far-right->right
            // Skip the first card (index 0) as it's fading out
            for (let i = 1; i < cards.length; i++) {
                let newPosition = '';
                if (i === 1) newPosition = 'far-left';
                else if (i === 2) newPosition = 'left';
                else if (i === 3) newPosition = 'center';
                else if (i === 4) newPosition = 'right';
                
                const scale = getScaleForPosition(newPosition);
                cards[i].style.transform = `scale(${scale})`;
            }
            
            // Add sliding animation to track (left direction)
            track.classList.add('sliding');
            
            // After animation completes, clean up without re-rendering
            setTimeout(() => {
                // Move first image to the end (rotate left)
                const firstImage = carousel.images.shift();
                carousel.images.push(firstImage);
                
                // Remove animation class
                track.classList.remove('sliding');
                
                // Remove the faded-out leftmost card
                const allCards = track.querySelectorAll('.card-wrapper');
                if (allCards[0]) {
                    allCards[0].remove();
                }
                
                // Update classes on remaining cards to match their new positions
                const remainingCards = track.querySelectorAll('.card-wrapper');
                const positions = ['far-left', 'left', 'center', 'right', 'far-right'];
                remainingCards.forEach((card, idx) => {
                    card.className = `card-wrapper ${positions[idx]}`;
                    card.classList.remove('slide-in-right', 'temp-card', 'fade-out-left');
                });
            }, 1500);
        }
    }, 4000);
}

// Start auto-rotation for all carousels
function startAllAutoRotation() {
    for (let i = 0; i < 3; i++) {
        startAutoRotation(i);
    }
}

// Add new image to gallery
function addNewImage(imageData) {
    console.log('New image received:', imageData);
    
    // Create new image object
    const newImage = {
        filename: imageData.filename || imageData.path.split('/').pop(),
        timestamp: imageData.timestamp || Math.floor(Date.now() / 1000),
        path: imageData.path || imageData.url
    };
    
    // Always remove the oldest image first if we're at capacity
    let totalImages = 0;
    for (let i = 0; i < 3; i++) {
        totalImages += carousels[i].images.length;
    }
    
    if (totalImages >= maxImages) {
        // Find the oldest image across all carousels
        let oldestCarouselIndex = -1;
        let oldestTimestamp = Infinity;
        
        for (let i = 0; i < 3; i++) {
            if (carousels[i].images.length > 0) {
                // First image in each carousel is the oldest in that carousel
                const firstImageTimestamp = carousels[i].images[0].timestamp || 0;
                if (firstImageTimestamp < oldestTimestamp) {
                    oldestTimestamp = firstImageTimestamp;
                    oldestCarouselIndex = i;
                }
            }
        }
        
        // Remove the oldest image
        if (oldestCarouselIndex !== -1) {
            const removed = carousels[oldestCarouselIndex].images.shift();
            console.log(`Removed oldest image from carousel ${oldestCarouselIndex}:`, removed.filename);
            renderCarousel(oldestCarouselIndex);
        }
    }
    
    // Find first carousel with less than 5 images
    let carouselIndex = -1;
    for (let i = 0; i < 3; i++) {
        if (carousels[i].images.length < maxPerCarousel) {
            carouselIndex = i;
            break;
        }
    }
    
    // If all carousels full, use round-robin
    if (carouselIndex === -1) {
        carouselIndex = nextCarouselIndex;
        nextCarouselIndex = (nextCarouselIndex + 1) % 3;
    }
    
    const carousel = carousels[carouselIndex];
    const track = document.getElementById(`track-${carouselIndex}`);
    
    // Add new image with smooth animation
    carousel.images.push(newImage);
    
    // Render with animation
    renderCarousel(carouselIndex);
    
    // Add slide-in animation to the last card
    setTimeout(() => {
        const cards = track.querySelectorAll('.card-wrapper');
        if (cards.length > 0) {
            const lastCard = cards[cards.length - 1];
            lastCard.classList.add('slide-in');
            lastCard.style.animation = 'slideFromRight 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards';
        }
    }, 50);
    
    // Restart auto-rotation for this carousel
    startAutoRotation(carouselIndex);
}

// Initialize gallery
function initializeGallery() {
    console.log('Initializing gallery with images:', initialImages);
    distributeImages(initialImages);
    renderGallery();
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
window.addEventListener('DOMContentLoaded', () => {
    initializeGallery();
    // Start auto-rotation after a short delay
    setTimeout(() => {
        startAllAutoRotation();
        console.log('✨ Auto-rotation started for all carousels');
    }, 2000);
});
</script>

</body>
</html>
