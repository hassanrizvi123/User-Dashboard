<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "users";

// MySQLi connection
$con = mysqli_connect($host, $user, $pass, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email exists in database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify password from database
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];  
            $_SESSION['user_name'] = $row['name'];  
            $_SESSION['user_status'] = $row['status'];  

            // Redirect to dashboard if login is successful
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['message'] = " Invalid details!";
        }
    } else {
        $_SESSION['message'] = " Email not found!";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <!-- Display Message -->
        <?php if (isset($_SESSION['message'])) { ?>
            <div class="alert"><?php echo $_SESSION['message']; ?></div>
            <?php unset($_SESSION['message']); // Message remove after refresh ?>
        <?php } ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Not registered? <a href="signup.php">Create an account</a></p>
    </div>
</body>
</html>
