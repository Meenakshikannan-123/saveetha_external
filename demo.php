<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examinee Details</title>
    <link rel="stylesheet" href="css/header.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include('includes/header.php'); ?>

<?php
// Get course_id from URL
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
?>

<div class="container mt-5">
    <h2 class="text-center">Examinee Details</h2>

    <table class="table table-bordered" id="examineeTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Branch</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="6" class="text-center">Fetching details...</td></tr>
        </tbody>
    </table>
</div>

<script>
  $(document).ready(function () {
    fetchExamineeDetails();
  });

  function fetchExamineeDetails() {
    $.ajax({
        url: "api/student_markbk.php", // Ensure this endpoint is correct
        type: "GET",
        data: { course_id: "<?= $course_id; ?>" }, // Pass course_id from PHP
        dataType: "json",
        beforeSend: function () {
            $("#examineeTable tbody").html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
        },
        success: function (response) {
            console.log("API Response:", response);

            if (response.status === "success") {
                let rows = "";
                response.data.forEach(function (examinee) {
                    rows += `
                        <tr>
                            <td>${examinee.exmne_id}</td>
                            <td>${examinee.exmne_fullname}</td>
                            <td>${examinee.branch || 'N/A'}</td>
                            <td>${examinee.subject_code || 'N/A'}</td>
                            <td>${examinee.subject_name || 'N/A'}</td>
                            <td>${examinee.exmne_email}</td>
                        </tr>
                    `;
                });
                $("#examineeTable tbody").html(rows);
            } else {
                $("#examineeTable tbody").html('<tr><td colspan="6" class="text-center text-danger">' + response.message + '</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            $("#examineeTable tbody").html('<tr><td colspan="6" class="text-center text-danger">Error fetching data</td></tr>');
        }
    });
  }
</script>

</body>
</html>


<?php
header("Content-Type: application/json");
include('../connection.php');

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

$course_code = isset($_GET['course_code']) ? $_GET['course_code'] : ''; // Get course_code

if (empty($course_id)) {
    echo json_encode(["status" => "error", "message" => "Course ID is required"]);
    exit;
}

// Fetch examinee details
$sql_examinee = "SELECT exmne_id, exmne_rnumber, exmne_fullname, course, branch, subject_code, subject_name, exmne_code, exmne_course, exmne_year_level, exmne_email, exmne_mobile, exmne_status, schedule_id, create_at FROM examinee_tbl WHERE exmne_course = ?";
$stmt_examinee = $conn->prepare($sql_examinee);
$stmt_examinee->bind_param("i", $course_id);
$stmt_examinee->execute();
$result_examinee = $stmt_examinee->get_result();

$examinees = [];
while ($row_examinee = $result_examinee->fetch_assoc()) {
    $examinees[] = $row_examinee;
}



// Fetch exam schedule details
$sql_schedule = "SELECT id, exam_type, course_code, course_name, date, start_time, end_time, session, create_at FROM exam_schedule WHERE course_code = ?";
$stmt_schedule = $conn->prepare($sql_schedule);
$stmt_schedule->bind_param("s", $course_code); // Use course_code here
$stmt_schedule->execute();
$result_schedule = $stmt_schedule->get_result();

$schedules = [];
while ($row_schedule = $result_schedule->fetch_assoc()) {
    $schedules[] = $row_schedule;
}

// Combine results based on matching course_code and course_name
$combinedData = [];
if (!empty($examinees) && !empty($schedules)) {
    foreach ($examinees as $examinee) {
        foreach ($schedules as $schedule) {
            if ($examinee['subject_code'] == $schedule['course_code'] && $examinee['subject_name'] == $schedule['course_name']) {
                $combinedData[] = array_merge($examinee, $schedule);
            }
        }
    }
}

if (!empty($combinedData)) {
    echo json_encode(["status" => "success", "data" => $combinedData]);
} else {
    echo json_encode(["status" => "error", "message" => "No matching data found for this course"]);
}

$stmt_examinee->close();
$stmt_schedule->close();
$conn->close();
?>

<!-- sumbit_matrks.php -->
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

// Check if the examinee already has data in the database
$sql_check = "SELECT * FROM student_marks WHERE exmne_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "SQL Prepare Failed: " . $conn->error]);
    exit;
}

$stmt_check->bind_param("i", $exmne_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

// If data exists, update it
if ($result->num_rows > 0) {
    // Existing record found, proceed with update
    $sql_update = "UPDATE student_marks 
                   SET fullname = ?, branch = ?, subject_code = ?, subject_name = ?, 
                       mark1 = ?, mark2 = ?, mark3 = ?, mark4 = ?, mark5 = ?, mark6 = ?, total = ? 
                   WHERE exmne_id = ?";

    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "SQL Prepare Failed: " . $conn->error]);
        exit;
    }

    $stmt_update->bind_param("ssssiiiiiiii", $fullname, $branch, $subject_code, $subject_name, 
                             $mark1, $mark2, $mark3, $mark4, $mark5, $mark6, $total, $exmne_id);

    if ($stmt_update->execute()) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Marks updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt_update->error]);
    }

    $stmt_update->close();
} else {
    // No existing record found, proceed with insert
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
}

// Close the database connection
$stmt_check->close();
$conn->close();

exit;
?>

<!-- student_markbk.php -->

<?php
header("Content-Type: application/json");
include('../connection.php');

// Ensure that course_id is provided in the request
// Ensure that course_id is provided in the request
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

// If course_id is empty, return an error
if (empty($course_id)) {
    echo json_encode(["status" => "error", "message" => "Course ID is required"]);
    exit;
}

// Fetch examinee details for EEE students (filtered by course_id)
$sql = "
    SELECT 
        e.exmne_id, 
        e.exmne_rnumber, 
        e.exmne_fullname, 
        e.branch, 
        e.subject_code, 
        e.subject_name, 
        e.exmne_code, 
        e.exmne_course, 
        e.exmne_year_level, 
        e.exmne_email, 
        e.exmne_mobile, 
        e.exmne_password, 
        e.exmne_status, 
        e.schedule_id, 
        e.create_at, 
        s.exam_type, 
        s.course_code, 
        s.course_name, 
        s.date, 
        s.start_time, 
        s.end_time, 
        s.session, 
        s.create_at AS exam_schedule_created_at
    FROM 
        examinee_tbl e
    INNER JOIN 
        exam_schedule s 
    ON 
        e.schedule_id = s.id
    AND 
        e.subject_code = s.course_code
    WHERE 
        e.exmne_course = ?"; // Filters based on the course_id provided

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_id);

// Execute and check for errors
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $examinees = [];

    while ($row = $result->fetch_assoc()) {
        $examinees[] = $row;
    }

    if (!empty($examinees)) {
        echo json_encode(["status" => "success", "data" => $examinees]);
    } else {
        echo json_encode(["status" => "error", "message" => "No examinees found for this course"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "SQL query failed"]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
