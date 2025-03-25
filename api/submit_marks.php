<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include('../connection.php');

$response = ["status" => "error", "message" => "Unknown error occurred"];

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    exit;
}

// Validate input data
$required_fields = ['exmne_id', 'fullname', 'branch', 'subject_code', 'subject_name', 'marks', 'total'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing or empty field: $field"]);
        exit;
    }
}

$exmne_id = $data['exmne_id'];
$fullname = $data['fullname'];
$branch = $data['branch'];
$subject_code = $data['subject_code'];
$subject_name = $data['subject_name'];
$marks = $data['marks'];
$total = $data['total'];

// Ensure marks is an array with exactly 6 numeric values
if (count($marks) != 6 || !array_reduce($marks, function ($carry, $item) {
    return $carry && is_numeric($item);
}, true)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid marks format"]);
    exit;
}

$mark1 = (int) $marks[0];
$mark2 = (int) $marks[1];
$mark3 = (int) $marks[2];
$mark4 = (int) $marks[3];
$mark5 = (int) $marks[4];
$mark6 = (int) $marks[5];

// Insert new record into the database
$sql_insert = "INSERT INTO student_marks (exmne_id, fullname, branch, subject_code, subject_name, 
                      mark1, mark2, mark3, mark4, mark5, mark6, total) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "SQL Prepare Failed: " . $conn->error]);
    exit;
}

$stmt_insert->bind_param("issssiiiiiii", $exmne_id, $fullname, $branch, $subject_code, $subject_name, 
                         $mark1, $mark2, $mark3, $mark4, $mark5, $mark6, $total);

if ($stmt_insert->execute()) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Marks inserted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $stmt_insert->error]);
}

$stmt_insert->close();

// Close the database connection
$conn->close();

exit;
?>
