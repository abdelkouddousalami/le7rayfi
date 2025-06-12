<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    switch ($_POST['action']) {
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_POST['user_id']]);
            break;
        
        case 'toggle_status':
            $stmt = $conn->prepare("UPDATE users SET status = NOT status WHERE id = ?");
            $stmt->execute([$_POST['user_id']]);
            break;
    }
    header('Location: customers.php');
    exit();
}

$stmt = $conn->query("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as total_orders,
           SUM(o.total_amount) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="customers.php">
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

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Manage Customers</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="input-group me-2">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search customers...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Orders</th>
                                        <th>Total Spent</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                                <div class="ms-3">
                                                    <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                    <div class="small text-muted">@<?php echo htmlspecialchars($user['username']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($user['email']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($user['phone'] ?? 'No phone'); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo $user['total_orders']; ?> orders</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo number_format($user['total_spent'] ?? 0, 2); ?> DH</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo isset($user['status']) && $user['status'] ? 'success' : 'warning'; ?>">
                                                <?php echo isset($user['status']) && $user['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewCustomerDetails(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to toggle this user\'s status?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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

    <div class="modal fade" id="customerDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="customerDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        function viewCustomerDetails(userId) {
            const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
            modal.show();
            
            fetch(`get_customer_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Personal Information</h6>
                                <p class="mb-1"><strong>Name:</strong> ${data.full_name}</p>
                                <p class="mb-1"><strong>Email:</strong> ${data.email}</p>
                                <p class="mb-1"><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                                <p class="mb-0"><strong>Address:</strong> ${data.address || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Order History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                    
                    if (data.orders && data.orders.length > 0) {
                        data.orders.forEach(order => {
                            html += `
                                <tr>
                                    <td>#${order.id}</td>
                                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                    <td><span class="badge bg-${
                                        order.status === 'completed' ? 'success' :
                                        order.status === 'pending' ? 'warning' :
                                        'secondary'
                                    }">${order.status}</span></td>
                                    <td>${order.total_amount} DH</td>
                                </tr>`;
                        });
                    } else {
                        html += '<tr><td colspan="4" class="text-center">No orders found</td></tr>';
                    }

                    html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;

                    document.getElementById('customerDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('customerDetailsContent').innerHTML = 'Error loading customer details.';
                });
        }
    </script>
    
    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #4e73df;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</body>
</html>