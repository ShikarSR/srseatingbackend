<?php
// test-aisensy-endpoints.php

// Your AiSensy API Key
$AISENSY_API_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY4ZjA5YjNlNjAxZDg4N2NiZDFkMzExZSIsIm5hbWUiOiJTUiBTZWF0aW5nIFByaXZhdGUgTGltaXRlZCIsImFwcE5hbWUiOiJBaVNlbnN5IiwiY2xpZW50SWQiOiI2OGYwOWIzZTYwMWQ4ODdjYmQxZDMxMTkiLCJhY3RpdmVQbGFuIjoiRlJFRV9GT1JFVkVSIiwiaWF0IjoxNzYwNTk4ODQ2fQ.e9R8rzjXSK6elyTzNLdROL2pTmdUCl76XMv-TB-beek";

// Test endpoints (all possible AISENSY endpoints)
$test_endpoints = [
    "https://backend.aisensy.com/api/sendOTP",
    "https://backend.aisensy.com/api/verifyOTP",
    "https://backend.aisensy.com/campaign/t1/api/v2",
    "https://api.aisensy.com/campaign/t1/api/send",
    "https://api.aisensy.com/v1/campaign/send",
    "https://api.aisensy.com/otp/send",
    "https://api.aisensy.com/otp/verify"
];

echo "<h2>Testing AISENSY Endpoints</h2>";

foreach ($test_endpoints as $endpoint) {
    echo "<h3>Testing: $endpoint</h3>";
    
    $test_payload = [
        "apiKey" => $AISENSY_API_KEY,
        "phoneNumber" => "919715245006" // Test phone number
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($test_payload),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => true, // Get headers too
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    
    curl_close($curl);

    echo "HTTP Code: <strong>$http_code</strong><br>";
    
    if ($curl_error) {
        echo "cURL Error: $curl_error<br>";
    } else {
        // Extract just the response body (remove headers)
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);
        
        echo "Response: " . htmlspecialchars($body) . "<br>";
    }
    
    echo "<hr>";
}
?>