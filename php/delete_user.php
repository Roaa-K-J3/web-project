<?php
include_once 'auth.php';

// Include the database connection file
require_once 'db_connection.php';

// get logged in user data;
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// if user not admin exit
if ($user['is_admin'] == 0) {
    http_response_code(403);
    echo json_encode(array('message' => 'You do not have permission to delete this user.'));
    exit;
}
// Get the user ID from the request parameter
$user_id = $_GET['id'];

// Prepare and execute the SQL query to delete the user
$sql = "DELETE FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Return a success response
http_response_code(200);
