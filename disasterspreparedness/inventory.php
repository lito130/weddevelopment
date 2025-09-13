<?php
session_start();
include 'auth_check.php';
require_login();
include 'connection.php';

$msg = '';


if (isset($_POST['add']) && $_SESSION['role'] === 'admin') {
    $item = trim($_POST['item_name']);
    $qty = intval($_POST['quantity']);
    $location = trim($_POST['location']);
    $date_added = $_POST['date_added'];

    if ($item && $qty > 0 && $location && $date_added) {
        $date_added = date('Y-m-d H:i:s', strtotime($date_added));
        $stmt = $conn->prepare("INSERT INTO inventory (item_name, quantity, location, date_added) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("siss", $item, $qty, $location, $date_added);
            $msg = $stmt->execute() ? "✅ Stock added successfully." : "❌ Error: " . $stmt->error;
            $stmt->close();
        } else {
            $msg = "❌ Error preparing statement.";
        }
    } else {
        $msg = "⚠️ Please fill in all required fields.";
    }
}


if (isset($_POST['edit']) && $_SESSION['role'] === 'admin') {
    $id = intval($_POST['id']);
    $item = trim($_POST['item_name']);
    $qty = intval($_POST['quantity']);
    $location = trim($_POST['location']);
    $date_added = $_POST['date_added'];

    if ($item && $qty >= 0 && $location && $date_added) {
        $date_added = date('Y-m-d H:i:s', strtotime($date_added));
        $stmt = $conn->prepare("UPDATE inventory SET item_name=?, quantity=?, location=?, date_added=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sissi", $item, $qty, $location, $date_added, $id);
            $msg = $stmt->execute() ? "✏️ Stock updated successfully." : "❌ Error: " . $stmt->error;
            $stmt->close();
        } else {
            $msg = "❌ Error preparing statement.";
        }
    } else {
        $msg = "⚠️ Please fill in all required fields.";
    }
}


if (isset($_GET['delete']) && $_SESSION['role'] === 'admin') {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $msg = $stmt->execute() ? "🗑️ Stock deleted successfully." : "❌ Error: " . $stmt->error;
        $stmt->close();
    } else {
        $msg = "❌ Error preparing delete statement.";
    }
}


$result = $conn->query("SELECT * FROM inventory ORDER BY date_added DESC");
if (!$result) {
    $msg = "❌ Error fetching data: " . $conn->error;
}


$summary = [
    'total_items' => 0,
    'total_quantity' => 0,
    'total_locations' => 0
];

$res = $conn->query("SELECT COUNT(DISTINCT item_name) as items, SUM(quantity) as qty, COUNT(DISTINCT location) as locations FROM inventory");
if ($res && $row = $res->fetch_assoc()) {
    $summary['total_items'] = $row['items'] ?? 0;
    $summary['total_quantity'] = $row['qty'] ?? 0;
    $summary['total_locations'] = $row['locations'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Disaster Preparedness</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="shortcut icon" type="image/png" href="images/disaster.png" />
  <style>
    body { background-color: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
    .card { border-radius: 12px; border: none; transition: transform 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .card:hover { transform: translateY(-5px); }
    .table th { background-color: #f1f3f6; font-weight: 600; color: #333; }
    .footer { text-align: center; margin-top: 50px; color: #6c757d; font-size: 0.9rem; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
  <h2 class="text-center mb-4">📦 Inventory of Disaster Stock</h2>

  <?php if (!empty($msg)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row mb-4">
  <div class="col-md-4">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <h5 class="card-title">🏷️ Total Items</h5>
        <p class="fs-4 fw-bold"><?= number_format($summary['total_items']) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <h5 class="card-title">📦 Total Quantity</h5>
        <p class="fs-4 fw-bold"><?= number_format($summary['total_quantity']) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <h5 class="card-title">📍 Locations</h5>
        <p class="fs-4 fw-bold"><?= number_format($summary['total_locations']) ?></p>
      </div>
    </div>
  </div>
</div>

  
  <div class="d-flex justify-content-end mb-3">
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Stock</button>
    <?php endif; ?>
  </div>

  <div class="card shadow-lg">
    <div class="card-body">
      <div class="table-responsive">
        <table id="inventoryTable" class="table table-bordered table-hover align-middle">
          <thead>
            <tr>
              <th>Item Name</th><th>Quantity</th><th>Location</th><th>Date Added</th>
              <?php if ($_SESSION['role'] === 'admin'): ?><th>Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
  <?php $lowStock = ($row['quantity'] < 10); // threshold ?>
  <tr class="<?= $lowStock ? 'table-warning' : '' ?>">
    <td><?= htmlspecialchars($row['item_name']) ?></td>
    <td>
      <?= $row['quantity'] ?>
      <?php if ($lowStock): ?>
        <span class="badge bg-danger ms-2">Low Stock!</span>
      <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($row['location']) ?></td>
    <td><?= date('F j, Y \a\t g:i A', strtotime($row['date_added'])) ?></td>
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <td>
        <button class="btn btn-warning btn-sm editBtn"
                data-id="<?= $row['id'] ?>"
                data-item_name="<?= htmlspecialchars($row['item_name'], ENT_QUOTES) ?>"
                data-quantity="<?= $row['quantity'] ?>"
                data-location="<?= htmlspecialchars($row['location'], ENT_QUOTES) ?>"
                data-date_added="<?= $row['date_added'] ?>"
                data-bs-toggle="modal" data-bs-target="#editModal">
          <i class="fas fa-edit"></i>
        </button>
        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this stock?')">
          <i class="fas fa-trash"></i>
        </a>
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


<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
      <form method="post">
        <div class="modal-header"><h5 class="modal-title">➕ Add New Stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Item Name *</label><input type="text" name="item_name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" required></div>
          <div class="mb-3"><label class="form-label">Location *</label><input type="text" name="location" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Date Added *</label><input type="datetime-local" name="date_added" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Close</button><button type="submit" name="add" class="btn btn-success">💾 Add Stock</button></div>
      </form>
  </div></div>
</div>


<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
      <form method="post">
        <div class="modal-header"><h5 class="modal-title">✏ Edit Stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3"><label class="form-label">Item Name *</label><input type="text" name="item_name" id="edit_item_name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Quantity *</label><input type="number" name="quantity" id="edit_quantity" class="form-control" min="0" required></div>
          <div class="mb-3"><label class="form-label">Location *</label><input type="text" name="location" id="edit_location" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Date Added *</label><input type="datetime-local" name="date_added" id="edit_date_added" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Close</button><button type="submit" name="edit" class="btn btn-primary">✅ Update Stock</button></div>
      </form>
  </div></div>
</div>

<div class="footer"><strong>&copy; <?= date('Y') ?> <b>Disaster Preparedness System. All rights reserved.</b></strong></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
  $('#inventoryTable').DataTable({ pageLength: 5, lengthMenu: [5,10,25,50,100] });

  $(document).on('click', '.editBtn', function () {
    const id = $(this).attr('data-id');
    const item = $(this).attr('data-item_name');
    const qty = $(this).attr('data-quantity');
    const loc = $(this).attr('data-location');
    const dateRaw = $(this).attr('data-date_added');

    $('#edit_id').val(id);
    $('#edit_item_name').val(item);
    $('#edit_quantity').val(qty);
    $('#edit_location').val(loc);

    if (dateRaw) {
      const dt = new Date(dateRaw.replace(' ', 'T'));
      $('#edit_date_added').val(dt.toISOString().slice(0,16));
    }
  });

  $('body').on('hidden.bs.modal', '.modal', function () {
    $(this).removeData('bs.modal');
  });
});
</script>
</body>
</html>
