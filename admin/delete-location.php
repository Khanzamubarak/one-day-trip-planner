<?php
include 'auth-check.php';
include '../config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: locations.php?error=invalid");
    exit();
}

$id = (int) $_GET['id'];

// optional: get image name to delete file later
$getSql = "SELECT image FROM locations WHERE id = ?";
$getStmt = $conn->prepare($getSql);
$getStmt->bind_param("i", $id);
$getStmt->execute();
$getResult = $getStmt->get_result();
$location = $getResult->fetch_assoc();

$sql = "DELETE FROM locations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    // optional: delete image file from folder
    if ($location && !empty($location['image'])) {
        $imagePath = "../assets/images/" . $location['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // delete file
        }
    }

    header("Location: locations.php?success=deleted");
    exit();

} else {
    header("Location: locations.php?error=failed");
    exit();
}