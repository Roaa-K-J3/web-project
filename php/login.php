<?php
session_start();

include_once 'db_connection.php';

// Get the request body as POST data
$email = $_POST['email'];
$password = $_POST['password'];

// Validate input fields
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Please provide email and password.'));
    exit;
}

// Check if user exists in DB
$sql = "SELECT * FROM user WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(array('message' => 'Email or password is incorrect.'));
    exit;
}

// Get user data from DB
$user = $result->fetch_assoc();

// Check if password is correct
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(array('message' => 'Email or password is incorrect.'));
    exit;
}

// Add user to session
$_SESSION['user_id'] = $user['id'];

// Return user data as JSON response
http_response_code(200);
echo json_encode(array(
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'avatar' => $user['avatar'],
    'is_admin' => $user['is_admin'],
));

$stmt->close();
$conn->close();
