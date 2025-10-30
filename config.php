<?php
/** ===== AiSensy + OTP config (edit these carefully) ===== */

// Your real AiSensy API key
define('AISENSY_API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY4ZjA5YjNlNjAxZDg4N2NiZDFkMzExZSIsIm5hbWUiOiJTUiBTZWF0aWluZyBQcml2YXRlIExpbWlpdGVkIiwiYXBwTmFtZSI6IkFpU2Vuc3kiLCJjbGllbnRJZCI6IjY4ZjA5YjNlNjAxZDg4N2NiZDFkMzExOSIsImFjdGl2ZVBsYW4iOiJGUkVFX0ZPUkVWRVIiLCJpYXQiOjE3NjA1OTg4NDZ9.e9R8rzjXSK6elyTzNLdROL2pTmdUCl76XMv-TB-beek');

// AiSensy API endpoint
define('AISENSY_ENDPOINT', 'https://backend.aisensy.com/campaign/t1/api/v2');
// define('AISENSY_ENDPOINT', 'https://backend.aisensy.com/api/sendCampaign/');

// MUST match your AiSensy **account display name** EXACTLY
// (use the typo if that’s what your dashboard shows!)
define('AISENSY_USERNAME', 'SR Seatiing Private Limiited');

// MUST match the exact Campaign/Template you’re using
define('AISENSY_CAMPAIGN', 'WhatsApp OTP Verification1');

/** ===== OTP policy ===== */
define('OTP_LENGTH', 6);
define('OTP_TTL_SECONDS', 5 * 60);
define('OTP_MAX_ATTEMPTS', 5);

/** ===== File-based session storage ===== */
define('OTP_STORE_DIR', __DIR__ . '/.otp_sessions');
if (!is_dir(OTP_STORE_DIR)) { @mkdir(OTP_STORE_DIR, 0775, true); }

/** ===== Helpers ===== */
function generate_otp(): string {
  $s=''; for($i=0;$i<OTP_LENGTH;$i++) $s.=random_int(0,9); return $s;
}
function normalize_phone_for_aisensy(string $phone): string {
  return preg_replace('/\D+/', '', $phone); // digits only, keep country code
}
function uuid4(): string {
  return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0,0xffff), mt_rand(0,0xffff),
    mt_rand(0,0xffff),
    mt_rand(0,0x0fff)|0x4000,
    mt_rand(0,0x3fff)|0x8000,
    mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
  );
}
function otp_session_path(string $id): string {
  return OTP_STORE_DIR.'/'.preg_replace('/[^a-zA-Z0-9\-]/','',$id).'.json';
}
function otp_session_create(string $phoneDigits, string $otp): string {
  $id = uuid4();
  $data = [
    'id'=>$id,'phone'=>$phoneDigits,
    'otp_hash'=>password_hash($otp,PASSWORD_DEFAULT),
    'expires'=>time()+OTP_TTL_SECONDS,
    'attempts'=>0,'verified'=>false,'created'=>time(),
  ];
  file_put_contents(otp_session_path($id), json_encode($data));
  return $id;
}
function otp_session_load(string $id): ?array {
  $f=otp_session_path($id); if(!is_file($f)) return null;
  $d=json_decode(file_get_contents($f),true); return is_array($d)?$d:null;
}
function otp_session_save(array $s): void {
  if(!empty($s['id'])) file_put_contents(otp_session_path($s['id']), json_encode($s));
}
function otp_session_delete(string $id): void { @unlink(otp_session_path($id)); }

?>