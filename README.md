# Spotlight - Interactive Image Template Overlay Application

A real-time photo booth application that allows users to upload photos, draw/doodle on them, and generate branded spotlight images with custom templates and text overlays.

## Features

### 🎨 Core Functionality
- **Image Upload & Processing** - Upload photos with automatic resizing and cropping to match template dimensions
- **Interactive Doodle Canvas** - Draw on uploaded images using PixiJS with multiple colors, brush sizes, and eraser tool
- **Template Overlay** - Automatic overlay of transparent PNG template on user photos
- **Custom Text** - Dynamic "STARRING [Name]" text generation with Montserrat Regular font (36pt)
- **Real-time Gallery** - Live gallery updates using Pusher WebSockets with toast notifications
- **QR Code Sharing** - Generate QR codes with loading placeholders for easy image sharing
- **Print Functionality** - One-click printing with optimized layout and auto-close
- **Modern UI** - Dark glass morphism design with smooth entry animations
- **Pagination** - Gallery displays 10 images per page (5 columns × 2 rows) without scrolling

### 🎨 Theme System
- **Unified Design Language** - Consistent dark glass morphism across all pages
- **CSS Variables** - Easy theme customization through centralized variables
- **Responsive Design** - Mobile-first design that works on all devices
- **Animation System** - Reusable entry animations (fadeInUp, fadeInDown, fadeInLeft, fadeInRight, fadeInScale, slideInUpCentered)
- **Modern Aesthetics** - Dark translucent cards with backdrop blur, subtle borders, and refined shadows

## Technical Stack

### Backend
- **PHP 7.4+** - Server-side processing
- **GD Library** - Image manipulation and composition
- **Composer** - Dependency management
- **Pusher PHP SDK** - Real-time notifications

### Frontend
- **PixiJS v8.13.2** - Hardware-accelerated 2D canvas for drawing
- **Pusher JS Client** - Real-time updates with toast notifications
- **Vanilla JavaScript** - No framework dependencies
- **CSS3** - Modern styling with glass morphism, gradients, and animations
- **Montserrat Font** - Professional typography (Regular and SemiBold)

### Libraries & APIs
- **QR Code API** - QR code generation for sharing
- **Web Audio API** - Notification sounds

## Installation

### Prerequisites
```bash
- PHP 7.4 or higher
- GD Library enabled
- Composer
- Web server (Apache/Nginx)
```

### Setup Steps

