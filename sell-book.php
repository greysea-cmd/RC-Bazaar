<?php
// sell-book.php - Add new book listing
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'models/Book.php';
require_once 'models/Category.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);
$category = new Category($db);

$categories = $category->getAllCategories();
$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'seller_id' => get_current_user_id(),
        'title' => sanitize_input($_POST['title'] ?? ''),
        'author' => sanitize_input($_POST['author'] ?? ''),
        'isbn' => sanitize_input($_POST['isbn'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'condition_type' => $_POST['condition_type'] ?? '',
        'description' => sanitize_input($_POST['description'] ?? ''),
        'price' => (float)($_POST['price'] ?? 0),
        'quantity' => (int)($_POST['quantity'] ?? 1),
        'image_url' => null
    ];

    // Validation
    if (empty($form_data['title'])) $errors[] = 'Title is required.';
    if (empty($form_data['author'])) $errors[] = 'Author is required.';
    if (empty($form_data['condition_type'])) $errors[] = 'Condition is required.';
    if ($form_data['price'] <= 0) $errors[] = 'Price must be greater than 0.';
    if ($form_data['quantity'] <= 0) $errors[] = 'Quantity must be greater than 0.';

    // Handle image upload
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
        try {
            $form_data['image_url'] = upload_image($_FILES['book_image']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        if ($book->create($form_data)) {
            flash_message('Book listed successfully! It will be reviewed by admin before approval.', 'success');
            redirect('my-books.php');
        } else {
            $errors[] = 'Failed to list book. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell a Book - <?php echo SITE_NAME; ?></title>
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
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus"></i> List a Book for Sale</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Book Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="isbn" class="form-label">ISBN</label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" 
                                           value="<?php echo htmlspecialchars($form_data['isbn'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="author" class="form-label">Author *</label>
                                    <input type="text" class="form-control" id="author" name="author" 
                                           value="<?php echo htmlspecialchars($form_data['author'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo ($form_data['category_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="condition_type" class="form-label">Condition *</label>
                                    <select class="form-select" id="condition_type" name="condition_type" required>
                                        <option value="">Select Condition</option>
                                        <option value="new" <?php echo ($form_data['condition_type'] ?? '') === 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="like_new" <?php echo ($form_data['condition_type'] ?? '') === 'like_new' ? 'selected' : ''; ?>>Like New</option>
                                        <option value="good" <?php echo ($form_data['condition_type'] ?? '') === 'good' ? 'selected' : ''; ?>>Good</option>
                                        <option value="fair" <?php echo ($form_data['condition_type'] ?? '') === 'fair' ? 'selected' : ''; ?>>Fair</option>
                                        <option value="poor" <?php echo ($form_data['condition_type'] ?? '') === 'poor' ? 'selected' : ''; ?>>Poor</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label">Price ($) *</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="price" name="price" 
                                           value="<?php echo $form_data['price'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" min="1" class="form-control" id="quantity" name="quantity" 
                                           value="<?php echo $form_data['quantity'] ?? 1; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Describe the book's condition, any highlights, missing pages, etc."><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="book_image" class="form-label">Book Image</label>
                                <input type="file" class="form-control" id="book_image" name="book_image" accept="image/*">
                                <small class="text-muted">Upload a clear image of your book (JPG, PNG, GIF - Max 5MB)</small>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> List Book
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>