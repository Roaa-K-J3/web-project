<?php
include_once 'auth.php';

require_once 'db_connection.php';

// Validate the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Error in connecting to the endpoint, please contact the developer.']);
    exit();
}

// Validate the request data
if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_FILES['image'])) {
    echo json_encode(['error' => 'You must provide name, email, password and image.']);
    exit();
}

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$is_admin = trim($_POST['is_admin']);
$image = $_FILES['image'];

// Validate the input data
if (empty($name) || empty($email) || empty($password) || $image['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'You must provide name, email, password and image.']);
    exit();
}

// Validate that the uploaded file is an image
$image_info = getimagesize($image['tmp_name']);
if (!$image_info || ($image_info[2] !== IMAGETYPE_JPEG && $image_info[2] !== IMAGETYPE_PNG)) {
    echo json_encode(['error' => 'You must provide a valid image.']);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
// Insert the user record into the database
$sql = "INSERT INTO user (name, email, password, is_admin) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $name, $email, $hashed_password, $is_admin);
$stmt->execute();
$user_id = $stmt->insert_id;
$stmt->close();


$dir = 'images';
// create new directory with 744 permissions if it does not exist yet
// owner will be the user/group the PHP script is run under
if (!file_exists($dir)) {
    mkdir($dir, 0755);
}

// Save the image file to the server
$image_filename = 'user_' . $user_id . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
$image_path = 'images/' . $image_filename;
if (move_uploaded_file($image['tmp_name'], $image_path)) {
    // Update the user record with the image filename
    $sql = "UPDATE user SET avatar = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $image_path, $user_id);
    $stmt->execute();
    $stmt->close();
// Return a success response
    http_response_code(200);
    echo json_encode(['message' => 'User created successfully.']);
} else {
    echo json_encode(['error' => 'Error creating User.']);
}
// Close the database connection
$conn->close();
