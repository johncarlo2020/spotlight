<?php
// gallery.php: Display all processed images with pagination
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

// Pagination
$itemsPerPage = 9;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalImages = count($images);
$totalPages = ceil($totalImages / $itemsPerPage);
$offset = ($currentPage - 1) * $itemsPerPage;
$currentImages = array_slice($images, $offset, $itemsPerPage);

function getCustomerNameFromFilename($filename) {
    // Try to extract customer name from filename pattern
    if (preg_match('/output_\d+_\d+\.png/', $filename)) {
        return 'Customer'; // Default if no name found
    }
    return 'Customer';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotlight Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/gallery.css">
    <style>
        /* Animation styles */
        [data-animate] {
            opacity: 0;
        }
        
        .animate-fade-in-down {
            animation: fadeInDown 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        .animate-fade-in-left {
            animation: fadeInLeft 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn" data-animate="fade-in-left">← Back to Generator</a>
    
    <div class="header">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        <h1>SPOTLIGHT GALLERY</h1>
        <p>Browse all your amazing spotlight creations</p>
    </div>
    
    <?php if (empty($currentImages)): ?>
        <div class="empty-state" data-animate="fade-in-up" data-delay="200">
            <h2>No Images Yet</h2>
            <p>Create your first spotlight image to see it here!</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid" data-animate="fade-in-up" data-delay="300">
                <?php foreach ($currentImages as $index => $image): ?>
                    <div class="image-card">
                        <div class="image-wrapper">
                            <img src="<?php echo htmlspecialchars($image['path']); ?>" alt="Spotlight Image" loading="lazy">
                            <div class="image-overlay">
                                <div class="image-info">
                                    <p><?php echo date('M j, Y g:i A', $image['timestamp']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="<?php echo htmlspecialchars($image['path']); ?>" target="_blank" class="action-btn view-btn">View Full</a>
                            <button onclick="printImage('<?php echo htmlspecialchars($image['path']); ?>')" class="action-btn print-btn">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button onclick="showQRModal('<?php echo htmlspecialchars($image['filename']); ?>')" class="action-btn share-btn">
                                <i class="fas fa-qrcode"></i> Share
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <div class="stats">
                        <?php echo $totalImages; ?> Total Images
                    </div>
                    <?php if ($currentPage > 1): ?>
                        <a href="#" onclick="event.preventDefault(); window.location.href='?page=<?php echo $currentPage - 1; ?>'; return false;">‹ Previous</a>
                    <?php else: ?>
                        <span class="disabled">‹ Previous</span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="#" onclick="event.preventDefault(); window.location.href='?page=<?php echo $i; ?>'; return false;"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="#" onclick="event.preventDefault(); window.location.href='?page=<?php echo $currentPage + 1; ?>'; return false;">Next ›</a>
                    <?php else: ?>
                        <span class="disabled">Next ›</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="pagination">
                    <div class="stats">
                        <?php echo $totalImages; ?> Total Images
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- QR Code Modal -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeQRModal()">&times;</span>
            <h3>Share Your Spotlight</h3>
            <div class="modal-qr" id="qrCodeContainer">
                <!-- QR code will be loaded here -->
            </div>
            <div class="modal-url">
                <input type="text" id="shareUrl" readonly>
            </div>
            <button class="copy-btn" onclick="copyShareUrl()">Copy Link</button>
        </div>
    </div>
    
    <!-- Pusher JavaScript Client -->
    <?php if ($pusherClientConfig): ?>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="js/gallery-pusher.js"></script>
    <script>
        // Initialize Pusher client for real-time updates
        const pusher = new Pusher('<?php echo $pusherClientConfig['key']; ?>', {
            cluster: '<?php echo $pusherClientConfig['cluster']; ?>'
        });

        // Add connection state logging and toaster notifications
        pusher.connection.bind('connected', function() {
            console.log('✅ Gallery connected to Pusher successfully!');
            console.log('Listening for updates on channel: <?php echo $pusherClientConfig['channel']; ?>');
            createToaster('success', '🔗 Connected!', 'Real-time updates are now active', 3000);
        });

        pusher.connection.bind('disconnected', function() {
            console.log('❌ Gallery disconnected from Pusher');
            createToaster('warning', '⚠️ Disconnected', 'Real-time updates temporarily unavailable', 4000);
        });

        pusher.connection.bind('error', function(error) {
            console.log('🚨 Pusher connection error:', error);
            createToaster('error', '🚨 Connection Error', 'Unable to connect for real-time updates', 5000);
        });

        const channel = pusher.subscribe('<?php echo $pusherClientConfig['channel']; ?>');
        
        // Add subscription logging
        channel.bind('pusher:subscription_succeeded', function() {
            console.log('📡 Successfully subscribed to gallery updates channel');
        });

        channel.bind('pusher:subscription_error', function(error) {
            console.log('❌ Failed to subscribe to channel:', error);
        });
        
        channel.bind('<?php echo $pusherClientConfig['event']; ?>', function(data) {
            console.log('🖼️ New image received:', data);
            
            // Add new image to gallery dynamically
            addNewImageToGallery(data);
            
            // Show notification
            showNewImageNotification(data.customer_name);
        });

        // Add keyboard navigation for pagination
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && <?php echo $currentPage; ?> > 1) {
                window.location.href = '?page=<?php echo $currentPage - 1; ?>';
            } else if (e.key === 'ArrowRight' && <?php echo $currentPage; ?> < <?php echo $totalPages; ?>) {
                window.location.href = '?page=<?php echo $currentPage + 1; ?>';
            }
        });
    </script>
    <?php else: ?>
    <script>
        console.log('Pusher not configured - real-time updates disabled');
    </script>
    <?php endif; ?>

    <script src="js/gallery-pusher.js"></script>
    <script src="js/animations.js"></script>
    <script src="js/gallery.js"></script>
</body>
