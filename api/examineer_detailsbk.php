<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include('../connection.php'); // Database connection

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Check if required fields are provided
if (
    !isset($data['name']) || !isset($data['contact_number']) || !isset($data['designation']) ||
    !isset($data['college_name']) || !isset($data['course_code'])
) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit();
}

// Assign input values to variables
$name = trim($data['name']);
$contact_number = trim($data['contact_number']);
$designation = trim($data['designation']);
$college_name = trim($data['college_name']);
$course_code = trim($data['course_code']);
$created_at = date("Y-m-d H:i:s"); // Timestamp

// ✅ Validate contact number (Assuming 10-digit number)
if (!preg_match("/^[0-9]{10}$/", $contact_number)) {
    echo json_encode(["status" => "error", "message" => "Invalid contact number"]);
    exit();
}

// ✅ Fetch course ID and course name based on course_code
$course_query = "SELECT cou_id, cou_name FROM course_tbl WHERE cou_code = ?";
$stmt_course = $conn->prepare($course_query);
$stmt_course->bind_param("s", $course_code);
$stmt_course->execute();
$result_course = $stmt_course->get_result();

if ($result_course->num_rows > 0) {
    $course_row = $result_course->fetch_assoc();
    $course_id = $course_row['cou_id']; // Fetch course ID
    $course_name = $course_row['cou_name']; // Fetch course name
} else {
    echo json_encode(["status" => "error", "message" => "Invalid course selection"]);
    exit();
}

// ✅ Insert examineer record into the database
$sql = "INSERT INTO examineer_details (name, contact_number, designation, college_name, course_id, course_code, course_name, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssisss", $name, $contact_number, $designation, $college_name, $course_id, $course_code, $course_name, $created_at);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Examineer registered successfully",
        "examineer" => [
            "name" => $name,
            "contact_number" => $contact_number,
            "designation" => $designation,
            "college_name" => $college_name,
            "course_id" => $course_id,
            "course_code" => $course_code,
            "course_name" => $course_name,
            "created_at" => $created_at
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to insert examineer record"]);
}

// Close connections
$stmt->close();
$conn->close();
?>
