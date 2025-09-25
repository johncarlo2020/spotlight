<?php
require_once 'config.php';

class PusherHelper {
    private $pusherConfig;
    private $channel;
    private $event;
    
    public function __construct() {
        error_log("=== PusherHelper constructor called ===");
        global $pusherConfig, $pusherChannel, $pusherEvent;
        
        error_log("Pusher config loaded: " . (isset($pusherConfig) ? 'YES' : 'NO'));
        error_log("Pusher channel loaded: " . (isset($pusherChannel) ? $pusherChannel : 'NOT SET'));
        error_log("Pusher event loaded: " . (isset($pusherEvent) ? $pusherEvent : 'NOT SET'));
        
        $this->pusherConfig = $pusherConfig;
        $this->channel = $pusherChannel;
        $this->event = $pusherEvent;
        
        if (isset($pusherConfig)) {
            error_log("Pusher app_id: " . (isset($pusherConfig['app_id']) ? $pusherConfig['app_id'] : 'NOT SET'));
            error_log("Pusher cluster: " . (isset($pusherConfig['cluster']) ? $pusherConfig['cluster'] : 'NOT SET'));
        }
        error_log("=== PusherHelper constructor finished ===");
    }
    
    /**
     * Send notification about new image using cURL (no external dependencies)
     */
    public function notifyNewImage($imageData) {
        // Only proceed if Pusher is configured
        if (empty($this->pusherConfig['app_id']) || $this->pusherConfig['app_id'] === 'your_app_id') {
            error_log('❌ Pusher not configured - skipping real-time notification');
            return false;
        }
        
        error_log("🔧 Pusher configured with app_id: " . $this->pusherConfig['app_id'] . ", cluster: " . $this->pusherConfig['cluster']);
        
        try {
            // Prepare the data
            $data = json_encode($imageData);
            $timestamp = time();
            $auth_version = '1.0';
            
            error_log("📤 Preparing to send data: " . $data);
            
            // Prepare query parameters for signing
            $query_params = [
                'auth_key' => $this->pusherConfig['key'],
                'auth_timestamp' => $timestamp,
                'auth_version' => $auth_version,
                'body_md5' => md5($data)
            ];
            
            // Sort parameters by key
            ksort($query_params);
            
            // Build query string
            $query_string = http_build_query($query_params);
            
            // Create string to sign
            $string_to_sign = "POST\n/apps/{$this->pusherConfig['app_id']}/events\n{$query_string}";
            
            // Generate signature
            $auth_signature = hash_hmac('sha256', $string_to_sign, $this->pusherConfig['secret']);
            
            // Add signature to query params
            $query_params['auth_signature'] = $auth_signature;
            
            // Prepare the payload
            $payload = [
                'name' => $this->event,
                'channel' => $this->channel,
                'data' => $data
            ];
            
            // Pusher API URL with query parameters
            $url = "https://api-{$this->pusherConfig['cluster']}.pusherapp.com/apps/{$this->pusherConfig['app_id']}/events?" . http_build_query($query_params);
            
            error_log("🌐 Pusher API URL: " . $url);
            error_log("📦 Payload: " . json_encode($payload));
            
            // cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            error_log("📡 HTTP Response Code: " . $httpCode);
            error_log("📄 Response Body: " . $response);
            
            if (curl_error($ch)) {
                error_log("❌ Pusher cURL error: " . curl_error($ch));
                curl_close($ch);
                return false;
            }
            
            curl_close($ch);
            
            if ($httpCode === 200) {
                error_log("Pusher notification sent successfully");
                return true;
            } else {
                error_log("Pusher API error: HTTP {$httpCode} - Response: {$response}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Pusher notification failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get Pusher client configuration for frontend
     */
    public function getClientConfig() {
        if (empty($this->pusherConfig['key']) || $this->pusherConfig['key'] === 'your_key') {
            return null;
        }
        
        return [
            'key' => $this->pusherConfig['key'],
            'cluster' => $this->pusherConfig['cluster'],
            'useTLS' => $this->pusherConfig['use_tls'],
            'channel' => $this->channel,
            'event' => $this->event
        ];
    }
}
?>