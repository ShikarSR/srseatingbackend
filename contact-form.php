<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allowed origins list
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost',
    'http://127.0.0.1',
    'https://srseating.com',
    'https://www.srseating.com',
    'http://134.209.144.29',
];

// Get the origin of the request
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Check if origin is in the allowed list
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} 
// else {
//     header("Access-Control-Allow-Origin: https://srseating.com"); // default fallback
// }

header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

include('config.php'); // ✅ Using your config file

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

$name           = $conn->real_escape_string($data->name ?? '');
$email          = $conn->real_escape_string($data->email ?? '');
$phone          = $conn->real_escape_string($data->phone ?? '');
$companyname    = $conn->real_escape_string($data->companyname ?? '');
$message        = $conn->real_escape_string($data->message ?? '');
$choosesolution = $conn->real_escape_string($data->choosesolution ?? '');

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(["error" => "Required fields missing"]);
    exit;
}

$query = "INSERT INTO contacts (name, email, phone, companyname, message, choosesolution)
          VALUES ('$name', '$email', '$phone', '$companyname', '$message', '$choosesolution')";

file_put_contents(__DIR__ . '/debug_contact.txt', $query . PHP_EOL, FILE_APPEND);


if ($conn->query($query) === TRUE) {
    echo json_encode(["message" => "Form submission successful"]);
} else {
    echo json_encode(["error" => "Database insert failed: " . $conn->error]);
}


$conn->close();
?>
