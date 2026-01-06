<?php
// Include auth middleware and check if user is employee
require_once '../includes/auth.php';
checkPermission('employee');

$user = getCurrentUser();

// Get user's leave requests
require_once '../config/database.php';
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT lr.*, lt.name as leave_type_name, 
               CONCAT(u2.first_name, ' ', u2.last_name) as approved_by_name
        FROM leave_requests lr
        JOIN leave_types lt ON lr.leave_type_id = lt.id
        LEFT JOIN users u2 ON lr.approved_by = u2.id
        WHERE lr.user_id = ?
        ORDER BY lr.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $leaveRequests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leaves - Employee Leave Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="apply_leave.php">Apply Leave</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_leaves.php">My Leaves</a>
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
                <h1>My Leave Requests</h1>
                <p>View your submitted leave requests and their status.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Leave Request History</h5>
                        <a href="apply_leave.php" class="btn btn-light btn-sm">Apply New Leave</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($leaveRequests)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Dates</th>
                                            <th>Days</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Applied Date</th>
                                            <th>Approved By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leaveRequests as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['leave_type_name']); ?></td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($request['start_date'])); ?> - 
                                                    <?php echo date('M d, Y', strtotime($request['end_date'])); ?>
                                                </td>
                                                <td><?php echo $request['days_requested']; ?></td>
                                                <td><?php echo htmlspecialchars(substr($request['reason'], 0, 50)) . (strlen($request['reason']) > 50 ? '...' : ''); ?></td>
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
                                                <td>
                                                    <?php 
                                                    if ($request['status'] === 'approved' && $request['approved_by_name']) {
                                                        echo htmlspecialchars($request['approved_by_name']);
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Summary Statistics -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card text-center bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo count($leaveRequests); ?></h5>
                                            <p class="card-text">Total Requests</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center bg-success text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php echo count(array_filter($leaveRequests, function($req) { return $req['status'] === 'approved'; })); ?>
                                            </h5>
                                            <p class="card-text">Approved</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center bg-warning text-dark">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php echo count(array_filter($leaveRequests, function($req) { return $req['status'] === 'pending'; })); ?>
                                            </h5>
                                            <p class="card-text">Pending</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center bg-danger text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php echo count(array_filter($leaveRequests, function($req) { return $req['status'] === 'rejected'; })); ?>
                                            </h5>
                                            <p class="card-text">Rejected</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <h5>No leave requests found</h5>
                                <p class="text-muted">You haven't submitted any leave requests yet.</p>
                                <a href="apply_leave.php" class="btn btn-success">Apply for Leave</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>