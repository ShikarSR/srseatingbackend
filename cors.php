<?php
// $allowedOrigins = [
//   'http://localhost:5173','http://127.0.0.1:5173',
//   'http://localhost','http://127.0.0.1', 'https://malgar.shop', 'http://134.209.144.29',
// ];

$allowedOrigins = [
  'http://localhost:5173', 'http://127.0.0.1:5173',
  'http://localhost', 'http://127.0.0.1',
  'https://srseating.com', 'https://www.srseating.com',
  'http://134.209.144.29',
];



$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowedOrigins, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: content-type, Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

?>