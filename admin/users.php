<?php
// Include auth middleware and check if user is admin
require_once '../includes/auth.php';
checkPermission('admin');

$user = getCurrentUser();

// Get all users
require_once '../config/database.php';
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT id, username, email, first_name, last_name, employee_id, department, position, role, date_joined, is_active
        FROM users
        ORDER BY role, first_name, last_name
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Employee Leave Management</title>
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
                        <a class="nav-link" href="manage_leaves.php">Manage Leaves</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php">Manage Users</a>
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
                <h1>Manage Users</h1>
                <p>View and manage all system users.</p>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Users</h5>
                        <span class="badge bg-light text-dark"><?php echo count($users); ?> users</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($users)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Employee ID</th>
                                            <th>Department</th>
                                            <th>Position</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Date Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $usr): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($usr['first_name'] . ' ' . $usr['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['username']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['email']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['employee_id']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['department']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['position']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usr['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                                        <?php echo ucfirst($usr['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usr['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $usr['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $usr['date_joined'] ? date('M d, Y', strtotime($usr['date_joined'])) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <h5>No users found</h5>
                                <p class="text-muted">No users in the system.</p>
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