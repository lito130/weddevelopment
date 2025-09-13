 <?php
session_start();
include 'connection.php';
include 'auth_check.php';


require_admin();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $disaster_type = $_POST['disaster_type'];
    $location = $_POST['location'];
    $emergency_contact = $_POST['emergency_contact'];
    $date_reported = $_POST['date_reported'];

    $date_reported = date('Y-m-d H:i:s', strtotime($date_reported));

    $stmt = $conn->prepare("UPDATE disasters SET disaster_type=?, location=?, emergency_contact=?, date_reported=? WHERE id=?");
    $stmt->bind_param("ssssi", $disaster_type, $location, $emergency_contact, $date_reported, $id);
    $stmt->execute();

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Disaster report updated.'];
    header("Location: dashboard.php");
    exit;
}

?>