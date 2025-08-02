<?php
// admin/login.php - Admin login page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../models/Admin.php';

if (is_admin()) {
    redirect('dashboard.php');
}

$database = new Database();
$db = $database->getConnection();
$admin = new Admin($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $admin_data = $admin->login($email, $password);
        if ($admin_data) {
            $_SESSION['admin_id'] = $admin_data['id'];
            $_SESSION['admin_username'] = $admin_data['username'];
            $_SESSION['admin_role'] = $admin_data['role'];
            
            flash_message('Welcome to admin panel!', 'success');
            redirect('dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h3><i class="fas fa-shield-alt text-danger"></i> Admin Login</h3>
                            <p class="text-muted"><?php echo SITE_NAME; ?> Administration</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted">← Back to Main Site</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>