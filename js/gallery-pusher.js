// Gallery Pusher Helper Functions
// Functions for real-time updates and notifications

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

function showNewImageNotification(customerName) {
    createToaster('success', '🖼️ New Image Added!', `New spotlight image created for ${customerName}`, 5000);
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

// Keyboard Navigation
document.addEventListener('keydown', function(e) {
    // Close modal with Escape key
    if (e.key === 'Escape') {
        closeQRModal();
    }
});
