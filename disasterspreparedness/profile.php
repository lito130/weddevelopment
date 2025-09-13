<?php
include 'auth_check.php';
require_login(); 
include 'connection.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Disaster Preparedness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" type="image/png" href="images/disaster.png">
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.3);
            margin-top: -55px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-body {
            padding: 25px;
        }
        .info-item {
            display: flex;
            margin-bottom: 18px;
            font-size: 1.05rem;
        }
        .info-icon {
            width: 36px;
            height: 36px;
            background: #f8f9fc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4e73df;
            font-size: 1.1rem;
            margin-right: 12px;
        }
        .info-text {
            flex: 1;
            padding-top: 3px;
            color: #495057;
        }
        .btn-outline-primary {
            border-radius: 8px;
            font-weight: 500;
        }
        .role-badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 50px;
            background: #e7f1ff;
            color: #224abe;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4 text-primary">👤 My Profile</h2>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card profile-card">
                    
                    <div class="profile-header">
                        <h4><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></h4>
                    </div>

                    
                    <div class="profile-body">
                        
                        <div class="text-center mb-4">
                            <i class="fas fa-user-circle fa-7x text-primary"></i>
                        </div>

                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-text">
                                <strong>Full Name</strong><br>
                                <?= htmlspecialchars($_SESSION['full_name'] ?? 'Not set') ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <div class="info-text">
                                <strong>Username</strong><br>
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="info-text">
                                <strong>Role</strong><br>
                                <span class="role-badge">
                                    <?= ucfirst($_SESSION['role'] ?? 'user') ?>
                                </span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-text">
                                <strong>Member Since</strong><br>
                                <?php
                                $stmt = $conn->prepare("SELECT created_at FROM users WHERE username = ?");
                                $stmt->bind_param("s", $_SESSION['username']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $user = $result->fetch_assoc();
                                echo $user ? date('F j, Y', strtotime($user['created_at'])) : 'Unknown';
                                $stmt->close();
                                ?>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="logout.php" class="btn btn-outline-primary btn-lg px-4">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="footer mt-5 text-center py-3 text-muted">
        &copy; <?= date('Y') ?> <strong>Disaster Preparedness System</strong>. All rights reserved.
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>