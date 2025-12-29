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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotlight Gallery</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #F7B4CD;
            color: #2c2c2c;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
        }
        
        .header .logo {
            margin-bottom: 20px;
        }
        
        .header .logo img {
            max-width: 250px;
            height: auto;
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
        }
        
        .header h1 {
            color: #ff69b4;
            font-size: 3rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 0 20px rgba(255, 105, 180, 0.3);
            margin-bottom: 10px;
            display: none;
        }
        
        .header p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 20px;
            display: none;
        }
        
        .stats {
            display: inline-block;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 20px;
            border-radius: 20px;
            border: 2px solid rgba(255, 105, 180, 0.3);
            color: #4a4a4a;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .image-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            overflow: hidden;
            border: 2px solid rgba(255, 105, 180, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(255, 105, 180, 0.3);
            border-color: #ff69b4;
        }
        
        .image-wrapper {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
        }
        
        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .image-card:hover .image-wrapper img {
            transform: scale(1.05);
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(255, 105, 180, 0.8) 100%);
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }
        
        .image-info {
            color: white;
        }
        
        .image-info h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #fff;
        }
        
        .image-info p {
            font-size: 0.9rem;
            color: #666;
        }
        
        .card-actions {
            padding: 15px;
            background: rgba(255, 255, 255, 0.5);
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .view-btn {
            background: #ff69b4;
            color: white;
        }
        
        .view-btn:hover {
            background: #ff1493;
            transform: translateY(-2px);
        }
        
        .share-btn {
            background: rgba(255, 255, 255, 0.9);
            color: #ff69b4;
            border: 2px solid #ff69b4;
        }
        
        .share-btn:hover {
            background: #ff69b4;
            color: white;
            transform: translateY(-2px);
        }
        
        .print-btn {
            background: #ff69b4;
            color: white;
        }
        
        .print-btn:hover {
            background: #ff1493;
            transform: translateY(-2px);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin: 40px 0;
        }
        
        .pagination a, .pagination span {
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .pagination a {
            background: rgba(255, 255, 255, 0.9);
            color: #ff69b4;
            border: 2px solid rgba(255, 105, 180, 0.3);
        }
        
        .pagination a:hover {
            background: #ff69b4;
            color: white;
            border-color: #ff69b4;
        }
            color: #fff;
            transform: translateY(-2px);
        }
        
        .pagination .current {
            background: #ff69b4;
            color: #fff;
            border: 2px solid #ff69b4;
        }
        
        .pagination .disabled {
            background: rgba(255, 255, 255, 0.5);
            color: #999;
            cursor: not-allowed;
            border: 2px solid rgba(255, 105, 180, 0.2);
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #ff69b4;
            padding: 12px 20px;
            border: 2px solid #ff69b4;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-btn:hover {
            background: #ff69b4;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }
        
        .empty-state h2 {
            color: #4a4a4a;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #666;
            font-size: 1.2rem;
        }
        
        /* QR Code Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            border: 2px solid rgba(255, 105, 180, 0.3);
            box-shadow: 0 10px 40px rgba(255, 105, 180, 0.3);
        }
            position: relative;
        }
        
        .close-modal {
            color: #ff69b4;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        
        .close-modal:hover {
            color: #ff1493;
        }
        
        .modal h3 {
            color: #4a4a4a;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-transform: uppercase;
        }
        
        .modal-qr {
            margin: 20px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            border: 2px solid rgba(255, 105, 180, 0.2);
        }
        
        .modal-url {
            background: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid rgba(255, 105, 180, 0.3);
            word-break: break-all;
        }
        
        .modal-url input {
            width: 100%;
            border: none;
            background: transparent;
            color: #4a4a4a;
            text-align: center;
            font-size: 14px;
        }
        
        .copy-btn {
            background: #ff69b4;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin: 10px;
        }
        
        .copy-btn:hover {
            background: #ff1493;
        }
        
        /* iPad Optimization */
        @media (min-width: 768px) and (max-width: 1024px) {
            .gallery-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
            
            .image-wrapper {
                height: 200px;
            }
            
            .header h1 {
                font-size: 2.5rem;
            }
        }
        
        /* Mobile Optimization */
        @media (max-width: 767px) {
            .gallery-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
            
            .back-btn {
                position: static;
                margin-bottom: 20px;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">← Back to Generator</a>
    
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="logo.png" alt="Spotlight Logo">
            </div>
            <h1>SPOTLIGHT GALLERY</h1>
            <p>Browse all your amazing spotlight creations</p>
            <div class="stats">
                <?php echo $totalImages; ?> Total Images
            </div>
        </div>
        
        <?php if (empty($currentImages)): ?>
            <div class="empty-state">
                <h2>No Images Yet</h2>
                <p>Create your first spotlight image to see it here!</p>
            </div>
        <?php else: ?>
            <div class="gallery-grid">
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
                            <button onclick="printImage('<?php echo htmlspecialchars($image['path']); ?>')" class="action-btn print-btn">Print</button>
                            <button onclick="showQRModal('<?php echo htmlspecialchars($image['filename']); ?>')" class="action-btn share-btn">Share</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>">‹ Previous</a>
                    <?php else: ?>
                        <span class="disabled">‹ Previous</span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>">Next ›</a>
                    <?php else: ?>
                        <span class="disabled">Next ›</span>
                    <?php endif; ?>
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
            showNewImageNotification();
        });

        function addNewImageToGallery(imageData) {
            const galleryGrid = document.querySelector('.gallery-grid');
            if (!galleryGrid) return;

            // Create new image card HTML
            const newImageCard = document.createElement('div');
            newImageCard.className = 'image-card';
            newImageCard.style.animation = 'fadeInUp 0.5s ease-out';
            
            newImageCard.innerHTML = `
                <div class="image-wrapper">
                    <img src="${imageData.path}" alt="Spotlight Image" loading="lazy">
                    <div class="image-overlay">
                        <div class="image-info">
                            <p>${imageData.formatted_date}</p>
                        </div>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="${imageData.path}" target="_blank" class="action-btn view-btn">View Full</a>
                    <button onclick="printImage('${imageData.path}')" class="action-btn print-btn">Print</button>
                    <button onclick="showQRModal('${imageData.filename}')" class="action-btn share-btn">Share</button>
                </div>
            `;

            // Insert at the beginning of gallery
            galleryGrid.insertBefore(newImageCard, galleryGrid.firstChild);
            
            // Remove last image if we're at the limit (9 images per page)
            const imageCards = galleryGrid.querySelectorAll('.image-card');
            if (imageCards.length > 9) {
                imageCards[imageCards.length - 1].remove();
            }
        }

        function showNewImageNotification() {
            createToaster('success', '🖼️ New Image Added!', 'New spotlight image has been created', 5000);
        }

        // Advanced Toaster System
        function createToaster(type, title, message, duration = 4000) {
            // Create toaster container if it doesn't exist
            let toasterContainer = document.getElementById('toaster-container');
            if (!toasterContainer) {
                toasterContainer = document.createElement('div');
                toasterContainer.id = 'toaster-container';
                toasterContainer.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 10000;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                `;
                document.body.appendChild(toasterContainer);
            }

            // Create toaster element
            const toaster = document.createElement('div');
            toaster.className = `toaster toaster-${type}`;
            
            // Define colors based on type
            const colors = {
                success: { bg: '#28a745', border: '#1e7e34' },
                info: { bg: '#17a2b8', border: '#117a8b' },
                warning: { bg: '#ffc107', border: '#e0a800' },
                error: { bg: '#dc3545', border: '#bd2130' }
            };
            
            const color = colors[type] || colors.info;
            
            toaster.innerHTML = `
                <div style="
                    background: ${color.bg};
                    color: white;
                    padding: 16px 20px;
                    border-radius: 8px;
                    border-left: 4px solid ${color.border};
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.1);
                    min-width: 300px;
                    max-width: 400px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    animation: toasterSlideIn 0.4s ease-out;
                    cursor: pointer;
                    transition: transform 0.2s ease;
                " onmouseover="this.style.transform='translateX(-5px)'" onmouseout="this.style.transform='translateX(0)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="flex: 1;">
                            <div style="font-weight: bold; font-size: 14px; margin-bottom: 4px;">
                                ${title}
                            </div>
                            <div style="font-size: 13px; opacity: 0.95; line-height: 1.4;">
                                ${message}
                            </div>
                        </div>
                        <div style="margin-left: 10px; cursor: pointer; font-size: 16px; opacity: 0.8; hover: opacity: 1;" onclick="this.parentElement.parentElement.parentElement.remove()">
                            ×
                        </div>
                    </div>
                    <div style="
                        position: absolute;
                        bottom: 0;
                        left: 0;
                        height: 3px;
                        background: rgba(255,255,255,0.3);
                        animation: toasterProgress ${duration}ms linear;
                        border-radius: 0 0 4px 4px;
                    "></div>
                </div>
            `;

            // Add click to dismiss
            toaster.onclick = function() {
                this.style.animation = 'toasterSlideOut 0.3s ease-in forwards';
                setTimeout(() => this.remove(), 300);
            };

            // Add to container
            toasterContainer.appendChild(toaster);

            // Auto remove after duration
            setTimeout(() => {
                if (toaster.parentNode) {
                    toaster.style.animation = 'toasterSlideOut 0.3s ease-in forwards';
                    setTimeout(() => {
                        if (toaster.parentNode) {
                            toaster.remove();
                        }
                    }, 300);
                }
            }, duration);

            // Add sound effect (optional)
            if (type === 'success') {
                playNotificationSound();
            }
        }

        // Optional notification sound
        function playNotificationSound() {
            try {
                // Create a subtle notification beep using Web Audio API
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
                
                gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (e) {
                // Ignore if Web Audio API is not supported
            }
        }

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
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
            
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(300px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes toasterSlideIn {
                from {
                    opacity: 0;
                    transform: translateX(100%) scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                }
            }
            
            @keyframes toasterSlideOut {
                from {
                    opacity: 1;
                    transform: translateX(0) scale(1);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%) scale(0.8);
                }
            }
            
            @keyframes toasterProgress {
                from {
                    width: 100%;
                }
                to {
                    width: 0%;
                }
            }
            
            .toaster:hover .toaster-progress {
                animation-play-state: paused;
            }
        `;
        document.head.appendChild(style);
    </script>
    <?php else: ?>
    <script>
        console.log('Pusher not configured - real-time updates disabled');
    </script>
    <?php endif; ?>

    <script>
        function showQRModal(filename) {
            const modal = document.getElementById('qrModal');
            const qrContainer = document.getElementById('qrCodeContainer');
            const shareUrl = document.getElementById('shareUrl');
            
            // Create the shareable URL
            const baseUrl = window.location.origin + window.location.pathname.replace('gallery.php', '');
            const fullShareUrl = baseUrl + 'view.php?img=' + encodeURIComponent(filename) + '&name=Spotlight';
            
            // Generate QR code URL
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(fullShareUrl);
            
            // Update modal content
            qrContainer.innerHTML = '<img src="' + qrUrl + '" alt="QR Code" style="border-radius: 8px;">';
            shareUrl.value = fullShareUrl;
            
            // Show modal
            modal.style.display = 'block';
        }
        
        function closeQRModal() {
            document.getElementById('qrModal').style.display = 'none';
        }
        
        function copyShareUrl() {
            const shareUrl = document.getElementById('shareUrl');
            shareUrl.select();
            shareUrl.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                document.execCommand('copy');
                alert('Link copied to clipboard!');
            } catch (err) {
                // Fallback for modern browsers
                navigator.clipboard.writeText(shareUrl.value).then(function() {
                    alert('Link copied to clipboard!');
                }).catch(function() {
                    alert('Failed to copy link. Please copy manually.');
                });
            }
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('qrModal');
            if (event.target == modal) {
                closeQRModal();
            }
        }
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            // Close modal with Escape key
            if (e.key === 'Escape') {
                closeQRModal();
            }
            
            if (e.key === 'ArrowLeft' && <?php echo $currentPage; ?> > 1) {
                window.location.href = '?page=<?php echo $currentPage - 1; ?>';
            } else if (e.key === 'ArrowRight' && <?php echo $currentPage; ?> < <?php echo $totalPages; ?>) {
                window.location.href = '?page=<?php echo $currentPage + 1; ?>';
            }
        });
        
        function printImage(imagePath) {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            
            // Write the HTML content for printing
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Image</title>
                    <style>
                        body {
                            margin: 0;
                            padding: 0;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            min-height: 100vh;
                            background: white;
                        }
                        img {
                            max-width: 100%;
                            max-height: 100vh;
                            object-fit: contain;
                        }
                        @media print {
                            body {
                                margin: 0;
                            }
                            img {
                                max-width: 100%;
                                height: auto;
                                page-break-inside: avoid;
                            }
                        }
                    </style>
                </head>
                <body>
                    <img src="${imagePath}" alt="Spotlight Image" onload="window.print(); window.close();">
                </body>
                </html>
            `);
            
            printWindow.document.close();
        }
    </script>
</body>
</html>