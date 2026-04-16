<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include '../config/db.php';

$sql = "SELECT locations.*, categories.name AS category_name
        FROM locations
        LEFT JOIN categories ON locations.category_id = categories.id
        ORDER BY locations.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Locations - One Day Trip Planner</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .location-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            height: 100%;
        }

        .location-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.15);
        }

        .location-image {
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .badge-category {
            font-size: 0.8rem;
            border-radius: 999px;
            padding: 8px 14px;
        }

        .card-title {
            font-weight: 700;
        }

        .description-text {
            color: #6c757d;
            min-height: 72px;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 8px;
        }

        .empty-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            text-align: center;
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
        <h1 class="display-5">Explore Tourist Locations</h1>
        <p class="lead mb-0">
            Discover the best places for your perfect one-day trip
        </p>
    </div>
</section>

<div class="container pb-5">
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card location-card">
                        <img 
                            src="../assets/images/<?php echo htmlspecialchars($row['image']); ?>" 
                            alt="<?php echo htmlspecialchars($row['name']); ?>"
                            class="location-image"
                        >

                        <div class="card-body p-4">
                            <span class="badge bg-primary badge-category mb-3">
                                <?php echo htmlspecialchars($row['category_name'] ?? 'No Category'); ?>
                            </span>

                            <h4 class="card-title mb-3">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </h4>

                            <p class="description-text">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </p>

                            <div class="info-box">
                                <strong>Distance:</strong> <?php echo htmlspecialchars($row['distance_km']); ?> km
                            </div>

                            <div class="info-box">
                                <strong>Visit Duration:</strong> <?php echo htmlspecialchars($row['visit_duration_minutes']); ?> min
                            </div>

                            <div class="mt-3 d-grid">
                                <a href="location-details.php?id=<?php echo $row['id']; ?>" class="btn btn-success rounded-pill">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-box">
                    <h3>No locations found</h3>
                    <p class="text-muted mb-0">Please add some locations from the admin panel.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>