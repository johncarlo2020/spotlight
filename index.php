<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image and Customer Name</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        
        <div class="text-center mb-30">
            <a href="gallery.php" class="gallery-link">📸 View Gallery</a>
        </div>
        
        <form id="uploadForm">
            <label for="customer_name">Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
            
            <label for="image">Select image to upload:</label>
            <input type="file" name="image" id="image" accept="image/*" required>
            
            <input type="button" id="continueBtn" value="Upload and Continue" onclick="goToDoodle()">
        </form>
    </div>
    
    <script src="js/index.js"></script>
</body>
</html>