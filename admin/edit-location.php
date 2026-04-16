<?php
include 'auth-check.php';
include '../config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid location ID.");
}

$id = (int) $_GET['id'];
$message = "";
$messageType = "";

// fetch categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

// fetch existing location
$sql = "SELECT * FROM locations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$location = $result->fetch_assoc();

if (!$location) {
    die("Location not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $description = trim($_POST['description']);
    $distance_km = trim($_POST['distance_km']);
    $visit_duration_minutes = trim($_POST['visit_duration_minutes']);

    $imageName = $location['image'];

    if (
        empty($name) ||
        empty($category_id) ||
        empty($description) ||
        $distance_km === "" ||
        $visit_duration_minutes === ""
    ) {
        $message = "All required fields must be filled.";
        $messageType = "danger";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $uploadDir = "../assets/images/";
            $originalName = basename($_FILES["image"]["name"]);
            $newImageName = time() . "_" . $originalName;
            $targetFile = $uploadDir . $newImageName;

            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedTypes)) {
                $message = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
                $messageType = "danger";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $imageName = $newImageName;
                } else {
                    $message = "Image upload failed.";
                    $messageType = "danger";
                }
            }
        }

        if (empty($message)) {
            $updateSql = "UPDATE locations
                          SET category_id = ?, name = ?, description = ?, distance_km = ?, visit_duration_minutes = ?, image = ?
                          WHERE id = ?";

            $updateStmt = $conn->prepare($updateSql);

            if ($updateStmt) {
                $updateStmt->bind_param(
                    "issdisi",
                    $category_id,
                    $name,
                    $description,
                    $distance_km,
                    $visit_duration_minutes,
                    $imageName,
                    $id
                );

                if ($updateStmt->execute()) {
                    $message = "Location updated successfully.";
                    $messageType = "success";

                    $stmt = $conn->prepare("SELECT * FROM locations WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $location = $result->fetch_assoc();
                } else {
                    $message = "Failed to update location.";
                    $messageType = "danger";
                }
            } else {
                $message = "Failed to prepare update query.";
                $messageType = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Location</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .page-header {
            background: linear-gradient(135deg, #fd7e14, #ffc107);
            color: white;
            padding: 50px 0;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-weight: 700;
        }

        .form-card {
            border: none;
            border-radius: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .navbar-brand {
            font-weight: 700;
        }

        .action-btn {
            border-radius: 999px;
            padding: 10px 20px;
        }

        .form-label {
            font-weight: 600;
        }

        .current-image-box {
            background: #f8f9fa;
            border-radius: 18px;
            padding: 16px;
        }

        .current-image-box img {
            max-width: 100%;
            width: 280px;
            height: 200px;
            object-fit: cover;
            border-radius: 14px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.10);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
        <div class="ms-auto d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="locations.php" class="btn btn-outline-light btn-sm">Manage Locations</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<section class="page-header">
    <div class="container text-center">
        <h1 class="display-6">Edit Location</h1>
        <p class="lead mb-0">Update destination details and image</p>
    </div>
</section>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-body p-4 p-md-5">

                    <div class="mb-4">
                        <a href="locations.php" class="btn btn-outline-primary action-btn">← Back to Manage Locations</a>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Location Name</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($location['name']); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                            <?php echo ($location['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea
                                name="description"
                                class="form-control"
                                rows="5"
                                required
                            ><?php echo htmlspecialchars($location['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Distance (km)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="distance_km"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($location['distance_km']); ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visit Duration (minutes)</label>
                                <input
                                    type="number"
                                    name="visit_duration_minutes"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($location['visit_duration_minutes']); ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Current Image</label>
                            <div class="current-image-box">
                                <img
                                    src="../assets/images/<?php echo htmlspecialchars($location['image']); ?>"
                                    alt="<?php echo htmlspecialchars($location['name']); ?>"
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Upload New Image</label>
                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                            >
                            <div class="form-text">Leave empty if you want to keep the current image.</div>
                        </div>

                        <div class="d-grid d-md-flex gap-3">
                            <button type="submit" class="btn btn-warning action-btn">Update Location</button>
                            <a href="locations.php" class="btn btn-outline-secondary action-btn">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>