1. **Clone the repository**
```bash
cd /path/to/webserver/htdocs
git clone [repository-url] spotlight
cd spotlight
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure Pusher (Optional)**
   - Sign up at [pusher.com](https://pusher.com)
   - Copy your credentials
   - Edit `config.php`:
```php
$pusherConfig = [
    'app_id' => 'your_app_id',
    'key' => 'your_key',
    'secret' => 'your_secret',
    'cluster' => 'your_cluster'
];
```

4. **Set up directories**
```bash
mkdir -p output samples
chmod 777 output samples
```

5. **Add your template**
   - Place your PNG template in `template/template.png`
   - Recommended size: **1200x1800 pixels** (2:3 aspect ratio)
   - Must have transparent areas for overlay

6. **Add custom font (Optional)**
   - Place your font file in `font/` directory
   - Update `$fontPath` in `process.php` if needed

## File Structure

```
spotlight/
├── css/
│   ├── styles.css          # Main centralized styles with CSS variables & animations
│   ├── doodle.css          # Doodle page with loading screen & progress bar
│   ├── gallery.css         # Gallery grid (5×2 layout), modal, and cards
│   ├── preview.css         # Preview page with print functionality
│   ├── view.css            # Image view page styles
│   └── share.css           # QR share page with modern card design
├── js/
│   ├── animations.js       # Reusable animation initialization system
│   ├── index.js            # Upload form handling
│   ├── doodle.js           # PixiJS drawing application with loading states
│   ├── gallery.js          # QR modal, print functions, and share
│   ├── gallery-pusher.js   # Real-time update helpers with toast system
│   ├── preview.js          # Print functionality
│   ├── view.js             # Image view and print
│   └── share.js            # Clipboard operations
├── font/
│   ├── Montserrat-Regular.ttf    # Body text and overlays (36pt)
│   └── Montserrat-SemiBold.ttf   # Headings and emphasis
├── template/
│   └── template.png        # Main overlay template (1200x1800)
├── output/                 # Generated images (auto-created)
├── samples/                # Sample gallery images
├── vendor/                 # Composer dependencies
├── config.php              # Pusher configuration
├── pusher_helper.php       # Pusher connection helper class
├── index.php               # Upload page with animations
├── doodle.php              # Drawing canvas with progress bar
├── process.php             # Image processing (no shadow, 36pt font)
├── preview.php             # Preview page with print button
├── gallery.php             # Gallery (10 per page, 5×2 grid, no scroll)
├── view.php                # Single image view
├── share.php               # QR code sharing with modern modal
└── composer.json           # Dependencies
```

## System Architecture

```mermaid
graph TB
    subgraph "Client Browser"
        A[index.php<br/>Upload Page] --> B[doodle.php<br/>Drawing Canvas]
        B --> C{User Action}
        C -->|Draw & Save| D[PixiJS Canvas]
        C -->|Skip Drawing| E[Direct Upload]
    end
    
    subgraph "Server Processing"
        D --> F[process.php]
        E --> F
        F --> G[Load Template<br/>1200x1800]
        G --> H[Resize/Crop<br/>User Image]
        H --> I[Overlay Template]
        I --> J[Add Text<br/>STARRING NAME]
        J --> K[Save to output/]
    end
    
    subgraph "Real-time System"
        K --> L[Pusher Notification]
        L --> M[gallery.php<br/>Live Update]
    end
    
    subgraph "Sharing & Output"
        M --> N[view.php<br/>Single Image]
        M --> O[share.php<br/>QR Code]
        N --> P[Print]
        O --> Q[Share URL]
    end
    
    style A fill:#eb3349
    style B fill:#f45c43
    style F fill:#2e1619
    style M fill:#1a0a0e
```

## Data Flow Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant I as index.php
    participant D as doodle.php
    participant P as process.php
    participant PS as Pusher
    participant G as gallery.php
    
    U->>I: Enter name & upload image
    I->>I: Store in sessionStorage
    I->>D: Redirect with data
    
    D->>D: Initialize PixiJS canvas
    D->>D: Load image from sessionStorage
    U->>D: Draw/Doodle (optional)
    
    D->>P: Submit image + name
    P->>P: Load template (1200x1800)
    P->>P: Resize/crop user image
    P->>P: Overlay template
    P->>P: Add "STARRING NAME" text
    P->>P: Save to output/
    
    P->>PS: Trigger 'new-image' event
    PS-->>G: WebSocket push
    G->>G: Add image to gallery (no refresh)
    G->>G: Show toast notification
    
    P->>I: Redirect with success
    I->>U: Show result modal
```

## Image Processing Flow

```mermaid
flowchart LR
    A[Upload Image] --> B{Check<br/>Aspect Ratio}
    B -->|Wider than 2:3| C[Fit to Height<br/>Crop Sides]
    B -->|Taller than 2:3| D[Fit to Width<br/>Crop Top/Bottom]
    B -->|Exact 2:3| E[No Crop Needed]
    
    C --> F[Center Crop]
    D --> F
    E --> F
    
    F --> G[Result: 1200x1800]
    G --> H[Overlay Template]
    H --> I[Add Text Layer]
    I --> J[Final Image]
    
    style A fill:#eb3349
    style J fill:#2e1619
```

## Component Architecture

