<?php
require __DIR__ . '/cors.php';
require __DIR__ . '/config.php';

try {
  $input = json_decode(file_get_contents('php://input'), true) ?: [];
  
  // Validation
  $name    = trim($input['name'] ?? '');
  $company = trim($input['companyname'] ?? '');
  $email   = trim($input['email'] ?? '');
  $phone   = trim($input['phone'] ?? '');

  if ($name === '') throw new Exception('Name is required');
  if ($company === '') throw new Exception('Company is required');
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Valid email is required');
  if ($phone === '') throw new Exception('Phone is required');

  // Normalize phone
  $destination = normalize_phone_for_aisensy($phone);
  if ($destination === '' || strlen($destination) < 10) throw new Exception('Valid phone with country code is required');

  // Generate OTP
  $otp = generate_otp();
  $sessionId = otp_session_create($destination, $otp);



  // ✅ FIXED: Correct AiSensy payload structure
  // Construct AiSensy payload (using tested campaign structure)
$payload = [
  'apiKey' => AISENSY_API_KEY,
  'campaignName' => AISENSY_CAMPAIGN,
  'destination' => $destination,
  'userName' => AISENSY_USERNAME,
  'source' => 'new-landing-page form',
  'templateParams' => [$otp],
  'media' => new stdClass(),
  'buttons' => [
    [
      'type' => 'button',
      'sub_type' => 'url',
      'index' => 0,
      'parameters' => [
        [
          'type' => 'text',
          'text' => $otp
        ]
      ]
    ]
  ],
  'carouselCards' => [],
  'location' => new stdClass(),
  'attributes' => new stdClass(),
  'paramsFallbackValue' => [
    'FirstName' => 'user'
  ]
];


// $payload = [
//   'apiKey'         => AISENSY_API_KEY,
//   'campaignName'   => AISENSY_CAMPAIGN,
//   'destination'    => $destination,
//   'userName'       => AISENSY_USERNAME,
//   'source'         => 'new-landing-page form',
//   'templateParams' => [$otp], // must match placeholders in AiSensy template
//   'media'          => new stdClass(),
//   'buttons'        => [],     // remove buttons if not defined in template
//   'carouselCards'  => [],
//   'location'       => new stdClass(),
//   'attributes'     => new stdClass(),
//   'paramsFallbackValue' => ['FirstName' => 'user'],
// ];


  // Send to AiSensy
  // Send to AiSensy with detailed logging
$ch = curl_init(AISENSY_ENDPOINT);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_VERBOSE        => true, // Enable verbose output
]);

$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);

// Log detailed information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
curl_close($ch);

// Enhanced debug log
@file_put_contents(__DIR__ . '/.otp_sessions/aisensy_detailed.log',
    date('c') . "\n" .
    "URL: " . AISENSY_ENDPOINT . "\n" .
    "HTTP Status: $http\n" .
    "cURL Error: " . ($err ?: 'None') . "\n" .
    "Verbose: " . $verboseLog . "\n" .
    "Request: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n" .
    "Response: " . $resp . "\n\n",
    FILE_APPEND
);

  // Check response
  if ($http < 200 || $http >= 300 || $resp === false) {
    otp_session_delete($sessionId);
    throw new Exception('AiSensy transport failed: ' . ($err ?: "HTTP $http"));
  }

  $j = json_decode($resp, true);
  if (!is_array($j)) {
    otp_session_delete($sessionId);
    throw new Exception('AiSensy returned non-JSON: ' . substr($resp, 0, 250));
  }

  if (empty($j['success'])) {
    otp_session_delete($sessionId);
    $msg = $j['errorMessage'] ?? $j['message'] ?? 'Unknown AiSensy error';
    throw new Exception("AiSensy error: $msg");
  }

  echo json_encode([
    'success' => true,
    'session_id' => $sessionId,
    'submitted_message_id' => $j['submitted_message_id'] ?? null,
    'aisensy_response' => $j
  ]);

} catch (Exception $e) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage(),
  ]);
}
?>