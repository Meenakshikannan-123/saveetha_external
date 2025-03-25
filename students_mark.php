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
    $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
    ?>

    <div class="container mt-3">
        <h2 class="text-center">Examinee Details</h2>

        <div class="text-center mb-3">
            <h4>Current Date and Time: <span id="currentDateTime"></span></h4>
        </div>

        <table class="table table-bordered" style="margin-left: -55px;" id="examineeTable">
            <thead>
                <tr>
                    <th>S.No</th>
                    <!-- <th>Examinee ID</th> -->
                    <th>Full Name</th>
                    <th>Branch</th>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>Exam Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Understanding of Problem (10 Marks)</th>
                    <th>Design & Analysis of the Problem (15 Marks)</th>
                    <th>Technical Complexity & Implementation (25 Marks)</th>
                    <th>Software/Hardware Functionality (20 Marks)</th>
                    <th>Use of Ethics & Engineering Standards (5 Marks)</th>
                    <th>Presentation & Communication (25 Marks)</th>
                    <th>Total Marks (100)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="16" class="text-center">Fetching details...</td>
                </tr>
            </tbody>
        </table>

        <!-- Next Batch Button -->
        <div class="text-center">
            <button class="btn btn-primary next-btn">Next Batch</button>
        </div>
    </div>

    <script>
    let allStudents = [];
    $(document).ready(function() {
        fetchExamineeDetails();
        setInterval(updateCurrentDateTime, 1000); // Update the current time every second
    });

    // Function to update the current date and time
    function updateCurrentDateTime() {
        let currentDateTime = new Date();
        let currentDate = currentDateTime.toLocaleDateString();
        let currentTime = currentDateTime.toLocaleTimeString();
        $('#currentDateTime').text(`${currentDate} ${currentTime}`);
    }

    // Function to fetch examinee details from the backend
    function fetchExamineeDetails(startTime = "", endTime = "") {
        $.ajax({
            url: "api/student_markbk.php", // Call to your backend API
            type: "GET",
            data: {
                course_id: "<?= $course_id; ?>",
                start_time: startTime,
                end_time: endTime
            },
            dataType: "json",
            beforeSend: function() {
                $("#examineeTable tbody").html('<tr><td colspan="16" class="text-center">Loading...</td></tr>');
            },
            success: function(response) {
                console.log(response); // Log the response for debugging
                if (response.status === "success") {
                    allStudents = response.data;
                    showCurrentExaminees(); // Update table based on current time slot
                } else {
                    $("#examineeTable tbody").html('<tr><td colspan="16" class="text-center text-danger">' + response.message + '</td></tr>');
                }
            },
            error: function() {
                $("#examineeTable tbody").html('<tr><td colspan="16" class="text-center text-danger">Error fetching data</td></tr>');
            }
        });
    }

    // Function to render the examinee table dynamically
    function showCurrentExaminees() {
        let rowCount = 1; // Counter for row numbering (starts from 1)
        let rows = allStudents.map((examinee, index) => {
            let startTime = new Date(examinee.date + " " + examinee.start_time); // Parse start time
            let endTime = new Date(examinee.date + " " + examinee.end_time); // Parse end time

            // Create table rows with dynamic data for each examinee
            return `
                <tr>
                    <td>${rowCount++}</td> <!-- Increment the counter for each row -->
                    <td>${examinee.exmne_fullname}</td>
                    <td>${examinee.branch || 'N/A'}</td>
                    <td>${examinee.subject_code || 'N/A'}</td>
                    <td>${examinee.subject_name || 'N/A'}</td>
                    <td>${examinee.date || 'N/A'}</td>
                    <td>${examinee.start_time || 'N/A'}</td>
                    <td>${examinee.end_time || 'N/A'}</td>
                    <td><input type="text" class="form-control mark" data-id="${examinee.exmne_id}" required></td>
                    <td><input type="text" class="form-control mark" data-id="${examinee.exmne_id}" required></td>
                    <td><input type="text" class="form-control mark" data-id="${examinee.exmne_id}" required></td>
                    <td><input type="text" class="form-control mark" data-id="${examinee.exmne_id}" required></td>
                    <td><input type="text" class="form-control mark" data-id="${examinee.exmne_id}" required></td>
                    <td><input type="text" class="form-control mark" data-id="${examinee.exmne_id}" required></td>
                    <td><input type="text" class="form-control total" id="total_${examinee.exmne_id}" readonly></td>
                    <td><button class="btn btn-success submit-btn" data-id="${examinee.exmne_id}">Submit</button></td>
                </tr>
            `;
        }).join(""); // Join all rows into a single string

        $("#examineeTable tbody").html(rows); // Insert rows into the table body
    }

    // Handle the "Next Batch" button click event
    $(document).on("click", ".next-btn", function() {
        window.location.reload(); // Reload the page when the button is clicked
    });

    // Calculate the total marks for each examinee when any mark is entered
    $(document).on("input", ".mark", function() {
        let row = $(this).closest("tr");
        let total = 0;
        row.find(".mark").each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        row.find(".total").val(total);
    });

    // Handle the "Submit" button click event
    $(document).on("click", ".submit-btn", function() {
        let row = $(this).closest("tr");
        let exmne_id = $(this).data("id");
        let marks = [];
        let valid = true;

        row.find(".mark").each(function() {
            let value = $(this).val().trim();
            if (value === "" || isNaN(value)) {
                valid = false;
            }
            marks.push(value === "" ? 0 : parseFloat(value));
        });

        if (!valid) {
            alert("Please enter valid marks.");
            return;
        }

        let total = row.find(".total").val();
        let data = {
            exmne_id: exmne_id,
            fullname: row.find("td:eq(1)").text(),
            branch: row.find("td:eq(2)").text(),
            subject_code: row.find("td:eq(3)").text(),
            subject_name: row.find("td:eq(4)").text(),
            marks: marks,
            total: total
        };

        $.ajax({
            url: "api/submit_marks.php", // Submit marks to backend API
            type: "POST",
            data: JSON.stringify(data),
            contentType: "application/json",
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    alert("Marks submitted successfully!");
                    window.location.reload(); // Reload the page after successful submission
                } else {
                    alert("Submission failed: " + response.message);
                }
            },
            error: function() {
                alert("Error submitting marks. Please check console for details.");
            }
        });
    });
    </script>

</body>

</html>
