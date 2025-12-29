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

// Get the 9 latest images
$latestImages = array_slice($images, 0, 9);
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
            gap: 40px;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .card-wrapper {
            perspective: 1500px;
            transform-style: preserve-3d;
            width: 270px;
            height: 360px;
            flex-shrink: 0;
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
        }
        
        /* Hide the far left card during slide */
        .card-wrapper.fade-out {
            opacity: 0;
            transform: translateX(-100px) scale(0.8);
        }
        
        /* New card slides in from right */
        .card-wrapper.slide-in {
            animation: slideFromRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes slideFromRight {
            0% {
                opacity: 0;
                transform: translateX(350px) scale(0.8);
            }
            100% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        
        /* Center card is larger and more prominent */
        .card-wrapper.center {
            transform: scale(1.15);
            z-index: 5;
        }
        
        /* Side cards are slightly dimmed and rotated */
        .card-wrapper.left .card {
            transform: rotateY(25deg);
            filter: brightness(0.85);
        }
        
        .card-wrapper.right .card {
            transform: rotateY(-25deg);
            filter: brightness(0.85);
        }
        
        .card-wrapper.center .card {
            transform: rotateY(0deg);
            filter: brightness(1);
        }
        
        .card {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
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
            transition: all 0.6s ease;
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

// Maximum 9 images in the gallery (3 per carousel)
const maxImages = 9;
const maxPerCarousel = 3;

// Carousel state - 3 carousels with 3 images each
const carousels = [
    { images: [] },
    { images: [] },
    { images: [] }
];

let nextCarouselIndex = 0;

// Distribute images across carousels
function distributeImages(images) {
    if (!images || images.length === 0) return;
    
    // Distribute evenly
    for (let i = 0; i < images.length && i < maxImages; i++) {
        const carouselIndex = Math.floor(i / maxPerCarousel);
        carousels[carouselIndex].images.push(images[i]);
    }
}

// Render a single carousel
function renderCarousel(carouselIndex) {
    const track = document.getElementById(`track-${carouselIndex}`);
    const carousel = carousels[carouselIndex];
    
    track.innerHTML = '';
    
    carousel.images.forEach((image, index) => {
        const position = index === 0 ? 'left' : (index === 1 ? 'center' : 'right');
        const cardWrapper = document.createElement('div');
        cardWrapper.className = `card-wrapper ${position}`;
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

// Add new image to gallery
function addNewImage(imageData) {
    console.log('New image received:', imageData);
    
    // Create new image object
    const newImage = {
        filename: imageData.filename || imageData.path.split('/').pop(),
        timestamp: imageData.timestamp || Math.floor(Date.now() / 1000),
        path: imageData.path || imageData.url
    };
    
    // Find first carousel with less than 3 images
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
    
    // Handle different states
    if (carousel.images.length < maxPerCarousel) {
        // Just add and render normally
        carousel.images.push(newImage);
        renderCarousel(carouselIndex);
        
        // Animate the new card
        setTimeout(() => {
            const cards = track.querySelectorAll('.card-wrapper');
            if (cards.length > 0) {
                cards[cards.length - 1].classList.add('slide-in');
            }
        }, 50);
    } else {
        // Carousel is full - slide animation
        // Step 1: Add new image to DOM on the right (4th position temporarily)
        const newCardWrapper = document.createElement('div');
        newCardWrapper.className = 'card-wrapper right slide-in';
        newCardWrapper.innerHTML = `
            <div class="card">
                <img src="${newImage.path}" alt="Image ${newImage.filename}">
            </div>
        `;
        track.appendChild(newCardWrapper);
        
        // Step 2: After a brief moment, slide everything left
        setTimeout(() => {
            const cards = track.querySelectorAll('.card-wrapper');
            
            // Mark leftmost card to fade out
            if (cards[0]) {
                cards[0].classList.add('fade-out');
            }
            
            // Shift positions: left->hidden, center->left, right->center, new->center
            if (cards[1]) cards[1].classList.remove('center');
            if (cards[1]) cards[1].classList.add('left');
            
            if (cards[2]) cards[2].classList.remove('right');
            if (cards[2]) cards[2].classList.add('left');
            
            if (cards[3]) cards[3].classList.remove('slide-in');
            if (cards[3]) cards[3].classList.add('center');
            
            // Step 3: After slide completes, update array and re-render with new image in center
            setTimeout(() => {
                const oldLeft = carousel.images[0];
                const oldRight = carousel.images[2];
                
                // New arrangement: [oldRight, newImage, oldCenter] -> old right becomes left, new in center
                carousel.images = [oldRight, newImage, carousel.images[1]];
                renderCarousel(carouselIndex);
            }, 850);
        }, 50);
    }
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
});
</script>

</body>
</html>
