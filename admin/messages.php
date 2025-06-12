<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['action'])) {
    switch ($_POST['action']) {
        case 'mark_read':
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$_POST['message_id']]);
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$_POST['message_id']]);
            break;
    }
    header('Location: messages.php');
    exit();
}

$stmt = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
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
                            <a class="nav-link active" href="messages.php">
                                <i class="fas fa-envelope"></i> Messages
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
                    <h1 class="h2">Contact Messages</h1>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                    <tr <?php echo $message['status'] === 'unread' ? 'class="table-warning"' : ''; ?>>
                                        <td>
                                            <span class="badge bg-<?php echo $message['status'] === 'unread' ? 'warning' : 'success'; ?>">
                                                <?php echo ucfirst($message['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" onclick="viewMessage(<?php echo htmlspecialchars(json_encode($message)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($message['status'] === 'unread'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

    <div class="modal fade" id="messageDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="messageDetailsContent">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewMessage(message) {
            const modal = new bootstrap.Modal(document.getElementById('messageDetailsModal'));
            const content = document.getElementById('messageDetailsContent');
            
            content.innerHTML = `
                <div class="mb-3">
                    <strong>From:</strong><br>
                    ${message.name} (${message.email})
                </div>
                <div class="mb-3">
                    <strong>Subject:</strong><br>
                    ${message.subject}
                </div>
                <div class="mb-3">
                    <strong>Message:</strong><br>
                    ${message.message}
                </div>
                <div class="text-muted">
                    <small>Sent on ${new Date(message.created_at).toLocaleString()}</small>
                </div>
            `;
            
            modal.show();
            
            if (message.status === 'unread') {
                fetch('messages.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message_id=${message.id}&action=mark_read`
                }).then(() => {
                    window.location.reload();
                });
            }
        }
    </script>
</body>
</html>