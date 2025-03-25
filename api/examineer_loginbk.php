<?php
session_start(); // Start the session to use session variables

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include('../connection.php'); // Include database connection

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Check if email and password are provided
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Email and Password are required"]);
    exit();
}

$email = $data['email']; // Email input
$password = $data['password']; // Get password entered by user

// ✅ Validate email format
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
    exit();
}

// ✅ Check if the email exists in the database
$sql = "SELECT * FROM examineer_login WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if the email exists in the database
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Check if the provided password matches the hashed password
    if (password_verify($password, $row['password'])) {
        // Password is correct, store the email in the session
        $_SESSION['user_email'] = $email;

        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user" => [
                "email" => $email
            ]
        ]);
    } else {
        // Password doesn't match
        echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
    }
} else {
    // Email does not exist, so insert the user login record
    $password_hashed = password_hash($password, PASSWORD_DEFAULT); // Hash the password

    $sql = "INSERT INTO examineer_login (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password_hashed);

    if ($stmt->execute()) {
        // Store the email in the session after successful registration
        $_SESSION['user_email'] = $email;

        echo json_encode([
            "status" => "success",
            "message" => "User login recorded successfully",
            "user" => [
                "email" => $email
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to insert login record"]);
    }
}

// Close connections
$stmt->close();
$conn->close();
?>
