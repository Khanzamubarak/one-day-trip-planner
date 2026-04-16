<?php
include 'auth-check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #212529, #495057);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .dashboard-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 30px rgba(0,0,0,0.12);
        }

        .dashboard-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .action-btn {
            border-radius: 999px;
            padding: 10px 20px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="ms-auto">
            <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-5">Admin Dashboard</h1>
        <p class="lead mb-0">Manage locations and control the system</p>
    </div>
</section>

<div class="container pb-5">
    <div class="row g-4">

        <div class="col-md-6 col-lg-4">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">📋</div>
                <h5 class="fw-bold">Manage Locations</h5>
                <p class="text-muted">View, edit, and delete all tourist locations.</p>
                <a href="locations.php" class="btn btn-primary action-btn">Open</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">➕</div>
                <h5 class="fw-bold">Add Location</h5>
                <p class="text-muted">Create a new tourist destination with image and details.</p>
                <a href="add-location.php" class="btn btn-success action-btn">Add</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">🌍</div>
                <h5 class="fw-bold">View Public Locations</h5>
                <p class="text-muted">See how locations appear on the user side.</p>
                <a href="../user/locations.php" class="btn btn-warning action-btn">View</a>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>