<?php
// Include auth middleware and check if user is admin
require_once '../includes/auth.php';
checkPermission('admin');

$user = getCurrentUser();

// Get filter parameters
$filter_status = validateInput($_GET['status'] ?? '');
$search = validateInput($_GET['search'] ?? '');

// Get leave requests with filtering
require_once '../config/database.php';
try {
    $pdo = getDBConnection();
    
    $sql = "
        SELECT lr.*, u.first_name, u.last_name, u.employee_id, lt.name as leave_type_name
        FROM leave_requests lr
        JOIN users u ON lr.user_id = u.id
        JOIN leave_types lt ON lr.leave_type_id = lt.id
    ";
    
    $params = [];
    $where_conditions = [];
    
    if (!empty($filter_status)) {
        $where_conditions[] = "lr.status = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.employee_id LIKE ? OR lt.name LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $sql .= " ORDER BY lr.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $leaveRequests = [];
}

// Handle approve/reject actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = validateInput($_POST['action'] ?? '');
    $request_id = (int)($_POST['request_id'] ?? 0);
    $admin_comment = validateInput($_POST['admin_comment'] ?? '');
    
    if ($action && $request_id > 0) {
        try {
            if ($action === 'approve') {
                // Update request status to approved with prepared statement
                $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW(), admin_comment = ? WHERE id = ?");
                $stmt->execute([(int)$user['id'], $admin_comment, $request_id]);
                
                // Update leave balance
                $requestStmt = $pdo->prepare("SELECT user_id, leave_type_id, days_requested FROM leave_requests WHERE id = ?");
                $requestStmt->execute([$request_id]);
                $request = $requestStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($request) {
                    // Check if balance record exists for this user, type, and year
                    $balanceStmt = $pdo->prepare("
                        SELECT id, allocated_days, used_days, remaining_days 
                        FROM leave_balances 
                        WHERE user_id = ? AND leave_type_id = ? AND year = YEAR(NOW())
                    ");
                    $balanceStmt->execute([(int)$request['user_id'], (int)$request['leave_type_id']]);
                    $balance = $balanceStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($balance) {
                        // Update existing balance
                        $new_used = $balance['used_days'] + $request['days_requested'];
                        $new_remaining = $balance['remaining_days'] - $request['days_requested'];
                        
                        $updateStmt = $pdo->prepare("UPDATE leave_balances SET used_days = ?, remaining_days = ? WHERE id = ?");
                        $updateStmt->execute([$new_used, $new_remaining, (int)$balance['id']]);
                    } else {
                        // Create new balance record if it doesn't exist
                        // First, get the allocated days for this leave type
                        $typeStmt = $pdo->prepare("SELECT max_days_per_year FROM leave_types WHERE id = ?");
                        $typeStmt->execute([(int)$request['leave_type_id']]);
                        $typeInfo = $typeStmt->fetch(PDO::FETCH_ASSOC);
                        
                        $allocated = $typeInfo ? $typeInfo['max_days_per_year'] : 0;
                        $used = $request['days_requested'];
                        $remaining = $allocated - $used;
                        
                        $insertStmt = $pdo->prepare("
                            INSERT INTO leave_balances (user_id, leave_type_id, year, allocated_days, used_days, remaining_days) 
                            VALUES (?, ?, YEAR(NOW()), ?, ?, ?)
                        ");
                        $insertStmt->execute([
                            (int)$request['user_id'], 
                            (int)$request['leave_type_id'], 
                            $allocated, 
                            $used, 
                            $remaining
                        ]);
                    }
                }
                
                $message = "Leave request approved successfully!";
                
            } elseif ($action === 'reject') {
                // Update request status to rejected with prepared statement
                $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'rejected', rejected_by = ?, rejected_at = NOW(), admin_comment = ? WHERE id = ?");
                $stmt->execute([(int)$user['id'], $admin_comment, $request_id]);
                
                $message = "Leave request rejected successfully!";
            }
            
            // Refresh the data
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $message = "Error processing request: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Requests - Employee Leave Management</title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_leaves.php">Manage Leaves</a>
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
                <h1>Manage Leave Requests</h1>
                <p>Review and manage employee leave requests.</p>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Filter by Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo ($filter_status === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo ($filter_status === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="cancelled" <?php echo ($filter_status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="search" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Search by employee name, ID, or leave type..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="manage_leaves.php" class="btn btn-secondary ms-2">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Leave Requests Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Leave Requests</h5>
                        <span class="badge bg-light text-dark"><?php echo count($leaveRequests); ?> requests</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($leaveRequests)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Employee ID</th>
                                            <th>Leave Type</th>
                                            <th>Dates</th>
                                            <th>Days</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Applied Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leaveRequests as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($request['employee_id']); ?></td>
                                                <td><?php echo htmlspecialchars($request['leave_type_name']); ?></td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($request['start_date'])); ?> - 
                                                    <?php echo date('M d, Y', strtotime($request['end_date'])); ?>
                                                </td>
                                                <td><?php echo (int)$request['days_requested']; ?></td>
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
                                                    <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($request['status'])); ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($request['status'] === 'pending'): ?>
                                                        <!-- Approve/Reject buttons -->
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <!-- Approve button with modal -->
                                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo (int)$request['id']; ?>">
                                                                Approve
                                                            </button>
                                                            <!-- Reject button with modal -->
                                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo (int)$request['id']; ?>">
                                                                Reject
                                                            </button>
                                                        </div>
                                                        
                                                        <!-- Approve Modal -->
                                                        <div class="modal fade" id="approveModal<?php echo (int)$request['id']; ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Approve Leave Request</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form method="POST" action="">
                                                                        <div class="modal-body">
                                                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                                                            <input type="hidden" name="action" value="approve">
                                                                            <p>Employee: <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong></p>
                                                                            <p>Leave Type: <strong><?php echo htmlspecialchars($request['leave_type_name']); ?></strong></p>
                                                                            <p>Dates: <strong><?php echo date('M d, Y', strtotime($request['start_date'])); ?> - <?php echo date('M d, Y', strtotime($request['end_date'])); ?></strong></p>
                                                                            <div class="mb-3">
                                                                                <label for="approve_comment_<?php echo (int)$request['id']; ?>" class="form-label">Admin Comment (Optional)</label>
                                                                                <textarea class="form-control" id="approve_comment_<?php echo (int)$request['id']; ?>" name="admin_comment" rows="3"></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <button type="submit" class="btn btn-success">Approve</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Reject Modal -->
                                                        <div class="modal fade" id="rejectModal<?php echo (int)$request['id']; ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Reject Leave Request</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form method="POST" action="">
                                                                        <div class="modal-body">
                                                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                                                            <input type="hidden" name="action" value="reject">
                                                                            <p>Employee: <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong></p>
                                                                            <p>Leave Type: <strong><?php echo htmlspecialchars($request['leave_type_name']); ?></strong></p>
                                                                            <p>Dates: <strong><?php echo date('M d, Y', strtotime($request['start_date'])); ?> - <?php echo date('M d, Y', strtotime($request['end_date'])); ?></strong></p>
                                                                            <div class="mb-3">
                                                                                <label for="reject_comment_<?php echo (int)$request['id']; ?>" class="form-label">Admin Comment (Required)</label>
                                                                                <textarea class="form-control" id="reject_comment_<?php echo (int)$request['id']; ?>" name="admin_comment" rows="3" required></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <button type="submit" class="btn btn-danger">Reject</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">No actions</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <h5>No leave requests found</h5>
                                <p class="text-muted">No leave requests match your current filters.</p>
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