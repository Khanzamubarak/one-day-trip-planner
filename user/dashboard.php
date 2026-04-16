<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);



if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// block admin from user dashboard
if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
    header("Location: ../admin/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
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
        .custom-navbar {
    background: #111;
    padding: 12px 0;
}

.custom-navbar .navbar-brand {
    color: #fff;
    font-size: 20px;
}

.custom-navbar .nav-link {
    color: rgba(255,255,255,0.7);
    font-weight: 500;
    margin-left: 18px;
    transition: all 0.3s ease;
    position: relative;
}

/* hover */
.custom-navbar .nav-link:hover {
    color: #fff;
}

/* active page */
.custom-navbar .nav-link.active {
    color: #fff;
    font-weight: 600;
}

/* underline animation */
.custom-navbar .nav-link::after {
    content: "";
    display: block;
    height: 2px;
    width: 0%;
    background: #0d6efd;
    transition: 0.3s;
    margin-top: 4px;
}

.custom-navbar .nav-link:hover::after,
.custom-navbar .nav-link.active::after {
    width: 100%;
}

/* logout button */
.logout-btn {
    background: #dc3545;
    color: #fff !important;
    padding: 6px 16px;
    border-radius: 20px;
    margin-left: 20px;
}

.logout-btn:hover {
    background: #b02a37;
}
    </style>
</head>
<body>


<nav class="navbar navbar-expand-lg custom-navbar shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">One Day Trip Planner</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'locations.php') ? 'active' : ''; ?>" href="locations.php">
                        Locations
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'itinerary.php') ? 'active' : ''; ?>" href="itinerary.php">
                        Itinerary
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'recommendations.php') ? 'active' : ''; ?>" href="recommendations.php">
                        Recommendations
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'saved-itineraries.php') ? 'active' : ''; ?>" href="saved-itineraries.php">
                        Saved Plans
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link logout-btn" href="logout.php">
                        Logout
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-5">Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></h1>
        <p class="lead mb-0">Plan your perfect one-day trip with ease</p>
    </div>
</section>

<div class="container pb-5">
    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">🌍</div>
                <h5 class="fw-bold">Browse Locations</h5>
                <p class="text-muted">Explore tourist attractions and destination details.</p>
                <a href="locations.php" class="btn btn-primary action-btn">Open</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">🗺️</div>
                <h5 class="fw-bold">Generate Itinerary</h5>
                <p class="text-muted">Select places and build your one-day travel plan.</p>
                <a href="itinerary.php" class="btn btn-success action-btn">Plan Now</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">⭐</div>
                <h5 class="fw-bold">Recommended Plans</h5>
                <p class="text-muted">See predefined trip plans for a quick travel idea.</p>
                <a href="recommendations.php" class="btn btn-warning action-btn">View</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card text-center p-4">
                <div class="dashboard-icon">💾</div>
                <h5 class="fw-bold">Saved Itineraries</h5>
                <p class="text-muted">Check the plans you have already generated and saved.</p>
                <a href="saved-itineraries.php" class="btn btn-info action-btn">My Plans</a>
            </div>
        </div>

    </div>

    <div class="text-center mt-5">
        <a href="logout.php" class="btn btn-outline-danger action-btn">Logout</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>