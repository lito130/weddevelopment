<?php
session_start(); 
include 'auth_check.php';
require_admin();
include 'connection.php';

if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Username already exists.'];
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $full_name, $role);
        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'User added successfully.'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to add user.'];
        }
        $stmt->close();
    }
    $check->close();
    header("Location: manage_user.php");
    exit;
}


if (isset($_POST['edit_user'])) {
    $id = (int)$_POST['id'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    
    if ($id == $_SESSION['user_id'] && $role !== 'admin') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'You cannot remove admin role from your own account.'];
    } else {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name=?, role=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $full_name, $role, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, role=? WHERE id=?");
            $stmt->bind_param("ssi", $full_name, $role, $id);
        }
        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'User updated successfully.'];
        } else {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to update user.'];
        }
        $stmt->close();
    }
    header("Location: manage_user.php");
    exit;
}

// Delete User
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Prevent self-delete
    if ($id == $_SESSION['user_id']) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'You cannot delete your own account.'];
    } else {
        // Optional: Prevent deletion if it's the last admin
        $admin_check = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='admin'");
        $admin_row = $admin_check->fetch_assoc();
        $is_admin = $conn->query("SELECT role FROM users WHERE id=$id")->fetch_assoc()['role'];

        if ($is_admin === 'admin' && $admin_row['cnt'] <= 1) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Cannot delete the last admin user.'];
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['flash'] = ['type' => 'warning', 'message' => 'User deleted successfully.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Failed to delete user.'];
            }
            $stmt->close();
        }
    }
    header("Location: manage_user.php");
    exit;
}

// Pagination & Search
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = in_array($limit, [5, 10, 25, 50]) ? $limit : 10;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = $search ? "&search=" . urlencode($search) : '';
$limit_param = "&limit=" . $limit;

$where = "1=1";
if (!empty($search)) {
    $search_like = "%" . $conn->real_escape_string($search) . "%";
    $where .= " AND (username LIKE '$search_like' OR full_name LIKE '$search_like' OR role LIKE '$search_like')";
}

$total_result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE $where");
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

$start = ($page - 1) * $limit;
$users_query = "SELECT * FROM users WHERE $where ORDER BY id DESC LIMIT $start, $limit";
$users = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Disaster Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="images/disaster.png">

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .table th {
            background-color: #f1f3f6;
            font-weight: 500;
        }
        .btn-sm i {
            font-size: 0.9em;
        }
        .badge.bg-primary, .badge.bg-secondary {
            font-size: 0.85em;
            padding: 0.5em 0.8em;
        }
        .pagination .page-link {
            color: #0d6efd;
            margin: 0 2px;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #e9ecef;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px 0;
            color: #6c757d;
            font-size: 0.95rem;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .form-select-sm {
            width: auto;
            display: inline-block;
        }
    </style>
</head>
<body>


<?php include 'navbar.php'; ?>
<?php if (isset($_SESSION['flash'])): ?>
    <?php $flash = $_SESSION['flash']; ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show mx-4 mt-3" role="alert">
        <strong>
            <?= ['success' => '✅', 'danger' => '❌', 'warning' => '⚠️', 'info' => 'ℹ️'][$flash['type']] ?? '📢' ?>
        </strong>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">👤 User Management</h2>
        <button class="btn btn-primary btn-lg px-4" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-user-plus me-1"></i> Add User
        </button>
    </div>

    
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-lg me-2"
                       placeholder="Search users by username, name, or role..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-outline-secondary btn-lg" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Items Per Page Selector -->
    <div class="d-flex justify-content-end mb-3">
        <label for="limit-select" class="me-2">Show:</label>
        <select id="limit-select" class="form-select form-select-sm" style="width: auto;" onchange="updateLimit(this.value)">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="table-responsive bg-white shadow-sm rounded mb-4">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users->num_rows > 0): ?>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['role'] === 'admin' ? 'primary' : 'secondary' ?> rounded-pill">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $row['id'] ?>">
                                    <i class="fas fa-edit"></i> 
                                </button>
                                <a href="?delete=<?= $row['id'] ?><?= $search_param ?><?= $limit_param ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i> 
                                </a>
                            </td>
                        </tr>

                        
                        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit User: <?= htmlspecialchars($row['username']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($row['full_name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <select name="role" class="form-control">
                                                    <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    <option value="viewer" <?= $row['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">New Password (leave blank to keep current)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Enter new password">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="edit_user" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Changes
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <em>No users found matching your search.</em>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if ($total_pages > 1): ?>
        <nav aria-label="User pagination" class="mt-4">
            <ul class="pagination justify-content-center flex-wrap" style="list-style: none; padding: 0;">
                <!-- Previous -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $search_param ?><?= $limit_param ?>">Previous</a>
                </li>

                
                <?php $start_page = max(1, $page - 2); ?>
                <?php $end_page = min($total_pages, $page + 2); ?>

                <?php if ($start_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=1<?= $search_param ?><?= $limit_param ?>">1</a></li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php endif; ?>
                <?php endif; ?>

            
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $search_param ?><?= $limit_param ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $total_pages ?><?= $search_param ?><?= $limit_param ?>"><?= $total_pages ?></a></li>
                <?php endif; ?>

                
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $search_param ?><?= $limit_param ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

    
    <div class="text-center text-muted mt-3">
        Showing <?= max(1, $start + 1) ?> to <?= min($start + $limit, $total_users) ?> of <?= $total_users ?> users
    </div>

    
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control">
                                <option value="admin">Admin</option>
                                <option value="viewer">Viewer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_user" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Add User
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="footer">
    <strong>&copy; <?= date('Y') ?> <b>Disaster Preparedness System. All rights reserved.</b></strong>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
   
    function updateLimit(value) {
        const search = new URLSearchParams(window.location.search);
        search.set('limit', value);    // Update limit
        search.set('page', '1');       // Reset page to 1
        window.location.search = search.toString(); // Reload with new params
    }

   
    function autoCloseAlerts(timeout = 5000) {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                try {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                } catch (e) {
                    console.warn("Bootstrap alert instance not found:", e);
                }
            });
        }, timeout);
    }


    autoCloseAlerts();
</script>


</body>
</html>