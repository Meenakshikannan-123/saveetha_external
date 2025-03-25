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

                <div class="carousel">
                    <div class="images-wrapper">
                        <img src="images/saveetha.jpg" class="image img-1 show" alt="Task Management" />
                    </div>

                    <div class="text-slider">
                        <div class="text-wrap">
                            <div class="text-group">
                                <!-- <h2>A Gateway to Your Academic Goals.</h2><br> -->
                                <!-- <h2>Your Path to Excellence Starts Here!</h2> -->
                            </div>
                        </div>
                    </div>
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