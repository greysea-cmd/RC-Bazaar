<?php
// Homepage with book listings and search
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'models/Book.php';
require_once 'models/Category.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);
$category = new Category($db);

// Get search parameters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Get books and categories
$books = $book->getApprovedBooks($search, $category_id, ITEMS_PER_PAGE, $offset);
$categories = $category->getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Buy and Sell Books Online</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .book-card {
            height: 100%;
            transition: transform 0.2s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .book-image {
            height: 200px;
            object-fit: cover;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
        }
        .category-badge {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            display: inline-block;
            text-decoration: none;
            color: #495057;
            transition: all 0.2s;
        }
        .category-badge:hover {
            background: #007bff;
            color: white;
            text-decoration: none;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="browse.php">Browse Books</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> My Account
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="my-books.php">My Books</a></li>
                                <li><a class="dropdown-item" href="my-orders.php">My Orders</a></li>
                                <li><a class="dropdown-item" href="sell-book.php">Sell a Book</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <?php if (empty($search) && empty($category_id)): ?>
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Buy and Sell Books Online</h1>
            <p class="lead mb-4">Connect with book lovers in your community. Find rare books, textbooks, and bestsellers at great prices.</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form method="GET" action="index.php" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search books, authors, ISBN..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="container my-5">
        <?php display_flash_message(); ?>

        <!-- Search and Filter Bar -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="index.php" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category" class="form-select me-2" style="max-width: 200px;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <?php if (is_logged_in()): ?>
                    <a href="sell-book.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Sell a Book
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories -->
        <?php if (empty($search) && empty($category_id)): ?>
        <div class="mb-4">
            <h5>Browse by Category:</h5>
            <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo $cat['id']; ?>" class="category-badge">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Books Grid -->
        <div class="row">
            <?php if (empty($books)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h4>No books found</h4>
                    <p class="text-muted">Try adjusting your search criteria or browse different categories.</p>
                </div>
            <?php else: ?>
                <?php foreach ($books as $book_item): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card book-card">
                        <div class="position-relative">
                            <?php if ($book_item['image_url']): ?>
                                <img src="<?php echo UPLOAD_PATH . $book_item['image_url']; ?>" class="card-img-top book-image" alt="Book cover">
                            <?php else: ?>
                                <div class="card-img-top book-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-book fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                <?php echo ucfirst($book_item['condition_type']); ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($book_item['title']); ?></h6>
                            <p class="card-text text-muted small">by <?php echo htmlspecialchars($book_item['author']); ?></p>
                            <p class="card-text">
                                <strong class="text-primary"><?php echo format_price($book_item['price']); ?></strong>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning"></i>
                                    <?php echo number_format($book_item['seller_rating'], 1); ?>
                                </small>
                                <small class="text-muted"><?php echo htmlspecialchars($book_item['seller_name']); ?></small>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <a href="book-details.php?id=<?php echo $book_item['id']; ?>" class="btn btn-primary btn-sm w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (count($books) >= ITEMS_PER_PAGE): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page-1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <li class="page-item active">
                    <span class="page-link"><?php echo $page; ?></span>
                </li>
                
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo ($page+1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Your trusted marketplace for buying and selling books online.</p>
                </div>
                <div class="col-md-6">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="browse.php" class="text-white-50">Browse Books</a></li>
                        <?php if (!is_logged_in()): ?>
                            <li><a href="register.php" class="text-white-50">Join Us</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>