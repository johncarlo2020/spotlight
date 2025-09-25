<?php
// Pusher Configuration
// Replace these with your actual Pusher credentials
$pusherConfig = [
    'app_id' => "1804777",
    'key' => "60de59064bcf7cfb6d63",
    'secret' => "a545f1f3ddea7427b33f",
    'cluster' => "ap1", // e.g., 'us2', 'eu', 'ap1'
    'use_tls' => true
];

// Channel and Event Names
$pusherChannel = 'gallery-updates';
$pusherEvent = 'new-image';
?>