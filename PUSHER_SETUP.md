# Pusher Real-Time Updates Setup

This guide will help you set up Pusher for real-time gallery updates.

## Step 1: Create Pusher Account
1. Go to [pusher.com](https://pusher.com) and create a free account
2. Create a new app in your Pusher dashboard
3. Note down your app credentials

## Step 2: Configure Credentials
1. Open `config.php` in your spotlight directory
2. Replace the placeholder values with your actual Pusher credentials:

```php
$pusherConfig = [
    'app_id' => 'your_actual_app_id',        // e.g., '1234567'
    'key' => 'your_actual_key',              // e.g., 'abcd1234efgh5678'
    'secret' => 'your_actual_secret',        // e.g., 'xyz789abc123def456'
    'cluster' => 'your_actual_cluster',      // e.g., 'us2', 'eu', 'ap1'
    'use_tls' => true
];
```

## Step 3: Test the Setup
1. Open the gallery page in one browser window
2. Upload a new image in another window
3. The gallery should automatically update with the new image
4. You should see a green notification: "✨ New image created for [Customer Name]!"

## Features
- **Real-time Updates**: Gallery updates instantly when new images are processed
- **Smooth Animations**: New images fade in with elegant animations
- **Notifications**: Visual notifications when new images are added
- **Auto-pagination**: New images are added to the current page view
- **Fallback**: Works normally even if Pusher is not configured

## Troubleshooting
- Check browser console for any JavaScript errors
- Verify Pusher credentials are correct
- Ensure your Pusher app has the correct cluster setting
- Check PHP error logs for server-side issues

## How It Works
1. When an image is processed in `process.php`, a Pusher event is triggered
2. The gallery page listens for these events using Pusher's JavaScript client
3. New images are dynamically added to the gallery without page refresh
4. Users see real-time updates and notifications