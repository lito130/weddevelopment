<?php
include 'auth_check.php';
require_login();
include 'connection.php';


$total_disasters = $conn->query("SELECT COUNT(*) as cnt FROM disasters")->fetch_assoc()['cnt'];


$total_types = $conn->query("SELECT COUNT(DISTINCT disaster_type) as cnt FROM disasters")->fetch_assoc()['cnt'];


$latest = $conn->query("SELECT * FROM disasters ORDER BY date_reported DESC LIMIT 100");


$typeData = [];
$typeResult = $conn->query("SELECT disaster_type, COUNT(*) as cnt FROM disasters GROUP BY disaster_type");
while ($row = $typeResult->fetch_assoc()) {
    $typeData[$row['disaster_type']] = (int)$row['cnt'];
}


$monthData = [];
$monthResult = $conn->query("
    SELECT 
        DATE_FORMAT(date_reported, '%b %Y') as month_label, 
        DATE_FORMAT(date_reported, '%Y-%m') as month_key, 
        COUNT(*) as cnt 
    FROM disasters 
    GROUP BY month_key 
    ORDER BY month_key ASC
");
while ($row = $monthResult->fetch_assoc()) {
    $monthData[$row['month_label']] = (int)$row['cnt'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disaster Preparedness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="shortcut icon" type="image/png" href="images/disaster.png">
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 600;
        }
        .nav-link {
            font-size: 1.05rem;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #fff !important;
            text-shadow: 0 0 2px rgba(255, 255, 255, 0.5);
        }
        .btn-lg {
            font-size: 1.1rem;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h3 {
            color: #4e73df;
            margin: 0;
            font-weight: 600;
        }
        .card p {
            color: #6c757d;
            margin: 0;
        }
        .chart-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        .table th {
            background-color: #f1f3f6;
            font-weight: 500;
        }
        .welcome-text {
            font-size: 1.1rem;
            color: #495057;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            color: #6c757d;
            font-size: 1rem;
        }
        /* Improve DataTables Search Input */
        .dataTables_filter input {
            margin-left: 0.5em;
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 0.95rem;
        }
        .dataTables_filter input:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 233, 0.25);
        }
        .dataTables_info, .dataTables_paginate {
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="text-center mb-3 text-primary">🛡️ Disaster Record</h2>
        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card text-center p-4">
                    <h3><?= $total_disasters ?></h3>
                    <p>Total Disaster Reports</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center p-4">
                    <h3><?= $total_types ?></h3>
                    <p>Unique Disaster Types</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="chart-container">
                    <h5>📊 Disasters by Type</h5>
                    <canvas id="barChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="chart-container">
                    <h5>📈 Reports Over Time (Monthly)</h5>
                    <canvas id="lineChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <h4 class="mb-3">📌 Latest Reports</h4>
        <?php if ($latest->num_rows > 0): ?>
            <div class="table-responsive">
                <table id="reportsTable" class="table table-bordered table-hover bg-white shadow-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Disaster Type</th>
                            <th>Location</th>
                            <th>Emergency Contact</th>
                            <th>Date Reported</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $latest->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['disaster_type']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['emergency_contact']) ?></td>
                                <td><?= date('F j, Y \a\t g:i A', strtotime($row['date_reported'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No disaster reports found.</div>
        <?php endif; ?>
    </div>

    
    <div class="footer">
        <strong>&copy; <?= date('Y') ?> <b>Disaster Preparedness System. All rights reserved.</b></strong>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    
    <script>
        $(document).ready(function () {
            $('#reportsTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "lengthMenu": [5, 10, 25, 50],
                "pageLength": 5,
                "language": {
                    "search": "🔍 Filter reports:",
                    "searchPlaceholder": "Search all columns...",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "paginate": {
                        "previous": "← Prev",
                        "next": "Next →"
                    },
                    "info": "Showing _START_ to _END_ of _TOTAL_ reports"
                }
            });
        });
    </script>

    
    <script>
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($typeData)) ?>,
                datasets: [{
                    label: 'Number of Reports',
                    data: <?= json_encode(array_values($typeData)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(54, 162, 235, 0.9)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#000',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#555',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: value => value.toFixed(0)
                        },
                        title: {
                            display: true,
                            text: 'Number of Reports'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Disaster Type'
                        }
                    }
                }
            }
        });

    
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($monthData)) ?>,
                datasets: [{
                    label: 'Reports per Month',
                    data: <?= json_encode(array_values($monthData)) ?>,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: value => value.toFixed(0)
                        },
                        title: {
                            display: true,
                            text: 'Number of Reports'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>