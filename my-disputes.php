<?php
// my-disputes.php - View user's disputes
if (basename($_SERVER['PHP_SELF']) === 'my-disputes.php') {
    require_once 'config/database.php';
    require_once 'config/config.php';
    require_once 'models/Dispute.php';

    if (!is_logged_in()) {
        redirect('login.php');
    }

    $database = new Database();
    $db = $database->getConnection();
    $dispute = new Dispute($db);

    $user_id = get_current_user_id();
    $disputes = $dispute->getDisputeById($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Disputes - <?php echo SITE_NAME; ?></title>
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

        <h2><i class="fas fa-exclamation-triangle"></i> My Disputes</h2>

        <?php if (empty($disputes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-handshake fa-3x text-success mb-3"></i>
                <h4>No disputes found</h4>
                <p class="text-muted">Great! You haven't had any issues with your transactions.</p>
                <a href="my-orders.php" class="btn btn-primary">View My Orders</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($disputes as $dispute_item): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Dispute #<?php echo $dispute_item['id']; ?></h6>
                            <?php
                            $status_colors = [
                                'open' => 'danger',
                                'under_review' => 'warning',
                                'resolved' => 'success',
                                'closed' => 'secondary'
                            ];
                            $color = $status_colors[$dispute_item['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $dispute_item['status'])); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Order:</strong> #<?php echo $dispute_item['order_number']; ?><br>
                                <strong>Book:</strong> <?php echo htmlspecialchars($dispute_item['book_title']); ?><br>
                                <strong>Type:</strong> 
                                <span class="badge bg-secondary"><?php echo ucfirst($dispute_item['dispute_type']); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong>Issue:</strong>
                                <p class="text-muted small"><?php echo nl2br(htmlspecialchars(substr($dispute_item['description'], 0, 150))); ?>
                                <?php if (strlen($dispute_item['description']) > 150): ?>...<?php endif; ?></p>
                            </div>

                            <div class="mb-2">
                                <strong>Other Party:</strong> 
                                <?php
                                $other_party = ($dispute_item['complainant_id'] == $user_id) 
                                    ? $dispute_item['respondent_name'] 
                                    : $dispute_item['complainant_name'];
                                echo htmlspecialchars($other_party);
                                ?>
                            </div>

                            <small class="text-muted">
                                <strong>Created:</strong> <?php echo time_ago($dispute_item['created_at']); ?>
                            </small>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                    data-bs-target="#disputeModal<?php echo $dispute_item['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>

                    <!-- Dispute Details Modal -->
                    <div class="modal fade" id="disputeModal<?php echo $dispute_item['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Dispute #<?php echo $dispute_item['id']; ?> Details
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Order ID:</strong> #<?php echo $dispute_item['order_number']; ?><br>
                                            <strong>Book:</strong> <?php echo htmlspecialchars($dispute_item['book_title']); ?><br>
                                            <strong>Dispute Type:</strong> <?php echo ucfirst($dispute_item['dispute_type']); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Status:</strong> 
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $dispute_item['status'])); ?>
                                            </span><br>
                                            <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($dispute_item['created_at'])); ?><br>
                                            <strong>Last Updated:</strong> <?php echo time_ago($dispute_item['updated_at']); ?>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Your Description:</strong>
                                        <div class="border p-3 bg-light rounded">
                                            <?php echo nl2br(htmlspecialchars($dispute_item['description'])); ?>
                                        </div>
                                    </div>

                                    <?php if ($dispute_item['admin_notes']): ?>
                                    <div class="mb-3">
                                        <strong>Admin Notes:</strong>
                                        <div class="border p-3 bg-info bg-opacity-10 rounded">
                                            <?php echo nl2br(htmlspecialchars($dispute_item['admin_notes'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($dispute_item['resolution'] && $dispute_item['status'] === 'resolved'): ?>
                                    <div class="mb-3">
                                        <strong>Resolution:</strong>
                                        <div class="border p-3 bg-success bg-opacity-10 rounded">
                                            <?php echo nl2br(htmlspecialchars($dispute_item['resolution'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($dispute_item['status'] === 'open'): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        Your dispute is waiting for admin review. You will be notified of any updates.
                                    </div>
                                    <?php elseif ($dispute_item['status'] === 'under_review'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-search"></i> 
                                        Admin is currently reviewing your dispute. Please check back for updates.
                                    </div>
                                    <?php elseif ($dispute_item['status'] === 'resolved'): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> 
                                        This dispute has been resolved by admin.
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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