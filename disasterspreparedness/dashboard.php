 <?php
session_start();
include 'auth_check.php';
require_login();
include 'connection.php';

$msg = '';

$total_disasters = $conn->query("SELECT COUNT(*) as cnt FROM disasters")->fetch_assoc()['cnt'];
$total_types = $conn->query("SELECT COUNT(DISTINCT disaster_type) as cnt FROM disasters")->fetch_assoc()['cnt'];

$latest = $conn->query("SELECT * FROM disasters ORDER BY date_reported DESC LIMIT 100");
if (!$latest) {
    $msg = "❌ Error loading reports: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Disaster Dashboard</title>
  <link rel="shortcut icon" type="image/png" href="images/disaster.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    body {
      background-color: #f8f9fa;
    }

    .card {
      border: none;
      border-radius: 12px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .card-body h3 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #0d6efd;
      margin-bottom: 0.5rem;
    }
    .card-body p {
      font-size: 1.1rem;
      color: #555;
    }

    .table thead th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #333;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      padding: 12px;
      border-bottom: 2px solid #dee2e6;
    }

    .table tbody td {
      padding: 14px 12px;
      vertical-align: middle;
      border-top: 1px solid #dee2e6;
    }

    .table tbody tr:hover {
      background-color: #f1f7ff;
    }

    @media (max-width: 768px) {
      .table thead { display: none; }
      .table tbody tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
      }
      .table tbody td {
        display: block;
        text-align: right;
        padding: 8px;
        position: relative;
        padding-left: 120px;
        border: none;
      }
      .table tbody td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 100px;
        font-weight: bold;
        color: #333;
      }
    }

    .footer {
      text-align: center;
      padding: 20px;
      margin-top: 40px;
      font-size: 0.9rem;
      color: #666;
      border-top: 1px solid #eee;
      background-color: #f8f9fa;
    }

    .dashboard-container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .time-display {
      font-size: 0.95rem;
      line-height: 1.5;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container-fluid px-4">
  <div class="dashboard-container">
    <h2 class="text-center mb-3">🛡️ Disaster Preparedness Dashboard</h2>
    <div class="text-center mb-4 p-3 bg-white rounded shadow-sm" style="border: 1px solid #dee2e6;">
        <small class="text-muted">
          <i class="fas fa-clock me-1"></i>
          <strong>Local Time:</strong>
          <span id="localTime" style="color: #0d6efd; font-weight: 500;">Loading...</span>
        </small>
      
    </div>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-info alert-dismissible fade show mx-3 mt-3" role="alert">
        <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash'])): ?>
      <?php $flash = $_SESSION['flash']; ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show mx-3 mt-3" role="alert">
        <strong><?= ['success'=>'✅','danger'=>'❌','warning'=>'⚠️','info'=>'ℹ️'][$flash['type']] ?? '📢' ?></strong>
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>


    <div class="row g-4 mb-5 justify-content-center">
      <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center p-4">
            <h3><?= (int)$total_disasters ?></h3>
            <p class="text-muted mb-0">Total Disaster Reports</p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center p-4">
            <h3><?= (int)$total_types ?></h3>
            <p class="text-muted mb-0">Unique Disaster Types</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Button -->
    <div class="d-flex justify-content-end mb-3">
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <button class="btn btn-primary btn-lg px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
          <i class="fas fa-plus-circle me-1"></i> Add Record
        </button>
      <?php endif; ?>
    </div>

  
    <h4 class="mb-3"><i class="fas fa-clock me-1 text-primary"></i> Latest Reports</h4>
    <div class="table-container px-3">
      <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Disaster Type</th>
              <th>Location</th>
              <th>Contact</th>
              <th>Date Reported</th>
              <?php if ($_SESSION['role'] === 'admin'): ?>
                <th>Actions</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if ($latest && $latest->num_rows > 0): ?>
              <?php while ($row = $latest->fetch_assoc()): ?>
                <tr>
                  <td data-label="Disaster Type"><?= htmlspecialchars($row['disaster_type']) ?></td>
                  <td data-label="Location"><?= htmlspecialchars($row['location']) ?></td>
                  <td data-label="Contact"><?= htmlspecialchars($row['emergency_contact']) ?></td>
                  <td data-label="Date Reported"><?= date('F j, Y \a\t h:i A', strtotime($row['date_reported'])) ?></td>
                  <?php if ($_SESSION['role'] === 'admin'): ?>
                    <td data-label="Actions" class="text-center">
                      <button class="btn btn-warning btn-sm editBtn me-1"
                              data-id="<?= (int)$row['id'] ?>"
                              data-type="<?= htmlspecialchars($row['disaster_type']) ?>"
                              data-location="<?= htmlspecialchars($row['location']) ?>"
                              data-contact="<?= htmlspecialchars($row['emergency_contact']) ?>"
                              data-datereported="<?= date('Y-m-d\TH:i', strtotime($row['date_reported'])) ?>"
                              data-bs-toggle="modal"
                              data-bs-target="#editModal">
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="POST" action="delete_disaster.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <input type="hidden" name="delete" value="1">
                        <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this report?');">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= $_SESSION['role'] === 'admin' ? 5 : 4 ?>" class="text-center text-muted">
                  No reports found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="modal fade" id="addModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" action="add_disaster.php">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New Report</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Disaster Type *</label>
                <input type="text" name="disaster_type" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Location *</label>
                <input type="text" name="location" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Emergency Contact *</label>
                <input type="text" name="emergency_contact" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Date Reported *</label>
                <input type="datetime-local" name="date_reported" class="form-control" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" name="submit" class="btn btn-success">💾 Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>


    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" action="edit_disaster.php">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Report</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" id="edit_id" />
              <div class="mb-3">
                <label class="form-label">Disaster Type *</label>
                <input type="text" name="disaster_type" id="edit_type" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Location *</label>
                <input type="text" name="location" id="edit_location" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Emergency Contact *</label>
                <input type="text" name="emergency_contact" id="edit_contact" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Date Reported *</label>
                <input type="datetime-local" name="date_reported" id="edit_date_reported" class="form-control" required />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" name="update" class="btn btn-primary">✅ Update</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<div class="footer">
  &copy; <?= date('Y') ?> <strong>Disaster Preparedness System.</strong> All rights reserved.
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  // Initialize DataTables
  $('#dataTable').DataTable({
    "order": [[3, "desc"]],
    "pageLength": 5,
    "lengthMenu": [[5, 10, 25, 100], [5, 10, 25, 100]],
    "language": {
      "search": "Search:",
      "lengthMenu": "Show _MENU_ entries",
      "zeroRecords": "No matching records found",
      "info": "Showing _START_ to _END_ of _TOTAL_ reports",
      "infoEmpty": "No reports available",
      "infoFiltered": "(filtered from _MAX_ total reports)"
    }
  });

  // Update local time every second
  function updateLocalTime() {
    const options = { 
      year: 'numeric', month: 'long', day: 'numeric',
      hour: '2-digit', minute: '2-digit', second: '2-digit',
      hour12: true
    };
    const now = new Date();
    document.getElementById('localTime').textContent = now.toLocaleString(undefined, options);
  }

  updateLocalTime();
  setInterval(updateLocalTime, 1000);

  // Edit button click (use .attr to avoid stale .data() cache from jQuery)
  $(document).on('click', '.editBtn', function () {
    const button = $(this);
    $('#edit_id').val(button.attr('data-id'));
    $('#edit_type').val(button.attr('data-type'));
    $('#edit_location').val(button.attr('data-location'));
    $('#edit_contact').val(button.attr('data-contact'));

    const rawDate = button.attr('data-datereported');
    if (rawDate) {
      const formatted = new Date(rawDate.replace(' ', 'T')).toISOString().slice(0, 16);
      $('#edit_date_reported').val(formatted);
    }
  });
});
</script>


</body>
</html>