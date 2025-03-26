<?php
header("Content-Type: application/json");
include('../connection.php');

// Set the desired timezone (Asia/Kolkata for Chennai)
date_default_timezone_set('Asia/Kolkata');  // This is for Chennai (IST timezone)

// Ensure that course_id is provided in the request
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';

// If course_id is empty, return an error
if (empty($course_id)) {
    echo json_encode(["status" => "error", "message" => "Course ID is required"]);
    exit;
}

// Get the current time and date in the same format as your start_time and end_time columns (e.g., 'Y-m-d H:i:s')
$current_time = date('Y-m-d H:i:s');  // This will give you the current time in 'Y-m-d H:i:s' format
$current_date = date('Y-m-d'); // This will give you the current date in 'Y-m-d' format

// Log current time and date for debugging
error_log("Current Time: " . $current_time);
error_log("Current Date: " . $current_date);

// Fetch examinee details for the given course_id, filtering by current date and time, and limiting to 10 students per hour
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
        e.exmne_course = ? 
    AND 
        s.date = ?  -- Match today's date
    AND 
        s.start_time <= ?  -- Ensure the exam has started before or at the current time
    AND 
        s.end_time >= ?  -- Ensure the exam is still ongoing
    AND 
        ? BETWEEN s.start_time AND s.end_time -- Ensure the current time is between start_time and end_time
    LIMIT 10";  // Limit to 10 students per hour

// Log the final SQL query for debugging
error_log("SQL Query: " . $sql);

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $course_id, $current_date, $current_time, $current_time, $current_time); // Bind parameters

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
        echo json_encode(["status" => "error", "message" => "No examinees found for this course or no ongoing exams for today"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "SQL query failed"]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
