<?php
include 'auth-check.php';
include '../config/db.php';

$message = "";
$messageType = "";

// fetch categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $description = trim($_POST['description']);
    $distance_km = trim($_POST['distance_km']);
    $visit_duration_minutes = trim($_POST['visit_duration_minutes']);

    $imageName = "";

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
            $imageName = time() . "_" . $originalName;
            $targetFile = $uploadDir . $imageName;

            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedTypes)) {
                $message = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
                $messageType = "danger";
            } else {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $sql = "INSERT INTO locations (category_id, name, description, distance_km, visit_duration_minutes, image)
                            VALUES (?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);

                    if ($stmt) {
                        $stmt->bind_param(
                            "issdis",
                            $category_id,
                            $name,
                            $description,
                            $distance_km,
                            $visit_duration_minutes,
                            $imageName
                        );

                        if ($stmt->execute()) {
                            $message = "Location added successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Database insert failed.";
                            $messageType = "danger";
                        }
                    } else {
                        $message = "Failed to prepare database query.";
                        $messageType = "danger";
                    }
                } else {
                    $message = "Image upload failed.";
                    $messageType = "danger";
                }
            }
        } else {
            $message = "Please choose an image file.";
            $messageType = "warning";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Location</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }

        .page-header {
            background: linear-gradient(135deg, #198754, #20c997);
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
        <h1 class="display-6">Add New Location</h1>
        <p class="lead mb-0">Create a new tourist destination for your system</p>
    </div>
</section>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="card-body p-4 p-md-5">

                    <div class="mb-4">
                        <a href="dashboard.php" class="btn btn-outline-primary action-btn">← Back to Dashboard</a>
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
                                placeholder="Enter location name"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option
                                            value="<?php echo $category['id']; ?>"
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>
                                        >
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
                                placeholder="Enter location description"
                                required
                            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Distance (km)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="distance_km"
                                    class="form-control"
                                    placeholder="Enter distance"
                                    value="<?php echo isset($_POST['distance_km']) ? htmlspecialchars($_POST['distance_km']) : ''; ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Visit Duration (minutes)</label>
                                <input
                                    type="number"
                                    name="visit_duration_minutes"
                                    class="form-control"
                                    placeholder="Enter duration"
                                    value="<?php echo isset($_POST['visit_duration_minutes']) ? htmlspecialchars($_POST['visit_duration_minutes']) : ''; ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Upload Image</label>
                            <input
                                type="file"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp"
                                required
                            >
                            <div class="form-text">Allowed formats: JPG, JPEG, PNG, WEBP</div>
                        </div>

                        <div class="d-grid d-md-flex gap-3">
                            <button type="submit" class="btn btn-success action-btn">Save Location</button>
                            <a href="locations.php" class="btn btn-outline-secondary action-btn">View All Locations</a>
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