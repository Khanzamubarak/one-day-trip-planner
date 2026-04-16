<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include '../config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid location ID.");
}

$id = (int) $_GET['id'];

$sql = "SELECT locations.*, categories.name AS category_name
        FROM locations
        LEFT JOIN categories ON locations.category_id = categories.id
        WHERE locations.id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query preparation failed.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$location = $result->fetch_assoc();

if (!$location) {
    die("Location not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($location['name']); ?> - Location Details</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 50px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .details-card {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.10);
        }

        .details-image {
            width: 100%;
            height: 420px;
            object-fit: cover;
        }

        .badge-category {
            font-size: 0.9rem;
            border-radius: 999px;
            padding: 8px 16px;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 16px;
            height: 100%;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,0.04);
        }

        .section-title {
            font-weight: 700;
        }

        .description-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px;
            border: 1px solid #eef0f2;
        }

        .action-btn {
            border-radius: 999px;
            padding: 10px 20px;
        }

        .navbar-brand {
            font-weight: 700;
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
        <h1 class="display-5"><?php echo htmlspecialchars($location['name']); ?></h1>
        <p class="lead mb-0">Discover full details of this tourist destination</p>
    </div>
</section>

<div class="container pb-5">
    <div class="mb-4">
        <a href="locations.php" class="btn btn-outline-primary action-btn">← Back to Locations</a>
    </div>

    <div class="card details-card">
        <img
            src="../assets/images/<?php echo htmlspecialchars($location['image']); ?>"
            alt="<?php echo htmlspecialchars($location['name']); ?>"
            class="details-image"
        >

        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <span class="badge bg-primary badge-category mb-3">
                        <?php echo htmlspecialchars($location['category_name'] ?? 'No Category'); ?>
                    </span>
                    <h2 class="fw-bold mb-0"><?php echo htmlspecialchars($location['name']); ?></h2>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="info-box">
                        <h5 class="section-title mb-2">Distance</h5>
                        <p class="mb-0 text-muted">
                            <?php echo htmlspecialchars($location['distance_km']); ?> km from the center point
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-box">
                        <h5 class="section-title mb-2">Visit Duration</h5>
                        <p class="mb-0 text-muted">
                            Approx. <?php echo htmlspecialchars($location['visit_duration_minutes']); ?> minutes
                        </p>
                    </div>
                </div>
            </div>

            <div class="description-box">
                <h4 class="section-title mb-3">Description</h4>
                <p class="text-muted mb-0" style="line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($location['description'])); ?>
                </p>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-3">
                <a href="itinerary.php" class="btn btn-success action-btn">Add to Trip Planning</a>
                <a href="recommendations.php" class="btn btn-outline-secondary action-btn">View Recommended Plans</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>