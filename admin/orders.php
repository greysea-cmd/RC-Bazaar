<?php
if (basename($_SERVER['PHP_SELF']) === 'orders.php') {
    require_once '../config/database.php';
    require_once '../config/config.php';
    require_once '../models/Order.php';

    if (!is_admin()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $order = new Order($db);

    $orders = $order->getAllOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #343a40;
        }
        .sidebar .nav-link {
            color: #adb5bd;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?> Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php" target="_blank">View Site</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="nav flex-column nav-pills p-3">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="books.php">
                        <i class="fas fa-book"></i> Books
                    </a>
                    <a class="nav-link active" href="orders.php">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                    <a class="nav-link" href="disputes.php">
                        <i class="fas fa-exclamation-triangle"></i> Disputes
                    </a>
                </div>
            </div>

            <div class="col-md-10 p-4">
                <h2><i class="fas fa-shopping-cart"></i> Order Management</h2>

                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4>No orders found</h4>
                        <p class="text-muted">Orders will appear here once users start making purchases.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Book</th>
                                    <th>Buyer</th>
                                    <th>Seller</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order_item): ?>
                                <tr>
                                    <td>#<?php echo $order_item['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order_item['book_title']); ?></strong><br>
                                        <small class="text-muted">by <?php echo htmlspecialchars($order_item['book_author']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($order_item['buyer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order_item['seller_name']); ?></td>
                                    <td><?php echo format_price($order_item['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $order_item['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($order_item['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_colors = [
                                            'placed' => 'secondary',
                                            'confirmed' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            'disputed' => 'warning'
                                        ];
                                        $color = $status_colors[$order_item['order_status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo ucfirst($order_item['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo time_ago($order_item['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
}