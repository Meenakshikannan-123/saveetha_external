<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examineer Details</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/header.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Add some custom styles to make the form look better */
        .form-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-container h3 {
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
        }
        .form-control {
            border-radius: 0.5rem; /* rounded corners for input fields */
        }
        .form-label {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .btn {
            border-radius: 0.5rem;
        }
        .alert {
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <?php include('includes/examineer_header.php'); ?>

    <div class="container mt-4 form-container">
        <h3>SIMATS EXTERNAL EVALUATION</h3>
        <div id="responseMessage"></div>

        <form id="examineerForm" class="mt-3">
            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" placeholder="Enter your full name" required>
            </div>

            <!-- Contact Number -->
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="contact_number" placeholder="Enter your contact number" required>
            </div>

            <!-- Designation -->
            <div class="mb-3">
                <label for="designation" class="form-label">Designation</label>
                <input type="text" class="form-control" id="designation" placeholder="Enter your designation" required>
            </div>

            <!-- College Name -->
            <div class="mb-3">
                <label for="college_name" class="form-label">College Name</label>
                <input type="text" class="form-control" id="college_name" placeholder="Enter your college name" required>
            </div>

            <!-- Course -->
            <div class="mb-3">
                <label for="course" class="form-label">Course Code and Name</label>
                <select class="form-select" id="course" name="course" required>
                    <option value="">Select Course</option>
                    <?php
                    include('connection.php');
                    $query = "SELECT cou_id, cou_code, cou_name FROM course_tbl"; // Ensure cou_id exists
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<option value="' . htmlspecialchars($row['cou_code']) . '" data-id="' . htmlspecialchars($row['cou_id']) . '">'
                            . htmlspecialchars($row['cou_name']) . ' (' . htmlspecialchars($row['cou_code']) . ')</option>';
                    }
                    ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary mx-auto d-block">Submit</button>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $("#examineerForm").submit(function (event) {
                event.preventDefault();

                var selectedCourse = $("#course option:selected"); // Get selected option
                var courseID = selectedCourse.attr("data-id"); // Get course ID
                var courseCode = selectedCourse.val(); // Get course code (value attribute)

                // Ensure course ID exists before proceeding
                if (!courseID || !courseCode) {
                    alert("Please select a valid course.");
                    return;
                }

                var formData = {
                    name: $("#name").val(),
                    contact_number: $("#contact_number").val(),
                    designation: $("#designation").val(),
                    college_name: $("#college_name").val(),
                    course_id: courseID,
                    course_code: courseCode
                };

                $.ajax({
                    url: 'api/examineer_detailsbk.php',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function (response) {
                        if (response.status === "success") {
                            alert(response.message);
                            window.location.href = 'students_mark.php?course_id=' + encodeURIComponent(courseID) + '&course_code=' + encodeURIComponent(courseCode);
                        } else {
                            $("#responseMessage").html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        $("#responseMessage").html('<div class="alert alert-danger">Error: ' + error + '</div>');
                    }
                });
            });
        });
    </script>

</body>

</html>
