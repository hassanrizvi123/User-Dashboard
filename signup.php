<?php
$message = ""; // Message variable initialize karein

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "users";

    // MySQLi connection with error handling
    $con = mysqli_connect($host, $user, $pass, $dbname);
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Check if all required fields exist in POST request
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if email already exists
        $check_email = "SELECT email FROM users WHERE email = '$email'";
        $result = mysqli_query($con, $check_email);

        if (mysqli_num_rows($result) > 0) {
            $message = "<div class='error'>This email is already registered. Please use a different email.</div>";
        } else {
            // Additional Fields
            $status = "inactive";
            $verification_code = bin2hex(random_bytes(16));
            $created_at = date('Y-m-d H:i:s');

            // Insert Query
            $sql = "INSERT INTO `users` (`name`, `email`, `password`, `status`, `verification_code`, `created_at`) 
                    VALUES ('$name', '$email', '$password', '$status', '$verification_code', '$created_at')";

            if (mysqli_query($con, $sql)) {
                $message = "<div class='success'>User registered successfully! Verification code sent to email.</div>";
            } else {
                $message = "<div class='error'>Error: " . mysqli_error($con) . "</div>";
            }
        }

    } else {
        $message = "<div class='error'>Please fill all required fields!</div>";
    }

    // Close connection
    mysqli_close($con);
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Register</h2>

        <?php 
        // Show the message only if user is not register

        if (!empty($message)) { 
            echo $message; 
        }
        ?>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
