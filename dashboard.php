<?php
if (basename($_SERVER['PHP_SELF']) === 'dashboard.php') {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'models/User.php';
    require_once 'models/Book.php';
    require_once 'models/Order.php';

    if (!is_logged_in()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $book = new Book($db);
    $order = new Order($db);

    $user_id = get_current_user_id();
    $user_data = $user->getUserById($user_id);
    $my_books = $book->getSellerBooks($user_id);
    $my_purchases = $order->getUserOrders($user_id, 'buyer');
    $my_sales = $order->getUserOrders($user_id, 'seller');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_data['first_name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php display_flash_message(); ?>

        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="my-books.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-book"></i> My Books
                    </a>
                    <a href="sell-book.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus"></i> Sell a Book
                    </a>
                    <a href="my-orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart"></i> My Orders
                    </a>
                    <a href="my-disputes.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-exclamation-triangle"></i> Disputes
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <h2>Dashboard</h2>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user_data['first_name']); ?>!</p>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($my_books); ?></h4>
                                        <p>My Books</p>
                                    </div>
                                    <i class="fas fa-book fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($my_purchases); ?></h4>
                                        <p>Purchases</p>
                                    </div>
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($my_sales); ?></h4>
                                        <p>Sales</p>
                                    </div>
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo number_format($user_data['rating'], 1); ?></h4>
                                        <p>Rating</p>
                                    </div>
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-clock"></i> Recent Purchases</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($my_purchases)): ?>
                                    <p class="text-muted">No purchases yet.</p>
                                <?php else: ?>
                                    <?php foreach (array_slice($my_purchases, 0, 5) as $purchase): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong><?php echo htmlspecialchars($purchase['book_title']); ?></strong><br>
                                                <small class="text-muted">Order #<?php echo $purchase['id']; ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $purchase['order_status'] === 'delivered' ? 'success' : 'primary'; ?>">
                                                    <?php echo ucfirst($purchase['order_status']); ?>
                                                </span><br>
                                                <small class="text-muted"><?php echo format_price($purchase['total_amount']); ?></small>
                                            </div>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                    <a href="my-orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-book"></i> My Recent Books</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($my_books)): ?>
                                    <p class="text-muted">No books listed yet.</p>
                                    <a href="sell-book.php" class="btn btn-primary btn-sm">Sell Your First Book</a>
                                <?php else: ?>
                                    <?php foreach (array_slice($my_books, 0, 5) as $my_book): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong><?php echo htmlspecialchars($my_book['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($my_book['author']); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $my_book['status'] === 'approved' ? 'success' : ($my_book['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($my_book['status']); ?>
                                                </span><br>
                                                <small class="text-muted"><?php echo format_price($my_book['price']); ?></small>
                                            </div>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                    <a href="my-books.php" class="btn btn-sm btn-outline-primary">View All</a>
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
}
?>