<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getConnection();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header('Location: orders.php');
    exit();
}

// Fetch all orders with customer details
$stmt = $conn->query("SELECT o.*, u.username, u.email, u.full_name 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="orders.php">
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Manage Orders</h1>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                                        </td>
                                        <td><?php echo number_format($order['total_amount'], 2); ?> DH</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($order['status']) {
                                                    'completed' => 'success',
                                                    'pending' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                    Update Status
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            <input type="hidden" name="status" value="pending">
                                                            <button type="submit" class="dropdown-item">Pending</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="dropdown-item">Completed</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit" class="dropdown-item">Cancelled</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                                <button type="button" class="btn btn-sm btn-info ms-1" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrderDetails(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();
            
            // Fetch order details
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Customer Information</h6>
                                <p class="mb-1"><strong>Name:</strong> ${data.customer.full_name}</p>
                                <p class="mb-1"><strong>Email:</strong> ${data.customer.email}</p>
                                <p class="mb-0"><strong>Phone:</strong> ${data.customer.phone || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    
                    data.items.forEach(item => {
                        html += `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.price} DH</td>
                                <td>${item.price * item.quantity} DH</td>
                            </tr>`;
                    });

                    html += `
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th>${data.total_amount} DH</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>`;

                    document.getElementById('orderDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('orderDetailsContent').innerHTML = 'Error loading order details.';
                });
        }
    </script>
</body>
</html>
