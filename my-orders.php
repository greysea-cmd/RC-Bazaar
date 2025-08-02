<?php
// View user's orders (both purchases and sales)
if (basename($_SERVER['PHP_SELF']) === 'my-orders.php') {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'models/Order.php';

    if (!is_logged_in()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $order = new Order($db);

    $user_id = get_current_user_id();
    $tab = $_GET['tab'] ?? 'purchases';
    
    if ($tab === 'sales') {
        $orders = $order->getUserOrders($user_id, 'seller');
        $page_title = 'My Sales';
    } else {
        $orders = $order->getUserOrders($user_id, 'buyer');
        $page_title = 'My Purchases';
    }

    // Handle order status updates (for sellers)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';
        
        if ($order->updateOrderStatus($order_id, $new_status)) {
            flash_message('Order status updated successfully.', 'success');
            redirect('my-orders.php?tab=' . $tab);
        } else {
            flash_message('Failed to update order status.', 'error');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
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
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php display_flash_message(); ?>

        <h2><i class="fas fa-shopping-cart"></i> My Orders</h2>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $tab === 'purchases' ? 'active' : ''; ?>" 
                   href="?tab=purchases">
                    <i class="fas fa-shopping-bag"></i> My Purchases
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $tab === 'sales' ? 'active' : ''; ?>" 
                   href="?tab=sales">
                    <i class="fas fa-dollar-sign"></i> My Sales
                </a>
            </li>
        </ul>

        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h4>No orders found</h4>
                <p class="text-muted">
                    <?php echo $tab === 'purchases' ? 'You haven\'t made any purchases yet.' : 'You haven\'t made any sales yet.'; ?>
                </p>
                <?php if ($tab === 'purchases'): ?>
                    <a href="index.php" class="btn btn-primary">Browse Books</a>
                <?php else: ?>
                    <a href="sell-book.php" class="btn btn-primary">List a Book</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order_item): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Order #<?php echo $order_item['id']; ?></h6>
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
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <?php if ($order_item['image_url']): ?>
                                    <img src="<?php echo UPLOAD_PATH . $order_item['image_url']; ?>" 
                                         alt="Book cover" class="me-3" style="width: 60px; height: 80px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="me-3 bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 80px;">
                                        <i class="fas fa-book text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h6><?php echo htmlspecialchars($order_item['book_title']); ?></h6>
                                    <p class="text-muted small mb-1">by <?php echo htmlspecialchars($order_item['book_author']); ?></p>
                                    <p class="mb-1">
                                        <strong>Quantity:</strong> <?php echo $order_item['quantity']; ?><br>
                                        <strong>Total:</strong> <?php echo format_price($order_item['total_amount']); ?>
                                    </p>
                                    <p class="small text-muted mb-1">
                                        <?php if ($tab === 'purchases'): ?>
                                            <strong>Seller:</strong> <?php echo htmlspecialchars($order_item['seller_name']); ?>
                                        <?php else: ?>
                                            <strong>Buyer:</strong> <?php echo htmlspecialchars($order_item['buyer_name']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="small text-muted">
                                        <strong>Ordered:</strong> <?php echo time_ago($order_item['created_at']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($tab === 'sales' && in_array($order_item['order_status'], ['placed', 'confirmed'])): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order_item['id']; ?>">
                                            <select name="new_status" class="form-select form-select-sm d-inline-block" style="width: auto;">
                                                <option value="confirmed" <?php echo $order_item['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="shipped" <?php echo $order_item['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order_item['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="order-details.php?id=<?php echo $order_item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                    <?php if (in_array($order_item['order_status'], ['delivered']) && $tab === 'purchases'): ?>
                                        <a href="create-dispute.php?order_id=<?php echo $order_item['id']; ?>" class="btn btn-sm btn-outline-warning">
                                            Report Issue
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
}
?>
