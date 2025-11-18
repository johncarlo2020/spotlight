// Gallery Pusher Helper Functions
// Functions for real-time updates and notifications

function addNewImageToGallery(imageData) {
    const galleryGrid = document.querySelector('.gallery-grid');
    if (!galleryGrid) return;

    // Create new image card HTML
    const newImageCard = document.createElement('div');
    newImageCard.className = 'image-card';
    
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
            <button onclick="printImage('${imageData.path}')" class="action-btn print-btn"><i class="fas fa-print"></i> Print</button>
            <button onclick="showQRModal('${imageData.filename}')" class="action-btn share-btn"><i class="fas fa-qrcode"></i> Share</button>
        </div>
    `;

    // Set initial state for animation
    newImageCard.style.opacity = '0';
    newImageCard.style.transform = 'translateY(30px) scale(0.95)';
    
    // Insert at the beginning of gallery
    galleryGrid.insertBefore(newImageCard, galleryGrid.firstChild);
    
    // Trigger animation after a brief delay
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            newImageCard.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            newImageCard.style.opacity = '1';
            newImageCard.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Remove last image if we're at the limit (10 images per page)
    const imageCards = galleryGrid.querySelectorAll('.image-card');
    if (imageCards.length > 10) {
        const lastCard = imageCards[imageCards.length - 1];
        // Fade out animation before removing
        lastCard.style.transition = 'all 0.4s ease-out';
        lastCard.style.opacity = '0';
        lastCard.style.transform = 'scale(0.95)';
        setTimeout(() => lastCard.remove(), 400);
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
            top: 80px;
            right: 30px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
        `;
        document.body.appendChild(toasterContainer);
    }

    // Create toaster element
    const toaster = document.createElement('div');
    toaster.className = `toaster toaster-${type}`;
    
    // Define colors based on type
    const colors = {
        success: { accent: '#28a745' },
        info: { accent: '#17a2b8' },
        warning: { accent: '#ffc107' },
        error: { accent: '#dc3545' }
    };
    
    const color = colors[type] || colors.info;
    
    toaster.innerHTML = `
        <div style="
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            color: white;
            padding: 16px 20px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.05),
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
            min-width: 320px;
            max-width: 400px;
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            animation: toasterSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        " onmouseover="this.style.transform='translateX(-8px)'; this.style.borderColor='rgba(255,255,255,0.2)'" onmouseout="this.style.transform='translateX(0)'; this.style.borderColor='rgba(255,255,255,0.1)'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #fff;">
                        ${title}
                    </div>
                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.8); line-height: 1.5;">
                        ${message}
                    </div>
                </div>
                <div style="
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.1);
                    cursor: pointer;
                    font-size: 18px;
                    color: rgba(255, 255, 255, 0.7);
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                " onclick="event.stopPropagation(); this.parentElement.parentElement.parentElement.remove()" onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.7)'">
                    ×
                </div>
            </div>
            <div style="
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(90deg, ${color.accent}, transparent);
                animation: toasterProgress ${duration}ms linear;
                opacity: 0.5;
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
