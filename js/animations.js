/**
 * Reusable Animation System
 * Add entry animations to elements with data attributes
 */

// Initialize animations on page load
document.addEventListener('DOMContentLoaded', function() {
    initAnimations();
});

/**
 * Initialize all animated elements
 */
function initAnimations() {
    const animatedElements = document.querySelectorAll('[data-animate]');
    
    animatedElements.forEach((element, index) => {
        const animationType = element.getAttribute('data-animate');
        const delay = element.getAttribute('data-delay') || (index * 100);
        
        // Add initial hidden state
        element.style.opacity = '0';
        
        // Trigger animation after delay
        setTimeout(() => {
            element.classList.add(`animate-${animationType}`);
        }, delay);
    });
}

/**
 * Animate element with specific animation type
 * @param {HTMLElement} element - Element to animate
 * @param {string} animationType - Animation type (fade-in-up, fade-in-down, etc.)
 * @param {number} delay - Delay in milliseconds
 */
function animateElement(element, animationType, delay = 0) {
    element.style.opacity = '0';
    
    setTimeout(() => {
        element.classList.add(`animate-${animationType}`);
    }, delay);
}

/**
 * Animate multiple elements with stagger effect
 * @param {NodeList|Array} elements - Elements to animate
 * @param {string} animationType - Animation type
 * @param {number} staggerDelay - Delay between each element in milliseconds
 */
function animateStagger(elements, animationType, staggerDelay = 100) {
    elements.forEach((element, index) => {
        animateElement(element, animationType, index * staggerDelay);
    });
}

/**
 * Remove animation classes and reset element
 * @param {HTMLElement} element - Element to reset
 */
function resetAnimation(element) {
    const animationClasses = [
        'animate-fade-in-up',
        'animate-fade-in-down',
        'animate-fade-in-left',
        'animate-fade-in-right',
        'animate-fade-in-scale',
        'animate-slide-in-up'
    ];
    
    animationClasses.forEach(cls => element.classList.remove(cls));
    element.style.opacity = '0';
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initAnimations,
        animateElement,
        animateStagger,
        resetAnimation
    };
}
