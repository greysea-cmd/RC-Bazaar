<?php
// admin/dashboard.php - Admin dashboard
if (basename($_SERVER['PHP_SELF']) === 'dashboard.php') {
    require_once '../config/database.php';
    require_once '../config/config.php';
    require_once '../models/Admin.php';
    require_once '../models/Book.php';
    require_once '../models/Order.php';
    require_once '../models/Dispute.php';

    if (!is_admin()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $admin = new Admin($db);
    $book = new Book($db);
    $order = new Order($db);
    $dispute = new Dispute($db);

    $stats = $admin->getDashboardStats();
    $pending_books = $book->getPendingBooks();
    $recent_orders = array_slice($order->getAllOrders(), 0, 10);
    $open_disputes = $dispute->getAllDisputes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt"></i> <?php echo SITE_NAME; ?> Admin
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../index.php" target="_blank">View Site</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="nav flex-column nav-pills p-3">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a class="nav-link" href="books.php">
                        <i class="fas fa-book"></i> Books
                    </a>
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                    <a class="nav-link" href="disputes.php">
                        <i class="fas fa-exclamation-triangle"></i> Disputes
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <?php display_flash_message(); ?>

                <h2>Admin Dashboard</h2>
                <p class="text-muted">Overview of RC Bazaar</p>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $stats['total_users']; ?></h3>
                                        <p>Total Users</p>
                                    </div>
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $stats['total_books']; ?></h3>
                                        <p>Total Books</p>
                                    </div>
                                    <i class="fas fa-book fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $stats['pending_books']; ?></h3>
                                        <p>Pending Approvals</p>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $stats['open_disputes']; ?></h3>
                                        <p>Open Disputes</p>
                                    </div>
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clock"></i> Pending Book Approvals</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pending_books)): ?>
                                    <p class="text-muted">No books pending approval.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Book</th>
                                                    <th>Seller</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                    <th>Submitted</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($pending_books, 0, 5) as $pending_book): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($pending_book['title']); ?></strong><br>
                                                        <small class="text-muted">by <?php echo htmlspecialchars($pending_book['author']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($pending_book['seller_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($pending_book['category_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo format_price($pending_book['price']); ?></td>
                                                    <td><?php echo time_ago($pending_book['created_at']); ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="book-review.php?id=<?php echo $pending_book['id']; ?>" 
                                                               class="btn btn-outline-primary">Review</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="books.php" class="btn btn-sm btn-outline-primary">View All Pending</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-exclamation-triangle"></i> Recent Disputes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($open_disputes)): ?>
                                    <p class="text-muted">No open disputes.</p>
                                <?php else: ?>
                                    <?php foreach (array_slice($open_disputes, 0, 5) as $dispute_item): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong>Order #<?php echo $dispute_item['order_number']; ?></strong><br>
                                                    <small class="text-muted"><?php echo ucfirst($dispute_item['dispute_type']); ?> dispute</small>
                                                </div>
                                                <span class="badge bg-<?php echo $dispute_item['status'] === 'open' ? 'danger' : 'warning'; ?>">
                                                    <?php echo ucfirst($dispute_item['status']); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted"><?php echo time_ago($dispute_item['created_at']); ?></small>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                    <a href="disputes.php" class="btn btn-sm btn-outline-danger">View All Disputes</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-shopping-cart"></i> Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_orders)): ?>
                                    <p class="text-muted">No orders yet.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Book</th>
                                                    <th>Buyer</th>
                                                    <th>Seller</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_orders as $recent_order): ?>
                                                <tr>
                                                    <td>#<?php echo $recent_order['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($recent_order['book_title']); ?></strong><br>
                                                        <small class="text-muted">by <?php echo htmlspecialchars($recent_order['book_author']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($recent_order['buyer_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($recent_order['seller_name']); ?></td>
                                                    <td><?php echo format_price($recent_order['total_amount']); ?></td>
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
                                                        $color = $status_colors[$recent_order['order_status']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?php echo $color; ?>">
                                                            <?php echo ucfirst($recent_order['order_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo time_ago($recent_order['created_at']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    header('Location: login.php');
    exit();
}