 <?php

session_start();
include 'connection.php';
include 'auth_check.php'; 
require_admin(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $disaster_type = $_POST['disaster_type'];
    $location = $_POST['location'];
    $emergency_contact = $_POST['emergency_contact'];
    $date_reported = $_POST['date_reported'];


    $date_reported = date('Y-m-d H:i:s', strtotime($date_reported));
    $stmt = $conn->prepare("INSERT INTO disasters (disaster_type, location, emergency_contact, date_reported) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $disaster_type, $location, $emergency_contact, $date_reported);
    $stmt->execute();

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'New disaster report added.'];
    header("Location: dashboard.php");
    exit;
}


?>