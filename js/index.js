// Index Page JavaScript
// Handles image upload and navigation to doodle page

function goToDoodle() {
    const nameInput = document.getElementById('customer_name');
    const imageInput = document.getElementById('image');
    
    const customerName = nameInput.value.trim();
    const file = imageInput.files[0];
    
    if (!customerName) {
        alert('Please enter a customer name!');
        nameInput.focus();
        return;
    }
    
    if (!file) {
        alert('Please select an image!');
        imageInput.click();
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(event) {
        // Store data in sessionStorage instead of URL
        sessionStorage.setItem('uploadedImage', event.target.result);
        sessionStorage.setItem('customerName', customerName);
        window.location.href = 'doodle.php';
    };
    reader.readAsDataURL(file);
}
