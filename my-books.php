<?php
// Manage user's book listings
if (basename($_SERVER['PHP_SELF']) === 'my-books.php') {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'models/Book.php';

    if (!is_logged_in()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $book = new Book($db);

    $user_id = get_current_user_id();
    $my_books = $book->getSellerBooks($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books - <?php echo SITE_NAME; ?></title>
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book"></i> My Books</h2>
            <a href="sell-book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Book
            </a>
        </div>

        <?php if (empty($my_books)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h4>No books listed yet</h4>
                <p class="text-muted">Start selling by adding your first book!</p>
                <a href="sell-book.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> List Your First Book
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Category</th>
                            <th>Condition</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Listed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_books as $book_item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($book_item['image_url']): ?>
                                        <img src="<?php echo UPLOAD_PATH . $book_item['image_url']; ?>" 
                                             alt="Book cover" class="me-3" style="width: 50px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="me-3 bg-light d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 60px;">
                                            <i class="fas fa-book text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($book_item['title']); ?></strong><br>
                                        <small class="text-muted">by <?php echo htmlspecialchars($book_item['author']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($book_item['category_name'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo ucwords(str_replace('_', ' ', $book_item['condition_type'])); ?>
                                </span>
                            </td>
                            <td><?php echo format_price($book_item['price']); ?></td>
                            <td><?php echo $book_item['quantity']; ?></td>
                            <td>
                                <?php
                                $status_colors = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'sold' => 'info',
                                    'inactive' => 'secondary'
                                ];
                                $color = $status_colors[$book_item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>">
                                    <?php echo ucfirst($book_item['status']); ?>
                                </span>
                                <?php if ($book_item['admin_notes']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($book_item['admin_notes']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo time_ago($book_item['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="book-details.php?id=<?php echo $book_item['id']; ?>" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($book_item['status'] === 'approved'): ?>
                                        <a href="edit-book.php?id=<?php echo $book_item['id']; ?>" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    // Redirect to homepage if accessed directly
    redirect('index.php');
}