<?php
// Include auth middleware and check if user is employee
require_once '../includes/auth.php';
checkPermission('employee');

$user = getCurrentUser();

// Get user's leave balances
require_once '../config/database.php';
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT lt.name as leave_type, lb.allocated_days, lb.used_days, lb.remaining_days
        FROM leave_balances lb
        JOIN leave_types lt ON lb.leave_type_id = lt.id
        WHERE lb.user_id = ? AND lb.year = YEAR(CURDATE())
    ");
    $stmt->execute([$user['id']]);
    $leaveBalances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $leaveBalances = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Employee Leave Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Leave Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="apply_leave.php">Apply Leave</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_leaves.php">My Leaves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendar.php">Calendar</a>
                    </li>
                </ul>
            </div>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> (Employee)</span>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Employee Dashboard</h1>
                <p>Welcome to your dashboard. Here you can manage your leave requests and view your leave balance.</p>
            </div>
        </div>
        
        <!-- Employee Details Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Employee Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($_SESSION['employee_id'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($_SESSION['department'] ?? 'N/A'); ?></p>
                                <p><strong>Position:</strong> <?php echo htmlspecialchars($_SESSION['position'] ?? 'N/A'); ?></p>
                                <p><strong>Date Joined:</strong> <?php echo htmlspecialchars($_SESSION['date_joined'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Leave Balances -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Leave Balances (<?php echo date('Y'); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($leaveBalances)): ?>
                            <div class="row">
                                <?php foreach ($leaveBalances as $balance): ?>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($balance['leave_type']); ?></h6>
                                                <p class="card-text">
                                                    <strong><?php echo $balance['remaining_days']; ?></strong> days remaining
                                                </p>
                                                <small class="text-muted">
                                                    Allocated: <?php echo $balance['allocated_days']; ?> | 
                                                    Used: <?php echo $balance['used_days']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No leave balances available. Contact your administrator.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <h5>Quick Actions</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Apply Leave</h5>
                                <a href="apply_leave.php" class="btn btn-success">Apply Now</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>My Leave History</h5>
                                <a href="my_leaves.php" class="btn btn-success">View Requests</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Leave Calendar</h5>
                                <a href="calendar.php" class="btn btn-success">View Calendar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>