<?php
session_start();

// Database Connection
$conn = mysqli_connect("localhost", "root", "", "users");

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_status = $_SESSION['user_status'];
$user_name = $_SESSION['user_name'];

// Insert Education Record (With Prepared Statement)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_record'])) {
    $degree = trim($_POST['degree']);
    $institution = trim($_POST['institution']);
    $year = intval($_POST['year']);
    $created_at = date('Y-m-d H:i:s');

    if (!empty($degree) && !empty($institution) && $year > 1900) {
        $stmt = $conn->prepare("INSERT INTO education (user_id, degree, institution, year, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $degree, $institution, $year, $created_at);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Record added successfully!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid input!";
    }

    header("Location: dashboard.php");
    exit;
}

// Fetch Education Records
$records = $conn->prepare("SELECT degree, institution, year FROM education WHERE user_id = ?");
$records->bind_param("i", $user_id);
$records->execute();
$result = $records->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">User Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-white">Status: <?php echo ($user_status == 'active') ? 'Active' : 'Inactive'; ?></span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>User Profile</h5>
                    <strong>Welcome, <?php echo htmlspecialchars($user_name); ?></strong>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card p-3">
                    <h5>Education Records</h5>

                    <?php if (isset($_SESSION['message'])) { ?>
                        <div class="alert alert-info"><?php echo $_SESSION['message']; ?></div>
                        <?php unset($_SESSION['message']); ?>
                    <?php } ?>

                    <form method="POST" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="degree" class="form-control" placeholder="Degree" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="institution" class="form-control" placeholder="Institution" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="year" class="form-control" placeholder="Year" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="add_record" class="btn btn-success">Add</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Degree</th>
                                <th>Institution</th>
                                <th>Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['degree']); ?></td>
                                    <td><?php echo htmlspecialchars($row['institution']); ?></td>
                                    <td><?php echo htmlspecialchars($row['year']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
