<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include('config.php');

$data = json_decode(file_get_contents("php://input"));

$username = $conn->real_escape_string($data->username);
$password = $conn->real_escape_string($data->password);

// Check if user already exists
$check = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($check);

if ($result && $result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "User already exists"]);
} else {
    $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if ($conn->query($query)) {
        echo json_encode(["success" => true, "message" => "Signup successful"]);
    } else {
        echo json_encode(["success" => false, "message" => "Signup failed"]);
    }
}

$conn->close();
?>
