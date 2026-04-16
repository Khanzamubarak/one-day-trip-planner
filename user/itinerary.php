<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include '../config/db.php';

$message = "";
$messageType = "";
$selectedLocations = [];
$totalDistance = 0;
$totalDuration = 0;
$maxDuration = 480; // change to 600 if needed
$itinerarySaved = false;

$isLoggedIn = isset($_SESSION['user_id']);

$sql = "SELECT locations.*, categories.name AS category_name
        FROM locations
        LEFT JOIN categories ON locations.category_id = categories.id
        ORDER BY locations.name ASC";

$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['locations']) && !empty($_POST['locations'])) {
        $ids = array_map('intval', $_POST['locations']);
        $ids = array_filter($ids);

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $selectedSql = "SELECT locations.*, categories.name AS category_name
                            FROM locations
                            LEFT JOIN categories ON locations.category_id = categories.id
                            WHERE locations.id IN ($placeholders)
                            ORDER BY locations.id ASC";

            $stmt = $conn->prepare($selectedSql);

            $types = str_repeat('i', count($ids));
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $selectedResult = $stmt->get_result();

            if ($selectedResult && $selectedResult->num_rows > 0) {
                while ($row = $selectedResult->fetch_assoc()) {
                    $selectedLocations[] = $row;
                    $totalDistance += (float)$row['distance_km'];
                    $totalDuration += (int)$row['visit_duration_minutes'];
                }

                // Only duration limit is checked now
                if ($totalDuration > $maxDuration) {
                    $message = "Selected locations exceed the allowed one-day trip duration limit.";
                    $messageType = "danger";
                } else {
                    if ($isLoggedIn) {
                        $title = "My Itinerary - " . date("Y-m-d H:i:s");
                        $userId = $_SESSION['user_id'];

                        $insertItinerarySql = "INSERT INTO itineraries (user_id, title, total_distance, total_duration_minutes)
                                               VALUES (?, ?, ?, ?)";
                        $insertItineraryStmt = $conn->prepare($insertItinerarySql);
                        $insertItineraryStmt->bind_param("isdi", $userId, $title, $totalDistance, $totalDuration);

                        if ($insertItineraryStmt->execute()) {
                            $itineraryId = $conn->insert_id;

                            $insertLocationSql = "INSERT INTO itinerary_locations (itinerary_id, location_id, visit_order)
                                                  VALUES (?, ?, ?)";
                            $insertLocationStmt = $conn->prepare($insertLocationSql);

                            $order = 1;
                            foreach ($selectedLocations as $location) {
                                $locationId = $location['id'];
                                $insertLocationStmt->bind_param("iii", $itineraryId, $locationId, $order);
                                $insertLocationStmt->execute();
                                $order++;
                            }

                            $itinerarySaved = true;
                            $message = "Itinerary generated and saved successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Itinerary generated, but saving failed.";
                            $messageType = "warning";
                        }
                    } else {
                        $message = "Itinerary generated successfully. Log in to save your plan.";
                        $messageType = "success";
                    }
                }
            } else {
                $message = "No valid locations found.";
                $messageType = "danger";
            }
        } else {
            $message = "Please select at least one location.";
            $messageType = "danger";
        }
    } else {
        $message = "Please select at least one location.";
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itinerary Generator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            padding: 55px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .planner-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .location-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }

        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.12);
        }

        .badge-category {
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 0.8rem;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .summary-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }

        .stop-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
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
        <h1 class="display-5">Build Your One-Day Itinerary</h1>
        <p class="lead mb-0">Select locations and generate a simple day trip plan</p>
    </div>
</section>

<div class="container pb-5">
    <div class="card planner-card mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="row g-4 align-items-center mb-4">
                <div class="col-md-6">
                    <div class="summary-box">
                        <h5 class="fw-bold mb-2">Trip Rules</h5>
                        <p class="mb-0"><strong>Maximum Duration:</strong> <?php echo $maxDuration; ?> minutes</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="summary-box">
                        <h5 class="fw-bold mb-2">Save Feature</h5>
                        <p class="mb-0">
                            <?php echo $isLoggedIn ? 'You are logged in. Valid itineraries can be saved.' : 'Log in to save your itinerary after generating it.'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card location-card p-3">
                                    <div class="form-check mb-3">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="locations[]"
                                            value="<?php echo $row['id']; ?>"
                                            id="location<?php echo $row['id']; ?>"
                                            <?php echo (isset($_POST['locations']) && in_array($row['id'], array_map('intval', $_POST['locations']))) ? 'checked' : ''; ?>
                                        >
                                        <label class="form-check-label fw-bold" for="location<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </label>
                                    </div>

                                    <span class="badge bg-primary badge-category mb-3">
                                        <?php echo htmlspecialchars($row['category_name'] ?? 'No Category'); ?>
                                    </span>

                                    <p class="text-muted mb-3">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </p>

                                    <div class="info-box">
                                        <strong>Distance:</strong> <?php echo htmlspecialchars($row['distance_km']); ?> km
                                    </div>

                                    <div class="info-box mb-0">
                                        <strong>Visit Duration:</strong> <?php echo htmlspecialchars($row['visit_duration_minutes']); ?> min
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0">No locations found.</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 d-grid d-md-flex gap-3">
                    <button type="submit" class="btn btn-success action-btn">Generate Itinerary</button>
                    <?php if ($itinerarySaved): ?>
                        <a href="saved-itineraries.php" class="btn btn-outline-primary action-btn">View Saved Itineraries</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($selectedLocations)): ?>
        <div class="card planner-card">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <h2 class="fw-bold mb-0">Generated Itinerary</h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge text-bg-light p-3">Total Distance: <?php echo $totalDistance; ?> km</span>
                        <span class="badge text-bg-light p-3">Total Duration: <?php echo $totalDuration; ?> min</span>
                    </div>
                </div>

                <?php if ($totalDuration <= $maxDuration): ?>
                    <div class="row g-4">
                        <?php $order = 1; ?>
                        <?php foreach ($selectedLocations as $location): ?>
                            <div class="col-md-6">
                                <div class="card stop-card h-100">
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-3">
                                            Stop <?php echo $order; ?>: <?php echo htmlspecialchars($location['name']); ?>
                                        </h5>

                                        <span class="badge bg-primary badge-category mb-3">
                                            <?php echo htmlspecialchars($location['category_name'] ?? 'No Category'); ?>
                                        </span>

                                        <p class="text-muted"><?php echo htmlspecialchars($location['description']); ?></p>

                                        <div class="info-box">
                                            <strong>Distance:</strong> <?php echo htmlspecialchars($location['distance_km']); ?> km
                                        </div>

                                        <div class="info-box mb-0">
                                            <strong>Visit Duration:</strong> <?php echo htmlspecialchars($location['visit_duration_minutes']); ?> min
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $order++; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mb-0">
                        Cannot generate itinerary because the selected locations exceed the allowed duration limit.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>