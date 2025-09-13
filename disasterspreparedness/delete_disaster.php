<?php
session_start();
include 'connection.php';
include 'auth_check.php';
require_admin();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); /

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete'])) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Invalid request.'];
    header("Location: dashboard.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid ID.'];
    header("Location: dashboard.php");
    exit;
}


$check = $conn->prepare("SELECT disaster_type, location FROM disasters WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Record not found.'];
} else {
    $row = $result->fetch_assoc();

    
    $stmt = $conn->prepare("DELETE FROM disasters WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "🗑️ Deleted '{$row['disaster_type']}' at '{$row['location']}'."
        ];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Delete failed: ' . $stmt->error];
    }
    $stmt->close();
}

$check->close();
header("Location: dashboard.php");
exit;
?>
