<?php
include 'auth-check.php';
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
    <title>Admin Locations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Admin Panel</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="add-location.php" class="btn btn-success btn-sm">+ Add Location</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Manage Locations</h2>
                    <p class="text-muted mb-0">View, edit, and delete tourist locations</p>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Location deleted successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Something went wrong. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Distance</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>

                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img
                                                src="../assets/images/<?php echo htmlspecialchars($row['image']); ?>"
                                                alt="<?php echo htmlspecialchars($row['name']); ?>"
                                                class="rounded"
                                                style="width: 70px; height: 55px; object-fit: cover;"
                                            >
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($row['category_name'] ?? 'No Category'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['distance_km']); ?> km</td>
                                    <td><?php echo htmlspecialchars($row['visit_duration_minutes']); ?> min</td>

                                    <td>
                                        <a href="edit-location.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm me-1">
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?php echo $row['id']; ?>"
                                        >
                                            Delete
                                        </button>

                                        <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content rounded-4">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete
                                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>
                                                        <a href="delete-location.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">
                                                            Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No locations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>