<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['username']) && !isset($_SESSION['full_name']) && !isset($_SESSION['admin_name'])) {
    header("Location: login.php");
    exit(); 
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-lg mb-4">
    <div class="container-fluid">
        
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="fas fa-shield-alt me-1"></i> Disaster Preparedness
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"
                       href="dashboard.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'disaster.php' ? 'active' : '' ?>"
                       href="disaster.php">
                        <i class="fas fa-exclamation-triangle me-1"></i> Disaster Records
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : '' ?>"
                       href="inventory.php">
                        <i class="fas fa-boxes me-1"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'drills.php' ? 'active' : '' ?>"
                       href="drills.php">
                        <i class="fas fa-bullhorn me-1"></i> Drills
                    </a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_user.php' ? 'active' : '' ?>"
                       href="manage_user.php">
                        <i class="fas fa-users-cog me-1"></i> Manage Users
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none text-white dropdown-toggle"
                   id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fa-lg me-2" style="font-size: 1.5rem;"></i>
                    <span class="fw-semibold">
                        <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['admin_name'] ?? $_SESSION['username'] ?? 'User') ?>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border" aria-labelledby="userDropdown" style="width: 200px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="profile.php">
                            <i class="fas fa-user me-2 opacity-75"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            <span><strong>Logout</strong></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 0.5rem 1rem;
    }

    .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
    }

    .dropdown-item:hover {
        background-color: #e9ecef;
    }

    .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
        color: #dc3545 !important;
    }
</style>