```mermaid
graph LR
    subgraph "Frontend (JS)"
        A[index.js<br/>Upload Handler]
        B[doodle.js<br/>PixiJS Drawing]
        C[gallery.js<br/>Modal & Print]
        D[gallery-pusher.js<br/>Real-time Updates]
        E[view.js<br/>Print Function]
        F[share.js<br/>Clipboard Copy]
    end
    
    subgraph "Styling (CSS)"
        G[styles.css<br/>Global + Variables]
        H[doodle.css<br/>Canvas Styles]
        I[gallery.css<br/>Grid Layout]
        J[view.css<br/>Image Display]
        K[share.css<br/>QR Modal]
    end
    
    subgraph "Backend (PHP)"
        L[index.php]
        M[doodle.php]
        N[process.php<br/>Image Processing]
        O[gallery.php<br/>+ Pusher Client]
        P[view.php]
        Q[share.php]
        R[config.php<br/>Pusher Config]
    end
    
    A --> L
    B --> M
    C --> O
    D --> O
    E --> P
    F --> Q
    
    G --> L
    G --> M
    H --> M
    I --> O
    J --> P
    K --> Q
    
    N --> R
    O --> R
```

## Usage Flow

### 1. Upload Image
- User enters their name
- Uploads a photo (JPEG, PNG, or GIF)
- Image is stored in sessionStorage
- Redirects to doodle page

### 2. Doodle (Optional)
- PixiJS canvas loads the uploaded image with elegant progress bar
- User can draw with 4 color options (White, Red, Green, Blue)
- Brush size adjustment and eraser tool
- Clear button to remove all drawings
- Loading screen with pulsing logo during canvas initialization
- Save button to process the image

### 3. Image Processing
**Process.php workflow:**
1. Load template to get target dimensions (1200x1800)
2. Load uploaded image
3. Calculate aspect ratio and resize/crop to match template
4. Overlay template PNG on user image
5. Add "STARRING [NAME]" text at bottom using Montserrat Regular (36pt, no shadow)
6. Save to output directory with unique timestamp filename
7. Trigger Pusher notification for real-time gallery update
8. Redirect to preview page with result

### 4. Gallery (Redesigned)
- Displays 10 images per page in 5×2 grid layout
- No page scrolling - fits viewport perfectly
- Real-time updates when new images are created
- Modern dark glass morphism cards with hover effects
- QR code modal with loading placeholder
- Print and share buttons for each image
- Pagination with total image count display
- Toast notifications with modern design

## Image Processing Logic

### Aspect Ratio Handling
All images are resized to match the template dimensions while preserving quality:

```php
Template: 1200x1800 (2:3 aspect ratio)

If uploaded image is wider:
  - Fit to height
  - Crop sides (center crop)

If uploaded image is taller:
  - Fit to width  
  - Crop top/bottom (center crop)
```

### Text Overlay
- Font: Montserrat Regular (font/Montserrat-Regular.ttf)
- Customer name: 36pt (uppercase)
- Position: Centered at bottom
- Colors: White text (no shadow for clean appearance)
- Clean, modern typography matching UI design

## Print Specifications

### Default Settings
- **Output Size**: 1200x1800 pixels
- **Aspect Ratio**: 2:3 (standard photo print ratio)
- **Print Size**: Adapts to printer settings
- **Recommended Paper**: 4x6" photo paper, A4, or Letter
- **Margins**: None (full bleed)

### Print Behavior
- `max-width: 100%` - Scales to paper width
- `height: auto` - Maintains aspect ratio
- No forced margins in print mode

## Real-time Updates (Pusher)

### Configuration
Edit `config.php`:
```php
$pusherConfig = [
    'app_id' => 'your_app_id',
    'key' => 'your_key',
    'secret' => 'your_secret',
    'cluster' => 'mt1', // your cluster
    'use_tls' => true
];

$pusherChannel = 'spotlight-gallery';
$pusherEvent = 'new-image';
```

### Features
- Live gallery updates without page refresh
- Toast notifications with dark glass morphism design
- Animated loading placeholders
- Connection status indicators
- No-scroll pagination (10 images per page)
- Modern notification system with progress bars

