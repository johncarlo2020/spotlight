// Gallery Page JavaScript
// Handles QR modal, image printing, and gallery interactions

// QR Modal Functions
function showQRModal(filename) {
    const modal = document.getElementById('qrModal');
    const qrContainer = document.getElementById('qrCodeContainer');
    const shareUrl = document.getElementById('shareUrl');
    
    // Show loading state
    qrContainer.classList.add('loading');
    qrContainer.innerHTML = '';
    
    // Create the shareable URL
    const baseUrl = window.location.origin + window.location.pathname.replace('gallery.php', '');
    const fullShareUrl = baseUrl + 'view.php?img=' + encodeURIComponent(filename) + '&name=Spotlight';
    
    // Generate QR code URL
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(fullShareUrl);
    
    // Create image element with loading handler
    const qrImg = new Image();
    qrImg.onload = function() {
        qrContainer.classList.remove('loading');
        qrContainer.innerHTML = '';
        qrImg.alt = 'QR Code';
        qrContainer.appendChild(qrImg);
    };
    qrImg.onerror = function() {
        qrContainer.classList.remove('loading');
        qrContainer.innerHTML = '<p style="color: #dc3545; padding: 20px;">Failed to load QR code</p>';
    };
    qrImg.src = qrUrl;
    
    shareUrl.textContent = fullShareUrl;
    
    // Show modal
    modal.style.display = 'block';
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

function copyShareUrl() {
    const shareUrl = document.getElementById('shareUrl');
    const urlText = shareUrl.textContent;
    
    // Use modern clipboard API
    navigator.clipboard.writeText(urlText).then(function() {
        // Create a temporary success message
        const originalText = shareUrl.textContent;
        shareUrl.textContent = '✓ Copied to clipboard!';
        shareUrl.style.color = '#28a745';
        
        setTimeout(() => {
            shareUrl.textContent = originalText;
            shareUrl.style.color = '';
        }, 2000);
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = urlText;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            const originalText = shareUrl.textContent;
            shareUrl.textContent = '✓ Copied to clipboard!';
            shareUrl.style.color = '#28a745';
            
            setTimeout(() => {
                shareUrl.textContent = originalText;
                shareUrl.style.color = '';
            }, 2000);
        } catch (err) {
            alert('Failed to copy link. Please copy manually.');
        }
        
        document.body.removeChild(textArea);
    });
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
