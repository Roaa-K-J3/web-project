<?php
include_once 'auth.php';

// Include the database connection file
require_once 'db_connection.php';

// Select all users from the database
$sql = "SELECT * FROM user";
$result = $conn->query($sql);

// Check if there are any users
$users = array();
while ($row = $result->fetch_assoc()) {
    $user = array(
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'avatar' => $row['avatar']
    );
    array_push($users, $user);
}
// Return the array of users as a JSON response
header('Content-Type: application/json');
echo json_encode($users);


// Close the database connection
$conn->close();

