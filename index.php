<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image and Customer Name</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            align-items: space-around;
            height: 100svh;
        }
    </style>
</head>
<body>
      <div class="logo" data-animate="fade-in-down">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        
        <div class="text-center mb-30" data-animate="fade-in-up" data-delay="200">
            <a href="gallery.php" class="gallery-link"><i class="fas fa-images"></i> View Gallery</a>
        </div>
        <form id="uploadForm" class="container form-container" data-animate="fade-in-up" data-delay="400">
            <label for="customer_name">Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
            
            <label for="image">Select image to upload:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            
            <button type="button" id="continueBtn" onclick="goToDoodle()"><i class="fas fa-upload"></i> Upload and Continue</button>
        </form>

    
    <script src="js/animations.js"></script>
    <script src="js/index.js"></script>
</body>
</html>