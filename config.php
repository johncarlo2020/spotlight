<?php
// Pusher Configuration
// Replace these with your actual Pusher credentials
$pusherConfig = [
    'app_id' => "2088742",
    'key' => "2548323bde39743a42ed",
    'secret' => "a99a6c2c9d4f3f5de64b",
    'cluster' => "us2", // e.g., 'us2', 'eu', 'ap1'
    'use_tls' => true
];

// Channel and Event Names
$pusherChannel = 'gallery-updates';
$pusherEvent = 'new-image';
?>