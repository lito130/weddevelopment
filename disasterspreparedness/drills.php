 <?php
session_start();
include 'connection.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_drill'])) {
    $drill_name   = trim($_POST['drill_name']);
    $location     = trim($_POST['location']);
    $date         = $_POST['date'];
    $participants = intval($_POST['participants']);
    $remarks      = trim($_POST['remarks']);

    if ($drill_name && $location && $date) {
        $stmt = $conn->prepare("INSERT INTO drills (drill_name, location, date, participants, remarks) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $drill_name, $location, $date, $participants, $remarks);
        $msg = $stmt->execute() ? "✅ Drill added successfully." : "❌ Error: " . $stmt->error;
        $stmt->close();
    } else {
        $msg = "⚠️ Please fill in required fields.";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_drill'])) {
    $id           = intval($_POST['id']);
    $drill_name   = trim($_POST['drill_name']);
    $location     = trim($_POST['location']);
    $date         = $_POST['date'];
    $participants = intval($_POST['participants']);
    $remarks      = trim($_POST['remarks']);

    if ($drill_name && $location && $date) {
        $stmt = $conn->prepare("UPDATE drills SET drill_name=?, location=?, date=?, participants=?, remarks=? WHERE id=?");
        $stmt->bind_param("sssdsi", $drill_name, $location, $date, $participants, $remarks, $id);
        $msg = $stmt->execute() ? "✏️ Drill updated successfully." : "❌ Error: " . $stmt->error;
        $stmt->close();
    } else {
        $msg = "⚠️ Please fill in required fields for editing.";
    }
}


if (isset($_GET['delete']) && $_SESSION['role'] === 'admin') {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM drills WHERE id = $id");
    $msg = "🗑️ Drill deleted successfully.";
}

$result = $conn->query("SELECT * FROM drills ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Disaster Preparedness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" type="image/png" href="images/disaster.png">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
        body { background-color: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .navbar-brand { font-size: 1.5rem; font-weight: 700; }
        .card { border-radius: 12px; border: none; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .table th { background-color: #f1f3f6; }
        .footer { text-align: center; margin-top: 50px; color: #6c757d; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h2 class="text-center mb-4">🚨 Safety Drills</h2>

  <?php if ($msg): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

     <div class="d-flex justify-content-end mb-3">
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDrillModal">➕ Add Drill</button>
    <?php endif; ?>
  </div>

  
  <div class="card shadow-lg">
    <div class="card-body">
      <div class="table-responsive">
      <table id="drillsTable" class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Drill Name</th>
            <th>Location</th>
            <th>Date</th>
            <th>Participants</th>
            <th>Remarks</th>
            <?php if ($_SESSION['role'] === 'admin'): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['drill_name']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td><?= htmlspecialchars($row['date']) ?></td>
              <td><?= $row['participants'] ?></td>
              <td><?= htmlspecialchars($row['remarks']) ?></td>
              <?php if ($_SESSION['role'] === 'admin'): ?>
              <td>
                <button class="btn btn-warning btn-sm editBtn"
                        data-id="<?= $row['id'] ?>"
                        data-drill_name="<?= htmlspecialchars($row['drill_name']) ?>"
                        data-location="<?= htmlspecialchars($row['location']) ?>"
                        data-date="<?= $row['date'] ?>"
                        data-participants="<?= $row['participants'] ?>"
                        data-remarks="<?= htmlspecialchars($row['remarks']) ?>"
                        data-bs-toggle="modal" data-bs-target="#editDrillModal"><i class="fas fa-edit"></i></button>
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this drill?')"><i class="fas fa-trash"> </i></a>
              </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
<div class="modal fade" id="addDrillModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">➕ Add Safety Drill</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Drill Name *</label>
            <input type="text" name="drill_name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Location *</label>
            <input type="text" name="location" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Date *</label>
            <input type="date" name="date" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Number of Participants</label>
            <input type="number" name="participants" class="form-control" min="0"></div>
          <div class="mb-3"><label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Close</button>
          <button type="submit" name="add_drill" class="btn btn-primary">💾 Save Drill</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Drill Modal -->
<div class="modal fade" id="editDrillModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">✏ Edit Safety Drill</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3"><label class="form-label">Drill Name *</label>
            <input type="text" name="drill_name" id="edit_drill_name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Location *</label>
            <input type="text" name="location" id="edit_location" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Date *</label>
            <input type="date" name="date" id="edit_date" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Number of Participants</label>
            <input type="number" name="participants" id="edit_participants" class="form-control" min="0"></div>
          <div class="mb-3"><label class="form-label">Remarks</label>
            <textarea name="remarks" id="edit_remarks" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Close</button>
          <button type="submit" name="edit_drill" class="btn btn-success">✅ Update Drill</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="footer">
   <strong>&copy; <?= date('Y') ?><b> Disaster Preparedness System. All rights reserved.</b></strong>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function() {
    
    $('#drillsTable').DataTable({
      "pageLength": 5,
      "lengthMenu": [5, 10, 25, 50, 100],
      "ordering": true,
      "searching": true
    });

  
    $('#drillsTable').on('click', '.editBtn', function() {
      
      $('#edit_id').val($(this).data('id'));
      $('#edit_drill_name').val($(this).data('drill_name'));
      $('#edit_location').val($(this).data('location'));
      $('#edit_date').val($(this).data('date'));
      $('#edit_participants').val($(this).data('participants'));
      $('#edit_remarks').val($(this).data('remarks'));
    });
  });
</script>

</body>
</html>