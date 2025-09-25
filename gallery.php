<?php
// gallery.php: Display all processed images with pagination

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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #000000;
            color: #ffffff;
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
            color: #fff;
            font-size: 3rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            margin-bottom: 10px;
            display: none;
        }
        
        .header p {
            color: #ccc;
            font-size: 1.2rem;
            margin-bottom: 20px;
            display: none;
        }
        
        .stats {
            display: inline-block;
            background: #1a1a1a;
            padding: 10px 20px;
            border-radius: 20px;
            border: 1px solid #333;
            color: #fff;
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
            background: #1a1a1a;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #333;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.1);
            border-color: #666;
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
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
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
            color: #ccc;
        }
        
        .card-actions {
            padding: 15px;
            background: #111;
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
            background: #666;
            color: white;
        }
        
        .view-btn:hover {
            background: #555;
            transform: translateY(-2px);
        }
        
        .share-btn {
            background: #888;
            color: white;
        }
        
        .share-btn:hover {
            background: #777;
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
            background: #1a1a1a;
            color: #fff;
            border: 1px solid #333;
        }
        
        .pagination a:hover {
            background: #666;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .pagination .current {
            background: #fff;
            color: #000;
            border: 1px solid #fff;
        }
        
        .pagination .disabled {
            background: #333;
            color: #666;
            cursor: not-allowed;
        }
        
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #1a1a1a;
            color: #fff;
            padding: 12px 20px;
            border: 1px solid #333;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-btn:hover {
            background: #666;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }
        
        .empty-state h2 {
            color: #666;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #999;
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
            background: #111111;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            border: 1px solid #333;
            position: relative;
        }
        
        .close-modal {
            color: #fff;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }
        
        .close-modal:hover {
            color: #aaa;
        }
        
        .modal h3 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-transform: uppercase;
        }
        
        .modal-qr {
            margin: 20px 0;
            padding: 20px;
            background: #1a1a1a;
            border-radius: 10px;
            border: 1px solid #333;
        }
        
        .modal-url {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #333;
            word-break: break-all;
        }
        
        .modal-url input {
            width: 100%;
            border: none;
            background: transparent;
            color: #fff;
            text-align: center;
            font-size: 14px;
        }
        
        .copy-btn {
            background: #666;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin: 10px;
        }
        
        .copy-btn:hover {
            background: #888;
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
    </script>
</body>
</html>