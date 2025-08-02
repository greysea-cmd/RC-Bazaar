<?php
// View book details and place order
if (basename($_SERVER['PHP_SELF']) === 'book-details.php') {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'models/Book.php';
    require_once 'models/Order.php';

    $database = new Database();
    $db = $database->getConnection();
    $book_model = new Book($db);
    $order_model = new Order($db);

    $book_id = (int)($_GET['id'] ?? 0);
    $book_data = $book_model->getBookById($book_id);

    if (!$book_data || $book_data['status'] !== 'approved') {
        flash_message('Book not found or not available.', 'error');
        redirect('index.php');
    }

    // Handle order placement
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
        $user_id = get_current_user_id();
        
        if ($user_id == $book_data['seller_id']) {
            flash_message('You cannot buy your own book.', 'error');
        } else {
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            $shipping_address = sanitize_input($_POST['shipping_address'] ?? '');
            $payment_method = $_POST['payment_method'] ?? 'cod';
            $notes = sanitize_input($_POST['notes'] ?? '');
            
            if (empty($shipping_address)) {
                flash_message('Shipping address is required.', 'error');
            } elseif ($quantity > $book_data['quantity']) {
                flash_message('Requested quantity not available.', 'error');
            } else {
                $order_data = [
                    'buyer_id' => $user_id,
                    'seller_id' => $book_data['seller_id'],
                    'book_id' => $book_id,
                    'quantity' => $quantity,
                    'unit_price' => $book_data['price'],
                    'total_amount' => $book_data['price'] * $quantity,
                    'shipping_address' => $shipping_address,
                    'payment_method' => $payment_method,
                    'notes' => $notes
                ];
                
                if ($order_model->create($order_data)) {
                    // Update book quantity
                    $new_quantity = $book_data['quantity'] - $quantity;
                    $book_model->updateQuantity($book_id, $new_quantity);
                    
                    flash_message('Order placed successfully!', 'success');
                    redirect('my-orders.php');
                } else {
                    flash_message('Failed to place order. Please try again.', 'error');
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book_data['title']); ?> - <?php echo SITE_NAME; ?></title>
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
                <?php if (is_logged_in()): ?>
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php display_flash_message(); ?>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($book_data['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <?php if ($book_data['image_url']): ?>
                        <img src="<?php echo UPLOAD_PATH . $book_data['image_url']; ?>" class="card-img-top" alt="Book cover" style="height: 400px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                            <i class="fas fa-book fa-5x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($book_data['title']); ?></h2>
                <p class="text-muted lead">by <?php echo htmlspecialchars($book_data['author']); ?></p>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h4 class="text-primary"><?php echo format_price($book_data['price']); ?></h4>
                    </div>
                    <div class="col-md-6">
                        <span class="badge bg-success fs-6">
                            <?php echo ucwords(str_replace('_', ' ', $book_data['condition_type'])); ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Category:</strong> <?php echo htmlspecialchars($book_data['category_name'] ?? 'N/A'); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>ISBN:</strong> <?php echo htmlspecialchars($book_data['isbn'] ?? 'N/A'); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Available:</strong> <?php echo $book_data['quantity']; ?> copies
                    </div>
                </div>

                <!-- Seller Information -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Seller Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Seller:</strong> <?php echo htmlspecialchars($book_data['seller_name']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Rating:</strong> 
                                <i class="fas fa-star text-warning"></i> 
                                <?php echo number_format($book_data['seller_rating'], 1); ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <strong>Location:</strong> 
                                <?php echo htmlspecialchars($book_data['city'] . ', ' . $book_data['state']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if ($book_data['description']): ?>
                <div class="mb-3">
                    <h6>Description</h6>
                    <p><?php echo nl2br(htmlspecialchars($book_data['description'])); ?></p>
                </div>
                <?php endif; ?>

                <!-- Order Form -->
                <?php if (is_logged_in() && get_current_user_id() != $book_data['seller_id'] && $book_data['quantity'] > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-shopping-cart"></i> Place Order</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity" class="form-label">Quantity</label>
                                    <select class="form-select" id="quantity" name="quantity">
                                        <?php for ($i = 1; $i <= min(5, $book_data['quantity']); $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="cod">Cash on Delivery</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="online">Online Payment</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address *</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" 
                                          placeholder="Enter your complete shipping address..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" 
                                          placeholder="Any special instructions or notes..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-shopping-cart"></i> Place Order
                            </button>
                        </form>
                    </div>
                </div>
                <?php elseif (!is_logged_in()): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Please <a href="login.php">login</a> to place an order.
                </div>
                <?php elseif (get_current_user_id() == $book_data['seller_id']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    This is your own listing.
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> 
                    This book is currently out of stock.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    // Redirect to home if accessed directly
    header('Location: index.php');
    exit();
}