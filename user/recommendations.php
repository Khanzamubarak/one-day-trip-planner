<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include '../config/db.php';

$sql = "SELECT * FROM recommended_plans ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Plans</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            padding: 55px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .plan-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(0,0,0,0.12);
        }

        .location-list {
            margin-bottom: 0;
        }

        .section-box {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 16px;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .action-btn {
            border-radius: 999px;
            padding: 10px 20px;
        }

        .empty-box {
            background: white;
            border-radius: 22px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
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
        <h1 class="display-5">Recommended Travel Plans</h1>
        <p class="lead mb-0">Explore ready-made one-day plans for a smooth travel experience</p>
    </div>
</section>

<div class="container pb-5">
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($plan = $result->fetch_assoc()): ?>
                <div class="col-lg-6">
                    <div class="card plan-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">
                                        Recommended Plan
                                    </span>
                                    <h3 class="fw-bold mb-2">
                                        <?php echo htmlspecialchars($plan['title']); ?>
                                    </h3>
                                </div>
                            </div>

                            <p class="text-muted mb-4">
                                <?php echo htmlspecialchars($plan['description']); ?>
                            </p>

                            <?php
                            $planId = $plan['id'];

                            $locationSql = "SELECT recommended_plan_locations.visit_order, locations.name
                                            FROM recommended_plan_locations
                                            INNER JOIN locations ON recommended_plan_locations.location_id = locations.id
                                            WHERE recommended_plan_locations.recommended_plan_id = ?
                                            ORDER BY recommended_plan_locations.visit_order ASC";

                            $locationStmt = $conn->prepare($locationSql);
                            $locationStmt->bind_param("i", $planId);
                            $locationStmt->execute();
                            $locationResult = $locationStmt->get_result();
                            ?>

                            <div class="section-box">
                                <h5 class="fw-bold mb-3">Included Locations</h5>

                                <?php if ($locationResult && $locationResult->num_rows > 0): ?>
                                    <ol class="location-list">
                                        <?php while ($location = $locationResult->fetch_assoc()): ?>
                                            <li class="mb-2"><?php echo htmlspecialchars($location['name']); ?></li>
                                        <?php endwhile; ?>
                                    </ol>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No locations added to this plan yet.</p>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                <a href="itinerary.php" class="btn btn-success action-btn">Create My Own Plan</a>
                                <a href="locations.php" class="btn btn-outline-primary action-btn">Browse Locations</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-box">
            <h3 class="fw-bold">No Recommended Plans Found</h3>
            <p class="text-muted mb-3">There are no travel plans available at the moment.</p>
            <a href="locations.php" class="btn btn-primary action-btn">Browse Locations</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>