<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";
$messageType = "";

/* =========================
   DELETE ITINERARY
========================= */
if (isset($_GET['delete'])) {
    $deleteItineraryId = (int) $_GET['delete'];

    // Make sure itinerary belongs to logged-in user
    $checkDeleteSql = "SELECT id FROM itineraries WHERE id = ? AND user_id = ?";
    $checkDeleteStmt = $conn->prepare($checkDeleteSql);
    $checkDeleteStmt->bind_param("ii", $deleteItineraryId, $userId);
    $checkDeleteStmt->execute();
    $checkDeleteResult = $checkDeleteStmt->get_result();

    if ($checkDeleteResult->num_rows === 1) {
        // Delete child records first
        $deleteLocationsSql = "DELETE FROM itinerary_locations WHERE itinerary_id = ?";
        $deleteLocationsStmt = $conn->prepare($deleteLocationsSql);
        $deleteLocationsStmt->bind_param("i", $deleteItineraryId);
        $deleteLocationsStmt->execute();

        // Delete itinerary
        $deleteItinerarySql = "DELETE FROM itineraries WHERE id = ? AND user_id = ?";
        $deleteItineraryStmt = $conn->prepare($deleteItinerarySql);
        $deleteItineraryStmt->bind_param("ii", $deleteItineraryId, $userId);

        if ($deleteItineraryStmt->execute()) {
            $message = "Itinerary deleted successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to delete itinerary.";
            $messageType = "danger";
        }
    } else {
        $message = "Invalid itinerary selected for deletion.";
        $messageType = "danger";
    }
}

/* =========================
   EDIT MODE
========================= */
$editMode = false;
$editItineraryId = null;
$selectedLocationIds = [];

if (isset($_GET['edit'])) {
    $editItineraryId = (int) $_GET['edit'];

    // Make sure itinerary belongs to logged-in user
    $checkEditSql = "SELECT * FROM itineraries WHERE id = ? AND user_id = ?";
    $checkEditStmt = $conn->prepare($checkEditSql);
    $checkEditStmt->bind_param("ii", $editItineraryId, $userId);
    $checkEditStmt->execute();
    $checkEditResult = $checkEditStmt->get_result();

    if ($checkEditResult->num_rows === 1) {
        $editMode = true;

        $selectedSql = "SELECT location_id FROM itinerary_locations WHERE itinerary_id = ? ORDER BY visit_order ASC";
        $selectedStmt = $conn->prepare($selectedSql);
        $selectedStmt->bind_param("i", $editItineraryId);
        $selectedStmt->execute();
        $selectedResult = $selectedStmt->get_result();

        while ($row = $selectedResult->fetch_assoc()) {
            $selectedLocationIds[] = (int) $row['location_id'];
        }
    } else {
        $message = "Invalid itinerary selected for editing.";
        $messageType = "danger";
    }
}

/* =========================
   UPDATE ITINERARY
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_itinerary'])) {
    $editItineraryId = (int) $_POST['itinerary_id'];

    // Make sure itinerary belongs to logged-in user
    $checkUpdateSql = "SELECT * FROM itineraries WHERE id = ? AND user_id = ?";
    $checkUpdateStmt = $conn->prepare($checkUpdateSql);
    $checkUpdateStmt->bind_param("ii", $editItineraryId, $userId);
    $checkUpdateStmt->execute();
    $checkUpdateResult = $checkUpdateStmt->get_result();

    if ($checkUpdateResult->num_rows !== 1) {
        $message = "Unauthorized update attempt.";
        $messageType = "danger";
    } elseif (!isset($_POST['locations']) || empty($_POST['locations'])) {
        $message = "Please select at least one location.";
        $messageType = "danger";
        $editMode = true;
        $selectedLocationIds = [];
    } else {
        $ids = array_map('intval', $_POST['locations']);
        $ids = array_filter($ids);

        if (empty($ids)) {
            $message = "Please select valid locations.";
            $messageType = "danger";
            $editMode = true;
        } else {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $fetchSql = "SELECT id, distance_km, visit_duration_minutes FROM locations WHERE id IN ($placeholders)";
            $fetchStmt = $conn->prepare($fetchSql);
            $types = str_repeat('i', count($ids));
            $fetchStmt->bind_param($types, ...$ids);
            $fetchStmt->execute();
            $fetchResult = $fetchStmt->get_result();

            $totalDistance = 0;
            $totalDuration = 0;

            while ($row = $fetchResult->fetch_assoc()) {
                $totalDistance += (float) $row['distance_km'];
                $totalDuration += (int) $row['visit_duration_minutes'];
            }

            // Delete old itinerary locations
            $deleteOldSql = "DELETE FROM itinerary_locations WHERE itinerary_id = ?";
            $deleteOldStmt = $conn->prepare($deleteOldSql);
            $deleteOldStmt->bind_param("i", $editItineraryId);
            $deleteOldStmt->execute();

            // Insert updated itinerary locations
            $insertSql = "INSERT INTO itinerary_locations (itinerary_id, location_id, visit_order) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);

            $order = 1;
            foreach ($ids as $locationId) {
                $insertStmt->bind_param("iii", $editItineraryId, $locationId, $order);
                $insertStmt->execute();
                $order++;
            }

            // Update totals
            $updateSql = "UPDATE itineraries 
                          SET total_distance = ?, total_duration_minutes = ?
                          WHERE id = ? AND user_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("diii", $totalDistance, $totalDuration, $editItineraryId, $userId);

            if ($updateStmt->execute()) {
                $message = "Itinerary updated successfully.";
                $messageType = "success";
                $editMode = false;
                $selectedLocationIds = [];
            } else {
                $message = "Failed to update itinerary.";
                $messageType = "danger";
                $editMode = true;
                $selectedLocationIds = $ids;
            }
        }
    }
}

/* =========================
   FETCH ALL LOCATIONS FOR EDIT FORM
========================= */
$allLocationsSql = "SELECT locations.*, categories.name AS category_name
                    FROM locations
                    LEFT JOIN categories ON locations.category_id = categories.id
                    ORDER BY locations.name ASC";
