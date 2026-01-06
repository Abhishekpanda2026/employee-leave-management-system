<?php
// Include auth middleware and check if user is employee
require_once '../includes/auth.php';
checkPermission('employee');

$user = getCurrentUser();

// Process form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $leave_type_id = (int)($_POST['leave_type'] ?? 0);
    $start_date = validateInput($_POST['start_date'] ?? '');
    $end_date = validateInput($_POST['end_date'] ?? '');
    $reason = validateInput($_POST['reason'] ?? '');
    
    // Validation
    if (empty($leave_type_id) || $leave_type_id <= 0 || empty($start_date) || empty($end_date) || empty($reason)) {
        $error = "All fields are required.";
    } elseif (!validateDate($start_date) || !validateDate($end_date)) {
        $error = "Invalid date format.";
    } elseif (strtotime($start_date) > strtotime($end_date)) {
        $error = "End date must be after start date.";
    } elseif (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $error = "Start date cannot be in the past.";
    } else {
        // Calculate number of days
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $days_requested = $interval->days + 1; // Include both start and end dates
        
        try {
            require_once '../config/database.php';
            $pdo = getDBConnection();
            
            // Check if user has enough leave balance
            $balanceStmt = $pdo->prepare("
                SELECT remaining_days 
                FROM leave_balances 
                WHERE user_id = ? AND leave_type_id = ? AND year = YEAR(?)
            ");
            $balanceStmt->execute([(int)$user['id'], $leave_type_id, $start_date]);
            $balance = $balanceStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$balance) {
                // If no balance record exists, get the default allocation for the leave type
                $typeStmt = $pdo->prepare("SELECT max_days_per_year FROM leave_types WHERE id = ?");
                $typeStmt->execute([$leave_type_id]);
                $typeInfo = $typeStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($typeInfo) {
                    $remaining_days = $typeInfo['max_days_per_year'];
                } else {
                    $error = "Invalid leave type selected.";
                }
            } else {
                $remaining_days = $balance['remaining_days'];
            }
            
            if (!$error && $remaining_days < $days_requested) {
                $error = "Insufficient leave balance for the selected period. You have {$remaining_days} days remaining for this leave type.";
            } else {
                // Insert the leave request with prepared statement
                $stmt = $pdo->prepare("
                    INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, reason, days_requested, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([
                    (int)$user['id'], 
                    $leave_type_id, 
                    $start_date, 
                    $end_date, 
                    $reason, 
                    $days_requested
                ]);
                
                $message = "Leave request submitted successfully!";
                
                // Clear form data
                $_POST = array();
            }
        } catch (PDOException $e) {
            $error = "Error submitting leave request: " . $e->getMessage();
        }
    }
}

// Get available leave types
try {
    require_once '../config/database.php';
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, name, description FROM leave_types WHERE is_active = 1 ORDER BY name");
    $leaveTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $leaveTypes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave - Employee Leave Management</title>
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
                        <a class="nav-link active" href="apply_leave.php">Apply Leave</a>
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
                <h1>Apply Leave</h1>
                <p>Submit a new leave request.</p>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Leave Request Form</h5>
                    </div>
                    <div class="card-body">
                        <form id="leaveForm" method="POST" action="">
                            <div class="mb-3">
                                <label for="leave_type" class="form-label">Leave Type</label>
                                <select class="form-select" id="leave_type" name="leave_type" required>
                                    <option value="">Select Leave Type</option>
                                    <?php foreach ($leaveTypes as $type): ?>
                                        <option value="<?php echo (int)$type['id']; ?>" <?php echo isset($_POST['leave_type']) && (int)$_POST['leave_type'] == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a leave type.</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                        <div class="invalid-feedback">Please select a start date.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                        <div class="invalid-feedback">Please select an end date.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Please provide reason for your leave..." required maxlength="500"><?php echo isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?></textarea>
                                <div class="form-text">Maximum 500 characters</div>
                                <div class="invalid-feedback">Please provide a reason for your leave.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Submit Leave Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Leave Guidelines</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>Leave requests must be submitted at least 2 days in advance</li>
                            <li>Ensure you have sufficient leave balance before applying</li>
                            <li>Leave requests are subject to approval by your manager</li>
                            <li>You will be notified once your request is processed</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h5>Your Leave Balance</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        try {
                            $balanceStmt = $pdo->prepare("
                                SELECT lt.name as leave_type, lb.remaining_days
                                FROM leave_balances lb
                                JOIN leave_types lt ON lb.leave_type_id = lt.id
                                WHERE lb.user_id = ? AND lb.year = YEAR(CURDATE())
                            ");
                            $balanceStmt->execute([(int)$user['id']]);
                            $balances = $balanceStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (!empty($balances)):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Remaining Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($balances as $balance): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($balance['leave_type']); ?></td>
                                                <td><?php echo (float)$balance['remaining_days']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No leave balances available. Contact your administrator.</p>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo "<p>Error retrieving leave balances.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript validation
        document.getElementById('leaveForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            const reason = document.getElementById('reason').value.trim();
            const leaveType = document.getElementById('leave_type').value;
            const today = new Date();
            
            let isValid = true;
            
            // Reset validation
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validate leave type
            if (leaveType === '') {
                document.getElementById('leave_type').classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate start date
            if (document.getElementById('start_date').value === '') {
                document.getElementById('start_date').classList.add('is-invalid');
                isValid = false;
            } else {
                // Check if start date is in the past
                today.setHours(0, 0, 0, 0); // Reset time for comparison
                if (startDate < today) {
                    e.preventDefault();
                    alert('Start date cannot be in the past.');
                    return;
                }
            }
            
            // Validate end date
            if (document.getElementById('end_date').value === '') {
                document.getElementById('end_date').classList.add('is-invalid');
                isValid = false;
            } else {
                // Check if end date is before start date
                if (endDate < startDate) {
                    e.preventDefault();
                    alert('End date must be after start date.');
                    return;
                }
            }
            
            // Validate reason
            if (reason === '') {
                document.getElementById('reason').classList.add('is-invalid');
                isValid = false;
            } else if (reason.length > 500) {
                e.preventDefault();
                alert('Reason cannot exceed 500 characters.');
                return;
            }
            
            // Calculate days and warn if too many
            if (startDate && endDate) {
                const timeDiff = endDate.getTime() - startDate.getTime();
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 to include both start and end dates
                
                if (daysDiff > 30) {
                    if (!confirm('Are you sure you want to request leave for ' + daysDiff + ' days?')) {
                        e.preventDefault();
                        return;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Set minimum date to today
        document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
        document.getElementById('end_date').min = new Date().toISOString().split('T')[0];
        
        // When start date changes, update end date minimum
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
        
        // Clear validation on input
        document.getElementById('leave_type').addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
        
        document.getElementById('start_date').addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
        
        document.getElementById('end_date').addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
        
        document.getElementById('reason').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    </script>
</body>
</html>