<?php
// Include auth middleware and check if user is admin
require_once '../includes/auth.php';
checkPermission('admin');

$user = getCurrentUser();

// Get summary statistics
require_once '../config/database.php';
try {
    $pdo = getDBConnection();
    
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'employee'");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total leave requests
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leave_requests");
    $totalRequests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending requests
    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM leave_requests WHERE status = 'pending'");
    $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
    
    // Approved requests
    $stmt = $pdo->query("SELECT COUNT(*) as approved FROM leave_requests WHERE status = 'approved'");
    $approvedRequests = $stmt->fetch(PDO::FETCH_ASSOC)['approved'];
    
    // Recent requests
    $stmt = $pdo->query("
        SELECT lr.*, u.first_name, u.last_name, lt.name as leave_type_name
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        JOIN leave_types lt ON lr.leave_type_id = lt.id
        ORDER BY lr.created_at DESC
        LIMIT 5
    ");
    $recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $totalEmployees = 0;
    $totalRequests = 0;
    $pendingRequests = 0;
    $approvedRequests = 0;
    $recentRequests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Employee Leave Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
                        <a class="nav-link" href="manage_leaves.php">Manage Leaves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Manage Users</a>
                    </li>
                </ul>
            </div>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> (Admin)</span>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Admin Dashboard</h1>
                <p>Welcome to the admin dashboard. Manage employee leave requests and system settings.</p>
            </div>
        </div>
        
        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalEmployees; ?></h5>
                        <p class="card-text">Total Employees</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalRequests; ?></h5>
                        <p class="card-text">Total Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $pendingRequests; ?></h5>
                        <p class="card-text">Pending Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $approvedRequests; ?></h5>
                        <p class="card-text">Approved Requests</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Leave Requests -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Recent Leave Requests</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentRequests)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Leave Type</th>
                                            <th>Dates</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th>Applied</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRequests as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($request['leave_type_name']); ?></td>
                                                <td>
                                                    <?php echo date('M d', strtotime($request['start_date'])); ?> - 
                                                    <?php echo date('M d', strtotime($request['end_date'])); ?>
                                                </td>
                                                <td><?php echo $request['days_requested']; ?></td>
                                                <td>
                                                    <?php 
                                                    $statusClass = '';
                                                    switch($request['status']) {
                                                        case 'approved': $statusClass = 'success'; break;
                                                        case 'rejected': $statusClass = 'danger'; break;
                                                        case 'pending': $statusClass = 'warning'; break;
                                                        case 'cancelled': $statusClass = 'secondary'; break;
                                                        default: $statusClass = 'info';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($request['status']); ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No recent leave requests.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="manage_leaves.php" class="btn btn-primary">Manage Leave Requests</a>
                            <a href="users.php" class="btn btn-outline-primary">Manage Users</a>
                            <a href="#" class="btn btn-outline-primary">Generate Reports</a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">System Info</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Database:</strong> leave_management</p>
                        <p class="mb-1"><strong>Users:</strong> <?php echo ($totalEmployees + 1); ?> (including admin)</p>
                        <p class="mb-1"><strong>Leave Types:</strong> 
                            <?php 
                            try {
                                $stmt = $pdo->query("SELECT COUNT(*) as count FROM leave_types");
                                echo $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            } catch (PDOException $e) {
                                echo "N/A";
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>