$allLocationsResult = $conn->query($allLocationsSql);

/* =========================
   FETCH USER ITINERARIES
========================= */
$sql = "SELECT * FROM itineraries WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Itineraries</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(135deg, #6f42c1, #0d6efd);
            color: white;
            padding: 55px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-weight: 700;
        }

        .itinerary-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .itinerary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(0,0,0,0.12);
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }

        .section-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px;
            border: 1px solid #eef0f2;
        }

        .location-list li {
            margin-bottom: 8px;
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

        .meta-badge {
            font-size: 0.9rem;
            padding: 10px 14px;
            border-radius: 999px;
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

        .edit-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .location-option {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 12px 14px;
            height: 100%;
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
        <h1 class="display-5">My Saved Itineraries</h1>
        <p class="lead mb-0">View, edit, and delete the one-day travel plans you have already created</p>
    </div>
</section>

<div class="container pb-5">
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="itinerary.php" class="btn btn-outline-primary action-btn">← Back to Itinerary Generator</a>
        <a href="locations.php" class="btn btn-outline-secondary action-btn">Browse Locations</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($editMode): ?>
        <div class="card edit-card mb-5">
            <div class="card-body p-4 p-md-5">
                <h3 class="fw-bold mb-4">Edit Saved Itinerary</h3>

                <form method="POST">
                    <input type="hidden" name="itinerary_id" value="<?php echo $editItineraryId; ?>">

                    <div class="row g-3">
                        <?php if ($allLocationsResult && $allLocationsResult->num_rows > 0): ?>
                            <?php while ($location = $allLocationsResult->fetch_assoc()): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="location-option">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="locations[]"
                                                value="<?php echo $location['id']; ?>"
                                                id="location_<?php echo $location['id']; ?>"
                                                <?php echo in_array((int)$location['id'], $selectedLocationIds) ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label fw-bold" for="location_<?php echo $location['id']; ?>">
                                                <?php echo htmlspecialchars($location['name']); ?>
                                            </label>
                                        </div>

                                        <div class="mt-2">
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($location['category_name'] ?? 'No Category'); ?>
                                            </span>
                                        </div>

                                        <div class="mt-2 text-muted small">
                                            Distance: <?php echo htmlspecialchars($location['distance_km']); ?> km
                                        </div>

                                        <div class="text-muted small">
                                            Duration: <?php echo htmlspecialchars($location['visit_duration_minutes']); ?> min
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button type="submit" name="update_itinerary" class="btn btn-success action-btn">Update Itinerary</button>
                        <a href="saved-itineraries.php" class="btn btn-outline-secondary action-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($itinerary = $result->fetch_assoc()): ?>
                <div class="col-12">
                    <div class="card itinerary-card">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <span class="badge bg-info text-dark meta-badge mb-3">Saved Plan</span>
                                    <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($itinerary['title']); ?></h2>
                                    <p class="text-muted mb-0">
                                        Created on <?php echo htmlspecialchars($itinerary['created_at']); ?>
                                    </p>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <a href="saved-itineraries.php?edit=<?php echo $itinerary['id']; ?>" class="btn btn-warning action-btn">
                                        Edit
                                    </a>

                                    <a href="saved-itineraries.php?delete=<?php echo $itinerary['id']; ?>"
                                       class="btn btn-danger action-btn"
                                       onclick="return confirm('Are you sure you want to delete this itinerary?');">
                                        Delete
                                    </a>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-box">
                                        <strong>Total Distance:</strong><br>
                                        <?php echo htmlspecialchars($itinerary['total_distance']); ?> km
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="info-box">
                                        <strong>Total Duration:</strong><br>
                                        <?php echo htmlspecialchars($itinerary['total_duration_minutes']); ?> minutes
                                    </div>
                                </div>
                            </div>

                            <?php
                            $itineraryId = $itinerary['id'];

                            $locationSql = "SELECT itinerary_locations.visit_order, locations.name
                                            FROM itinerary_locations
                                            INNER JOIN locations ON itinerary_locations.location_id = locations.id
                                            WHERE itinerary_locations.itinerary_id = ?
                                            ORDER BY itinerary_locations.visit_order ASC";

                            $locationStmt = $conn->prepare($locationSql);
                            $locationStmt->bind_param("i", $itineraryId);
                            $locationStmt->execute();
                            $locationResult = $locationStmt->get_result();
                            ?>

                            <div class="section-box">
                                <h4 class="fw-bold mb-3">Locations in This Plan</h4>

                                <?php if ($locationResult && $locationResult->num_rows > 0): ?>
                                    <ol class="location-list mb-0">
                                        <?php while ($loc = $locationResult->fetch_assoc()): ?>
                                            <li>
                                                <strong>Stop <?php echo htmlspecialchars($loc['visit_order']); ?>:</strong>
                                                <?php echo htmlspecialchars($loc['name']); ?>
                                            </li>
                                        <?php endwhile; ?>
                                    </ol>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No locations found for this itinerary.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-box">
            <h3 class="fw-bold">No Saved Itineraries Found</h3>
            <p class="text-muted mb-3">You have not saved any itinerary yet.</p>
            <a href="itinerary.php" class="btn btn-primary action-btn">Create an Itinerary</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>