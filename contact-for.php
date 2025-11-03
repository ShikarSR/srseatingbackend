<?php
// Allow requests from the React frontend (localhost:3000)
header("Access-Control-Allow-Origin: http://localhost:5173");  // Allow React app to access this API
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");    // Allow POST, GET, and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type");           // Allow Content-Type header for JSON requests

// Handle OPTIONS request (preflight request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Respond with status 200 to allow the preflight request to pass
    http_response_code(200);
    exit;
}

// Your existing code goes here...
header('Content-Type: application/json');

// Include database connection
include('config.php');

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"));

// Sanitize input data to prevent SQL injection
$name = $conn->real_escape_string($data->name);
$email = $conn->real_escape_string($data->password);
$phone = $conn->real_escape_string($data->phone);
$companyname = $conn->real_escape_string($data->companyname);
$message = $conn->real_escape_string($data->message);
$choosesolution = $conn->real_escape_string($data->choosesolution);

// SQL query to insert form data into the database
$query = "INSERT INTO contacts (name, email, phone, companyname, message, choosesolution) 
          VALUES ('$name', '$email', '$phone', '$companyname', '$message', '$choosesolution')";

// Execute the query and return a response
if ($conn->query($query) === TRUE) {
    echo json_encode(["message" => "Form submission successful"]);
} else {
    echo json_encode(["error" => "Failed to insert data"]);
}

// Close the database connection
$conn->close();
?>
