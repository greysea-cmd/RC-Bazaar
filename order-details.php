<?php

// Detailed order view
if (basename($_SERVER['PHP_SELF']) === 'order-details.php') {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'models/Order.php';

    if (!is_logged_in()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $order = new Order($db);

    $order_id = (int)($_GET['id'] ?? 0);
    $order_data = $order->getOrderById($order_id);

    if (!$order_data) {
        flash_message('Order not found.', 'error');
        redirect('my-orders.php');
    }

    $user_id = get_current_user_id();
    if ($order_data['buyer_id'] != $user_id && $order_data['seller_id'] != $user_id) {
        flash_message('You are not authorized to view this order.', 'error');
        redirect('my-orders.php');
    }

    $is_buyer = ($order_data['buyer_id'] == $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_data['id']; ?> - <?php echo SITE_NAME; ?></title>
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
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="my-orders.php">My Orders</a></li>
                <li class="breadcrumb-item active">Order #<?php echo $order_data['id']; ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Order #<?php echo $order_data['id']; ?></h4>
                            <?php
                            $status_colors = [
                                'placed' => 'secondary',
                                'confirmed' => 'info', 
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                'disputed' => 'warning'
                            ];
                            $color = $status_colors[$order_data['order_status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?> fs-6">
                                <?php echo ucfirst($order_data['order_status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Book Information -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <?php if ($order_data['image_url']): ?>
                                    <img src="<?php echo UPLOAD_PATH . $order_data['image_url']; ?>" 
                                         alt="Book cover" class="img-fluid" style="max-height: 200px;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 100%; height: 200px;">
                                        <i class="fas fa-book fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h5><?php echo htmlspecialchars($order_data['book_title']); ?></h5>
                                <p class="text-muted">by <?php echo htmlspecialchars($order_data['book_author']); ?></p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Quantity:</strong> <?php echo $order_data['quantity']; ?><br>
                                        <strong>Unit Price:</strong> <?php echo format_price($order_data['unit_price']); ?><br>
                                        <strong>Total Amount:</strong> <?php echo format_price($order_data['total_amount']); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order_data['payment_method'])); ?><br>
                                        <strong>Payment Status:</strong> 
                                        <span class="badge bg-<?php echo $order_data['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($order_data['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="mb-4">
                            <h6>Order Timeline</h6>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order Placed</h6>
                                        <small><?php echo date('M d, Y H:i', strtotime($order_data['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1">Your order has been placed successfully.</p>
                                </div>
                                
                                <?php if (in_array($order_data['order_status'], ['confirmed', 'shipped', 'delivered'])): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order Confirmed</h6>
                                        <small><?php echo time_ago($order_data['updated_at']); ?></small>
                                    </div>
                                    <p class="mb-1">Seller has confirmed your order.</p>
                                </div>
                                <?php endif; ?>

                                <?php if (in_array($order_data['order_status'], ['shipped', 'delivered'])): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order Shipped</h6>
                                        <small><?php echo time_ago($order_data['updated_at']); ?></small>
                                    </div>
                                    <p class="mb-1">Your order is on its way!</p>
                                </div>
                                <?php endif; ?>

                                <?php if ($order_data['order_status'] === 'delivered'): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Order Delivered</h6>
                                        <small><?php echo time_ago($order_data['updated_at']); ?></small>
                                    </div>
                                    <p class="mb-1">Your order has been delivered successfully.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="mb-3">
                            <h6>Shipping Address</h6>
                            <address class="border p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($order_data['shipping_address'])); ?>
                            </address>
                        </div>

                        <?php if ($order_data['notes']): ?>
                        <div class="mb-3">
                            <h6>Order Notes</h6>
                            <p class="border p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($order_data['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Contact Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6><?php echo $is_buyer ? 'Seller' : 'Buyer'; ?> Information</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($is_buyer): ?>
                            <strong>Name:</strong> <?php echo htmlspecialchars($order_data['seller_first_name'] . ' ' . $order_data['seller_last_name']); ?><br>
                            <strong>Username:</strong> <?php echo htmlspecialchars($order_data['seller_name']); ?>
                        <?php else: ?>
                            <strong>Name:</strong> <?php echo htmlspecialchars($order_data['buyer_first_name'] . ' ' . $order_data['buyer_last_name']); ?><br>
                            <strong>Username:</strong> <?php echo htmlspecialchars($order_data['buyer_name']); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6>Actions</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($order_data['order_status'] === 'delivered' && $is_buyer): ?>
                            <a href="create-dispute.php?order_id=<?php echo $order_data['id']; ?>" class="btn btn-warning btn-sm w-100 mb-2">
                                <i class="fas fa-exclamation-triangle"></i> Report Issue
                            </a>
                        <?php endif; ?>
                        
                        <a href="my-orders.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
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
    // Redirect to homepage if accessed directly
    redirect('index.php');
}