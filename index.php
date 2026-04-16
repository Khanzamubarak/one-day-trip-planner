<?php
session_start();

$isLoggedIn = isset($_SESSION["user_id"]);
$currentPage = basename($_SERVER['PHP_SELF']); // ✅ FIX
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One Day Trip Planner</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #0d6efd, #20c997);
            color: white;
            padding: 90px 0;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .feature-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 28px rgba(0,0,0,0.12);
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

.custom-navbar .nav-link:hover {
    color: #fff;
}

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

/* logout */
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
        <a class="navbar-brand fw-bold" href="index.php">One Day Trip Planner</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $isLoggedIn ? 'user/locations.php' : 'user/login.php'; ?>">
                        Locations
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $isLoggedIn ? 'user/itinerary.php' : 'user/login.php'; ?>">
                        Itinerary
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $isLoggedIn ? 'user/recommendations.php' : 'user/login.php'; ?>">
                        Recommendations
                    </a>
                </li>

                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user/saved-itineraries.php">Saved Plans</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link logout-btn" href="user/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user/login.php">Login</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="user/register.php">Register</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4">Plan Your Perfect One-Day Trip</h1>
        <p class="lead mt-3 mb-4">
            Explore tourist locations, generate itineraries, and discover recommended plans with ease.
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?php echo $isLoggedIn ? 'user/locations.php' : 'user/login.php'; ?>" class="btn btn-light btn-lg action-btn">
                Explore Locations
            </a>

            <a href="<?php echo $isLoggedIn ? 'user/itinerary.php' : 'user/login.php'; ?>" class="btn btn-outline-light btn-lg action-btn">
                Generate Itinerary
            </a>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4">

        <div class="col-md-6 col-lg-4">
            <div class="card feature-card text-center p-4">
                <div class="fs-1 mb-3">🌍</div>
                <h5 class="fw-bold">Browse Locations</h5>
                <p class="text-muted">View tourist destinations with category, distance, duration, and image details.</p>
                <a href="<?php echo $isLoggedIn ? 'user/locations.php' : 'user/login.php'; ?>" class="btn btn-primary action-btn">
                    <?php echo $isLoggedIn ? 'Open' : 'Login to Continue'; ?>
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card feature-card text-center p-4">
                <div class="fs-1 mb-3">🗺️</div>
                <h5 class="fw-bold">Generate Itinerary</h5>
                <p class="text-muted">Select multiple places and build a valid one-day travel plan easily.</p>
                <a href="<?php echo $isLoggedIn ? 'user/itinerary.php' : 'user/login.php'; ?>" class="btn btn-success action-btn">
                    <?php echo $isLoggedIn ? 'Plan Now' : 'Login to Continue'; ?>
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card feature-card text-center p-4">
                <div class="fs-1 mb-3">⭐</div>
                <h5 class="fw-bold">Recommended Plans</h5>
                <p class="text-muted">Check ready-made recommended plans for a faster travel experience.</p>
                <a href="<?php echo $isLoggedIn ? 'user/recommendations.php' : 'user/login.php'; ?>" class="btn btn-warning action-btn">
                    <?php echo $isLoggedIn ? 'View' : 'Login to Continue'; ?>
                </a>
            </div>
        </div>

    </div>
</div>

<footer class="bg-dark text-white text-center py-3">
    <p class="mb-0">© 2026 One Day Trip Planner System</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>