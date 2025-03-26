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

<!-- login
  -->

  <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LOGIN PAGE</title>
    <!-- favicon -->
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <!-- custom style sheet -->
    <link rel="stylesheet" href="css/login.css" />
    <link rel="stylesheet" href="css/header.css" />
    <!-- bootstrap style sheet -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <style>
        body {
            height: 100vh;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <?php include('includes/header.php') ?>
    <main>
        <div class="box">
            <div class="inner-box">
                <div class="forms-wrap">
                    <form id="loginForm" method="POST" class="sign-in-form">
                        <div class="logo">
                            <h4>SIGN IN</h4>
                        </div>

                        <div class="heading">
                            <!-- <h2>Make Every Answer Count!</h2> -->
                            <!-- <h6>Not registred yet?</h6>
                            <a href="#" class="toggle">Sign up</a> -->
                        </div>

                        <div class="actual-form">
                            <div class="input-wrap">
                                <input type="text" name="userid" id="userId" class="input-field" autocomplete="off" />
                                <label>Email </label>

                            </div>

                            <div class="input-wrap">
                                <input type="password" name="password" id="pass" class="input-field"
                                    autocomplete="new-password" />
                                <label>Password</label>
                            </div>


                            <input type="submit" name="submit" value="Sign In" class="sign-btn" />

                            <p class="text">
                                <!-- Forgotten your password or you login datails?
                                <a href="#">Get help</a> signing in -->
                            </p>
                        </div>
                    </form>
                </div>

                <!-- <div class="carousel">
                    <div class="images-wrapper">
                        <img src="images/saveetha.jpg" class="image img-1 show" alt="Task Management" />
                    </div> -->

                   
                </div>

            </div>
        </div>
    </main>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- login script -->
    <script>
       $('#loginForm').on('submit', function (e) {
    e.preventDefault(); 

    const email = $('#userId').val().trim();
    const password = $('#pass').val().trim();

    if (email === "") {
        alert("Please enter your email");
        $('#userId').focus();
        return;
    }
    if (password === "") {
        alert("Please enter your password");
        return;
    }

    let formData = {
        email: email,
        password: password
    };

    $.ajax({
        url: 'api/examineer_loginbk.php',
        type: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        dataType: 'json',
        success: function (response) {
            if (response.status === "success") {
                alert('Login successful!');
                window.location.href = "dashboard.php";
            } else {
                alert(response.message);
            }
        },
        error: function (xhr, status, error) {
            alert('An error occurred: ' + error);
        }
    });
});

    </script>

    <!-- Javascript file -->
    <script>
        const inputs = document.querySelectorAll(".input-field");
        const toggle_btn = document.querySelectorAll(".toggle");
        const main = document.querySelector("main");
        const bullets = document.querySelectorAll(".bullets span");
        const images = document.querySelectorAll(".image");

        inputs.forEach((inp) => {
            inp.addEventListener("focus", () => {
                inp.classList.add("active");
            });
            inp.addEventListener("blur", () => {
                if (inp.value != "") return;
                inp.classList.remove("active");
            });
        });

        toggle_btn.forEach((btn) => {
            btn.addEventListener("click", () => {
                main.classList.toggle("sign-up-mode");
            });
        });

        function moveSlider() {
            let index = this.dataset.value;

            let currentImage = document.querySelector(`.img-${index}`);
            images.forEach((img) => img.classList.remove("show"));
            currentImage.classList.add("show");

            const textSlider = document.querySelector(".text-group");
            textSlider.style.transform = `translateY(${-(index - 1) * 2.2}rem)`;

            bullets.forEach((bull) => bull.classList.remove("active"));
            this.classList.add("active");
        }

        bullets.forEach((bullet) => {
            bullet.addEventListener("click", moveSlider);
        });
    </script>

</body>

</html>

<!-- LOGIN BK -->
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
