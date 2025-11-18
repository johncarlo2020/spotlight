<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doodle on Your Image - Spotlight</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/8.13.2/pixi.min.js" integrity="sha512-rOMqai9NIPaFWpmvHUjdOa2dSuaaYo6i7E19jS1ZW9rjnrl4qAOOtsOeTk0QgIflFCe2ZYi/7or3CRF6VfBk9g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/doodle.css">
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        <h1>Draw & Customize</h1>
        <p class="subtitle">Draw on your image - Pick a color and start doodling!</p>
    </div>
    
    <div class="canvas-container">
        <div id="pixiCanvas"></div>
    </div>
    
    <div class="controls-left">
        <div class="control-panel">
            <div class="color-palette">
                <div class="color-btn active" style="background: #ffffff;" onclick="setColor('#ffffff', event)" title="White"></div>
                <div class="color-btn" style="background: #ff0000;" onclick="setColor('#ff0000', event)" title="Red"></div>
                <div class="color-btn" style="background: #00ff00;" onclick="setColor('#00ff00', event)" title="Green"></div>
                <div class="color-btn" style="background: #0000ff;" onclick="setColor('#0000ff', event)" title="Blue"></div>
            </div>
            <div class="button-group">
                <button class="action-btn btn-clear" onclick="clearCanvas()">Clear</button>
                <button class="action-btn btn-process" onclick="processImage()">Save</button>
            </div>
        </div>
    </div>
    
    <div class="controls-right">
    </div>
    
    <div id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h2 style="color: #fff;">Processing your image...</h2>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Confirm Action</h3>
            <p id="modalMessage">Are you sure?</p>
            <div class="modal-buttons">
                <button class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="modal-btn btn-confirm" onclick="confirmAction()">Confirm</button>
            </div>
        </div>
    </div>
    
    <script src="js/doodle.js"></script>
</body>
</html>
