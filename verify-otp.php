<?php
require __DIR__ . '/cors.php';
require __DIR__ . '/config.php';

try {
  $input     = json_decode(file_get_contents('php://input'), true) ?: [];
  $sessionId = trim($input['session_id'] ?? '');
  $otpInput  = trim($input['otp'] ?? '');

  if ($sessionId==='' || !preg_match('/^\d{6}$/', $otpInput)) throw new Exception('Invalid payload');

  $session = otp_session_load($sessionId);
  if (!$session) throw new Exception('Session not found');
  if (!empty($session['verified'])) throw new Exception('OTP already used');
  if (($session['expires'] ?? 0) < time()) throw new Exception('OTP expired');

  $session['attempts'] = (int)($session['attempts'] ?? 0);
  if ($session['attempts'] >= OTP_MAX_ATTEMPTS) throw new Exception('Too many attempts');

  if (!password_verify($otpInput, $session['otp_hash'] ?? '')) {
    $session['attempts']++;
    otp_session_save($session);
    throw new Exception('Invalid OTP');
  }

  $session['verified'] = true;
  otp_session_save($session);

  echo json_encode(['success'=>true]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

?>