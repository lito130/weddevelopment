<?php
session_start();
require 'connection.php'; 

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $err = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;
            } else {
                $err = "Invalid username or password.";
            }
        } else {
            $err = "Invalid username or password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Disaster Preparedness</title>
    <link rel="shortcut icon" type="image/png" href="images/disaster.png">
    <link href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-label {
            font-weight: 500;
            font-size: 1.1rem; /* Slightly larger label */
        }
        .btn-primary {
            padding: 14px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .img-fluid {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
            object-position: center;
        }
        @media (max-width: 767.98px) {
            .img-fluid {
                height: 200px !important;
                object-fit: cover;
            }
        }

        
        .input-group-text {
            font-size: 1.3rem;
            padding: 1.2rem 1rem; /* Larger padding for bigger touch area */
            background-color: #f8f9fa;
            border: 2px solid #ced4da;
        }

        
        #togglePasswordBtn {
            font-size: 1.2rem;
            padding: 0 16px;
            border: 2px solid #ced4da;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .form-control-lg {
            font-size: 1.15rem;
            padding: 1.2rem 1rem;
        }
    </style>
</head>
<body class="bg-light">
<section class="p-3 p-md-4 p-xl-5 login-section">
    <div class="container">
        <div class="card border-light-subtle shadow-sm">
            <div class="row g-0">

                <!-- Left Side: Image -->
                <div class="col-12 col-md-6">
                    <img 
                        class="img-fluid w-100 h-100 object-fit-cover" 
                        loading="lazy" 
                        src="images/preparedness1_cleanup.jpg" 
                        alt="Safety Preparedness" 
                        style="min-height: 300px;">
                </div>

                
                <div class="col-12 col-md-6 d-flex align-items-center">
                    <div class="card-body p-4 p-md-5">

                        <div class="mb-4">
                            <h3 class="fw-bold">Log in</h3>
                            <p class="text-secondary mb-0">Welcome back! Please enter your details.</p>
                        </div>

                        
                        <?php if (!empty($err)): ?>
                            <div class="alert alert-danger text-center mb-4">
                                <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        
                        <form method="post">
                            <div class="row gy-3 gy-md-4 overflow-hidden">

                                
                                <div class="col-12">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input 
                                            type="text" 
                                            class="form-control form-control-lg" 
                                            name="username" 
                                            id="username" 
                                            placeholder="Enter your username"
                                            value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                            required>
                                    </div>
                                </div>

                                
                                <div class="col-12">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input 
                                            type="password" 
                                            class="form-control form-control-lg" 
                                            name="password" 
                                            id="password" 
                                            placeholder="Enter password"
                                            required
                                            autocomplete="off">
                                        <button 
                                            class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePasswordBtn" 
                                            aria-label="Show password"
                                            aria-pressed="false">
                                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button class="btn btn-primary btn-lg" type="submit">Log in now</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</section>

<script src="https://unpkg.com/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("password");
    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const togglePasswordIcon = document.getElementById("togglePasswordIcon");

    togglePasswordBtn.addEventListener("click", function () {
        const type = passwordInput.type === "password" ? "text" : "password";
        passwordInput.type = type;

        togglePasswordIcon.classList.toggle("fa-eye");
        togglePasswordIcon.classList.toggle("fa-eye-slash");

        const isHidden = passwordInput.type === "password";
        this.setAttribute("aria-label", isHidden ? "Show password" : "Hide password");
        this.setAttribute("aria-pressed", !isHidden);
    });
});
</script>
</body>
</html>