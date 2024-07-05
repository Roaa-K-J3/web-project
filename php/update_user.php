<?php

include_once 'auth.php';

// Include DB connection file
require_once 'db_connection.php';

// Get the form data
$user_id = $_POST['userId'];
$name = $_POST['name'];
$email = $_POST['email'];
$password = isset($_POST['password']) ? $_POST['password'] : '';
$avatar = isset($_FILES['avatar']) ? $_FILES['avatar'] : '';

// get logged in user data;
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

# check if user is admin or owner of the account
if ($user['id'] != $_SESSION['user_id'] && $user['is_admin'] == 0) {
    http_response_code(403);
    echo json_encode(array('error' => 'You do not have permission to update this user.'));
    exit;
}

// Validate input fields
if (empty(trim($user_id)) || empty(trim($name)) || empty(trim($email))) {
    http_response_code(400);
    echo json_encode(array('message' => 'Please provide user id, name, and email.'));
    exit;
}

// Sanitize input fields
$user_id = filter_var(trim($user_id), FILTER_SANITIZE_NUMBER_INT);
$name = filter_var(trim($name), FILTER_SANITIZE_STRING);
$email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid email format.'));
    exit;
}

// Check if user exists in DB
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(array('message' => 'User not found.'));
    exit;
}

// Prepare the update query
$sql = "UPDATE user SET name = ?, email = ?";
$params = array($name, $email);

// Add password and/or avatar to the query if provided
if (!empty(trim($password))) {
    $sql .= ", password = ?";
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}

if (!empty($avatar)) {
    // Handle avatar upload
    // Save the image file to the server
    $image_filename = 'user_' . $user_id . rand(1, 1000000) . '.' . pathinfo($avatar['name'], PATHINFO_EXTENSION);
    $image_path = 'images/' . $image_filename;
    move_uploaded_file($avatar['tmp_name'], $image_path);
    $sql .= ", avatar = ?";
    $params[] = $image_path;
}

$sql .= " WHERE id = ?";
$params[] = $user_id;
// Execute the update query
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);

if ($stmt->execute()) {
    // Return a success response
    http_response_code(200);

    echo json_encode($user);
} else {
    // Return an error response
    http_response_code(500);
    echo json_encode(array('error' => 'User not updated.'));
}

$stmt->close();
$conn->close();
