<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Selector - Spotlight</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            padding: 20px;
            color: #ffffff;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            background: rgba(26, 26, 46, 0.4);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo img {
            max-width: 300px;
            height: auto;
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.2));
        }
        
        h1 {
            text-align: center;
            color: #fff;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 800;
            letter-spacing: 2px;
        }
        
        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        
        .theme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .theme-card {
            background: rgba(30, 30, 50, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .theme-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .theme-card.selected {
            border-color: #667eea;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.4);
        }
        
        .theme-preview {
            height: 150px;
            border-radius: 12px;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }
        
        .theme-name {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #fff;
        }
        
        .theme-description {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }
        
        .color-palette {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }
        
        .color-swatch {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .template-section {
            background: rgba(30, 30, 50, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .template-section h2 {
            color: #fff;
            font-size: 1.8rem;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .template-images {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .template-image {
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .template-image:hover {
            border-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.05);
        }
        
        .template-image.selected {
            border-color: #667eea;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
        }
        
        .template-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        
        .template-name {
            text-align: center;
            margin-top: 10px;
            font-weight: 600;
            color: #fff;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c92f5 0%, #8b5cb8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .theme-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Spotlight Logo">
        </div>
        
        <h1>Choose Your Theme</h1>
        <p class="subtitle">Select a color theme for your Spotlight app</p>
        
        <!-- Template Images Section -->
        <div class="template-section">
            <h2>📸 Use Template Image</h2>
            <p style="text-align: center; color: rgba(255, 255, 255, 0.7); margin-bottom: 20px;">
                Click on a template to extract its color scheme
            </p>
            <div class="template-images">
                <div class="template-image" data-template="template.png" onclick="selectTemplate('template.png')">
                    <img src="template/template.png" alt="Template">
                    <div class="template-name">Template</div>
                </div>
                <div class="template-image" data-template="template1.png" onclick="selectTemplate('template1.png')">
                    <img src="template/template1.png" alt="Template 1">
                    <div class="template-name">Template 1</div>
                </div>
                <div class="template-image" data-template="templateOLD.png" onclick="selectTemplate('templateOLD.png')">
                    <img src="template/templateOLD.png" alt="Template OLD">
                    <div class="template-name">Template OLD</div>
                </div>
            </div>
        </div>
        
        <!-- Predefined Themes -->
        <h2 style="text-align: center; color: #fff; margin-bottom: 20px;">🎨 Or Choose a Preset Theme</h2>
        <div class="theme-grid">
            <!-- Current Theme (Purple/Blue) -->
            <div class="theme-card selected" data-theme="purple-blue" onclick="selectTheme('purple-blue')">
                <div class="theme-preview" style="background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);"></div>
                <div class="theme-name">Purple Blue (Current)</div>
                <div class="theme-description">Dark elegant theme with purple and blue gradients</div>
                <div class="color-palette">
                    <div class="color-swatch" style="background: #667eea;"></div>
                    <div class="color-swatch" style="background: #764ba2;"></div>
                    <div class="color-swatch" style="background: #1a1a2e;"></div>
                    <div class="color-swatch" style="background: #16213e;"></div>
                </div>
            </div>
            
            <!-- Orange/Yellow Theme -->
            <div class="theme-card" data-theme="orange-yellow" onclick="selectTheme('orange-yellow')">
                <div class="theme-preview" style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);"></div>
                <div class="theme-name">Sunset Orange</div>
                <div class="theme-description">Warm and vibrant orange to yellow gradient</div>
                <div class="color-palette">
                    <div class="color-swatch" style="background: #ff6b6b;"></div>
                    <div class="color-swatch" style="background: #feca57;"></div>
                    <div class="color-swatch" style="background: #2c3e50;"></div>
                    <div class="color-swatch" style="background: #34495e;"></div>
                </div>
            </div>
            
            <!-- Green/Teal Theme -->
            <div class="theme-card" data-theme="green-teal" onclick="selectTheme('green-teal')">
                <div class="theme-preview" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);"></div>
                <div class="theme-name">Ocean Teal</div>
                <div class="theme-description">Fresh and calming teal to green gradient</div>
                <div class="color-palette">
                    <div class="color-swatch" style="background: #11998e;"></div>
                    <div class="color-swatch" style="background: #38ef7d;"></div>
                    <div class="color-swatch" style="background: #0a3d38;"></div>
                    <div class="color-swatch" style="background: #1a5246;"></div>
                </div>
            </div>
            
            <!-- Red/Pink Theme -->
            <div class="theme-card" data-theme="red-pink" onclick="selectTheme('red-pink')">
                <div class="theme-preview" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);"></div>
                <div class="theme-name">Fiery Red</div>
                <div class="theme-description">Bold and energetic red gradient</div>
                <div class="color-palette">
                    <div class="color-swatch" style="background: #eb3349;"></div>
                    <div class="color-swatch" style="background: #f45c43;"></div>
                    <div class="color-swatch" style="background: #1a0a0e;"></div>
                    <div class="color-swatch" style="background: #2e1619;"></div>
                </div>
            </div>
            
            <!-- Blue/Cyan Theme -->
            <div class="theme-card" data-theme="blue-cyan" onclick="selectTheme('blue-cyan')">
                <div class="theme-preview" style="background: linear-gradient(135deg, #0575e6 0%, #00f2fe 100%);"></div>
                <div class="theme-name">Electric Blue</div>
                <div class="theme-description">Modern and tech-inspired blue gradient</div>
                <div class="color-palette">
                    <div class="color-swatch" style="background: #0575e6;"></div>
                    <div class="color-swatch" style="background: #00f2fe;"></div>
                    <div class="color-swatch" style="background: #0a1929;"></div>
                    <div class="color-swatch" style="background: #132f4c;"></div>
                </div>
            </div>
            
            <!-- Pink/Purple Theme -->
            <div class="theme-card" data-theme="pink-purple" onclick="selectTheme('pink-purple')">
                <div class="theme-preview" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <div class="theme-name">Rose Pink</div>
                <div class="theme-description">Romantic and elegant pink gradient</div>
                <div class="color-palette">
                    <div class="color-swatch" style="background: #f093fb;"></div>
                    <div class="color-swatch" style="background: #f5576c;"></div>
                    <div class="color-swatch" style="background: #1a0a14;"></div>
                    <div class="color-swatch" style="background: #2e1627;"></div>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button class="btn btn-secondary" onclick="window.location.href='index.php'">Cancel</button>
            <button class="btn btn-primary" onclick="applyTheme()">Apply Theme</button>
        </div>
    </div>
    
    <script>
        let selectedTheme = 'purple-blue';
        let selectedTemplate = null;
        
        function selectTheme(theme) {
            selectedTheme = theme;
            selectedTemplate = null;
            
            // Update UI
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`[data-theme="${theme}"]`).classList.add('selected');
            
            // Deselect templates
            document.querySelectorAll('.template-image').forEach(img => {
                img.classList.remove('selected');
            });
        }
        
        function selectTemplate(template) {
            selectedTemplate = template;
            selectedTheme = null;
            
            // Update UI
            document.querySelectorAll('.template-image').forEach(img => {
                img.classList.remove('selected');
            });
            document.querySelector(`[data-template="${template}"]`).classList.add('selected');
            
            // Deselect theme cards
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('selected');
            });
        }
        
        function applyTheme() {
            if (selectedTemplate) {
                // Store template selection
                localStorage.setItem('selectedTheme', 'template');
                localStorage.setItem('templateImage', selectedTemplate);
                alert(`Template "${selectedTemplate}" will be analyzed for color extraction. This feature requires backend implementation.`);
            } else if (selectedTheme) {
                // Store theme selection
                localStorage.setItem('selectedTheme', selectedTheme);
                localStorage.removeItem('templateImage');
            }
            
            // Redirect to apply theme
            window.location.href = 'apply-theme.php?theme=' + (selectedTemplate ? 'template&file=' + selectedTemplate : selectedTheme);
        }
    </script>
</body>
</html>
