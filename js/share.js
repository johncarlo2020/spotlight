/**
 * Share Page JavaScript
 * Clipboard copy functionality
 */

function copyToClipboard() {
    const urlInput = document.getElementById('shareUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        alert('Link copied to clipboard!');
    } catch (err) {
        // Fallback for modern browsers
        navigator.clipboard.writeText(urlInput.value).then(function() {
            alert('Link copied to clipboard!');
        }).catch(function() {
            alert('Failed to copy link. Please copy manually.');
        });
    }
}
