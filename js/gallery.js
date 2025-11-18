// Gallery Page JavaScript
// Handles QR modal, image printing, and gallery interactions

// QR Modal Functions
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

// Print Image Function
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
