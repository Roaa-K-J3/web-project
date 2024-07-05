<?php
include_once 'auth.php';

require_once 'db_connection.php';

// Check if user ID was provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID not provided']);
    exit();
}

// Prepare and execute SQL query to get user by ID
$stmt = $conn->prepare('SELECT id, name, email, avatar FROM user WHERE id = ?');
$stmt->bind_param('i', $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

// Check if user was found
if ($result->num_rows == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit();
}

// Fetch user record and return as JSON
$user = $result->fetch_assoc();
echo json_encode($user);

// Close database connection
$stmt->close();
$conn->close();