## Customization

### Theme Colors
Edit `css/styles.css` root variables:
```css
:root {
    --primary-red: #eb3349;
    --secondary-red: #f45c43;
    --bg-overlay: rgba(26, 26, 46, 0.4);  /* Glass morphism background */
    --border-light: rgba(255, 255, 255, 0.1);
    /* ... more variables ... */
}
```

### Animations
Add custom animations in `css/styles.css`:
```css
@keyframes yourAnimation {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
```

Then use with `data-animate` attribute:
```html
<div data-animate="fade-in-up" data-delay="200">Content</div>
```

### Template
Replace `template/template.png` with your custom template:
- Must be PNG with transparency
- Recommended: 1200x1800 pixels
- Transparent areas will show user's photo
- Opaque areas will overlay on photo

### Drawing Colors
Edit `doodle.php` to add more color options:
```html
<div class="color-btn" style="background: #ff00ff;" 
     onclick="setColor('#ff00ff', event)" title="Magenta"></div>
```

### Font
Replace font files in `font/` directory and update `process.php`:
```php
$fontPath = __DIR__ . '/font/Montserrat-Regular.ttf';
$nameFontSize = 36; // Font size in points
```

### Gallery Layout
Edit `css/gallery.css` to change grid layout:
```css
.gallery-grid {
    grid-template-columns: repeat(5, 1fr);  /* 5 columns */
    grid-template-rows: repeat(2, 1fr);     /* 2 rows */
}
```

### Loading Screen
Customize doodle loading in `css/doodle.css`:
```css
.loading-screen {
    /* Customize background, logo, progress bar */
}
```

## Troubleshooting

### Images not processing
- Check PHP GD library is installed: `php -m | grep gd`
- Verify write permissions on `output/` directory
- Check PHP error logs

### Pusher not working
- Verify credentials in `config.php`
- Check browser console for connection errors
- Enable debug mode in Pusher dashboard

### Drawing not working
- Check browser console for JavaScript errors
- Verify PixiJS CDN is accessible
- Clear browser cache

### Print issues
- Ensure browser allows popups
- Check printer paper size settings
- Try different browsers (Chrome recommended)

## Browser Support

- **Chrome/Edge**: Full support ✅
- **Firefox**: Full support ✅
- **Safari**: Full support (iOS 12+) ✅
- **Mobile Browsers**: Touch drawing supported ✅

## Performance Notes

- Images are processed server-side (no client-side resize)
- PixiJS uses WebGL hardware acceleration with fallback to canvas
- Pusher uses WebSockets for minimal overhead
- Gallery pagination limits DOM nodes (10 per page, no scrolling)
- Lazy loading for gallery images
- Modern backdrop-filter with hardware acceleration
- Optimized animations using CSS transforms and opacity
- Reusable animation system reduces JavaScript overhead

## Security Considerations

- File upload validation by MIME type
- Output files have random names
- No direct file execution possible
- Pusher credentials should be kept secret
- Consider adding rate limiting for production

## Development

### Debugging
Enable PHP error logging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Testing Pusher
Check PHP error logs for Pusher debug output:
```bash
tail -f /var/log/apache2/error.log
```

## License

[Your License Here]

## Credits

- **PixiJS** - 2D rendering engine
- **Pusher** - Real-time communication
- **QR Server API** - QR code generation

## Support

For issues or questions, please open an issue on the repository.

---

**Version**: 3.0  
**Last Updated**: November 19, 2025  
**Major Updates**:
- Redesigned UI with dark glass morphism theme
- Reusable animation system across all pages
- Gallery pagination (5×2 grid, no scroll)
- Modern loading states and progress bars
- Improved typography with Montserrat font family
- Enhanced modal designs with loading placeholders
- Toast notification system with modern styling
- Print functionality improvements
- Consistent design language across all pages

---
