<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch statistics
$conn = getConnection();

// Total products
$stmt = $conn->query("SELECT COUNT(*) as total_products FROM products");
$totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

// Total users
$stmt = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Total orders
$stmt = $conn->query("SELECT COUNT(*) as total_orders FROM orders");
$totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Revenue
$stmt = $conn->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
$totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// Get monthly sales data for chart
$stmt = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as revenue 
                     FROM orders 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                     ORDER BY month");
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$months = [];
$revenues = [];
foreach ($monthlyData as $data) {
    $months[] = date('M Y', strtotime($data['month']));
    $revenues[] = $data['revenue'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HA GROUP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-brand">
                    <i class="fas fa-laptop me-2"></i> HA GROUP
                </div>
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customers.php">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-store"></i> View Store
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
                    <div>
                        <h1 class="h2 mb-1">Dashboard</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary"><i class="fas fa-download fa-sm"></i> Export</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1">
                            <i class="fas fa-calendar fa-sm"></i>
                            This week
                        </button>
                    </div>
                </div>

                <!-- Stats cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card data-card primary stats-card h-100">
                            <div class="card-body">
                                <div class="stats-label text-primary">Products</div>
                                <div class="stats-amount"><?php echo $totalProducts; ?></div>
                                <div class="text-muted small mt-2">
                                    <i class="fas fa-arrow-up text-success"></i> 
                                    <span>12% since last month</span>
                                </div>
                                <i class="fas fa-box stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card data-card success stats-card h-100">
                            <div class="card-body">
                                <div class="stats-label text-success">Users</div>
                                <div class="stats-amount"><?php echo $totalUsers; ?></div>
                                <div class="text-muted small mt-2">
                                    <i class="fas fa-arrow-up text-success"></i>
                                    <span>8% since last month</span>
                                </div>
                                <i class="fas fa-users stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card data-card info stats-card h-100">
                            <div class="card-body">
                                <div class="stats-label text-info">Orders</div>
                                <div class="stats-amount"><?php echo $totalOrders; ?></div>
                                <div class="text-muted small mt-2">
                                    <i class="fas fa-arrow-up text-success"></i>
                                    <span>5% since last week</span>
                                </div>
                                <i class="fas fa-shopping-cart stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card data-card warning stats-card h-100">
                            <div class="card-body">
                                <div class="stats-label text-warning">Revenue</div>
                                <div class="stats-amount"><?php echo number_format($totalRevenue, 2); ?> DH</div>
                                <div class="text-muted small mt-2">
                                    <i class="fas fa-arrow-up text-success"></i>
                                    <span>15% since last month</span>
                                </div>
                                <i class="fas fa-dollar-sign stats-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                                <div class="dropdown">
                                    <button class="btn btn-link btn-sm" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-gray-400"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-download fa-sm me-2"></i>Download Report</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-print fa-sm me-2"></i>Print Report</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-sync fa-sm me-2"></i>Refresh Data</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" style="min-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Product Categories</h6>
                                <div class="dropdown">
                                    <button class="btn btn-link btn-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-gray-400"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-list fa-sm me-2"></i>View All</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-plus fa-sm me-2"></i>Add New</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="categoryChart" style="min-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>    <script>
        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const gradient = revenueCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(78, 115, 223, 0.3)');
        gradient.addColorStop(1, 'rgba(78, 115, 223, 0)');

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue (DH)',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: gradient,
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#4e73df',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#5a5c69',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ' + context.parsed.y.toLocaleString() + ' DH';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#eaecf4',
                            borderDash: [2, 2]
                        },
                        ticks: {
                            color: '#858796',
                            callback: function(value) {
                                return value.toLocaleString() + ' DH';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#858796'
                        }
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laptops', 'Mobile', 'Desktop', 'Accessories'],
                datasets: [{
                    data: [30, 25, 20, 25],
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#5a5c69',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                },
                cutout: '75%'
            }
        });
    </script>
</body>
</html>
