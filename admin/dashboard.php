<?php
require_once '../config/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get admin info
$stmt = $pdo->prepare("SELECT username, role FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Get dashboard statistics
try {
    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE status = 'active'");
    $stmt->execute();
    $totalUsers = $stmt->fetch()['total_users'];
    
    // Total deposits
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_deposits, SUM(amount) as total_amount FROM deposits WHERE status = 'approved'");
    $stmt->execute();
    $deposits = $stmt->fetch();
    
    // Total withdrawals
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_withdrawals, SUM(amount) as total_amount FROM withdrawals WHERE status = 'approved'");
    $stmt->execute();
    $withdrawals = $stmt->fetch();
    
    // Total wallet balance
    $stmt = $pdo->prepare("SELECT SUM(balance) as total_balance FROM users");
    $stmt->execute();
    $totalBalance = $stmt->fetch()['total_balance'];
    
    // Pending deposits
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_deposits FROM deposits WHERE status = 'pending'");
    $stmt->execute();
    $pendingDeposits = $stmt->fetch()['pending_deposits'];
    
    // Pending withdrawals
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_withdrawals FROM withdrawals WHERE status = 'pending'");
    $stmt->execute();
    $pendingWithdrawals = $stmt->fetch()['pending_withdrawals'];
    
    // Current round
    $currentRound = getCurrentRound();
    
    // Today's stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as today_users FROM users WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $todayUsers = $stmt->fetch()['today_users'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as today_bets, SUM(bet_amount) as today_volume FROM predictions WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $todayStats = $stmt->fetch();

} catch (Exception $e) {
    $totalUsers = $pendingDeposits = $pendingWithdrawals = 0;
    $deposits = $withdrawals = ['total_deposits' => 0, 'total_amount' => 0];
    $totalBalance = 0;
    $todayUsers = 0;
    $todayStats = ['today_bets' => 0, 'today_volume' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - 91CLUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-red-400" href="#">
                <i class="fas fa-shield-alt"></i> 91CLUB Admin
            </a>
            
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($admin['username']) ?></span>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fas fa-user-cog"></i> <?= ucfirst($admin['role']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Site</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Users</h5>
                                <h2 class="mb-0"><?= number_format($totalUsers) ?></h2>
                                <small>+<?= $todayUsers ?> today</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Deposits</h5>
                                <h2 class="mb-0"><?= formatCurrency($deposits['total_amount'] ?? 0) ?></h2>
                                <small><?= number_format($deposits['total_deposits'] ?? 0) ?> transactions</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-down fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Withdrawals</h5>
                                <h2 class="mb-0"><?= formatCurrency($withdrawals['total_amount'] ?? 0) ?></h2>
                                <small><?= number_format($withdrawals['total_withdrawals'] ?? 0) ?> transactions</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-up fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Balance</h5>
                                <h2 class="mb-0"><?= formatCurrency($totalBalance ?? 0) ?></h2>
                                <small>User wallets</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-wallet fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Current Round -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <!-- Current Round Management -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-gamepad"></i> Round Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6>Current Round: #<?= $currentRound['round_number'] ?? 'N/A' ?></h6>
                                <p class="mb-0">Status: <span class="badge bg-<?= $currentRound['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($currentRound['status'] ?? 'N/A') ?></span></p>
                                <p class="small text-muted">Started: <?= $currentRound ? date('Y-m-d H:i:s', strtotime($currentRound['start_time'])) : 'N/A' ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="countdown-circle">
                                        <span id="adminCountdown" class="display-6 fw-bold text-primary">--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Round Result Form -->
                        <hr>
                        <form id="roundResultForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Result Color</label>
                                <select name="result_color" class="form-select" required>
                                    <option value="">Select Color</option>
                                    <option value="red">Red</option>
                                    <option value="green">Green</option>
                                    <option value="violet">Violet</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Result Number</label>
                                <select name="result_number" class="form-select" required>
                                    <option value="">Select Number</option>
                                    <?php for($i = 0; $i <= 9; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Result Size</label>
                                <select name="result_size" class="form-select" required>
                                    <option value="">Auto Calculate</option>
                                    <option value="small">Small</option>
                                    <option value="big">Big</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-check"></i> Set Result
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-warning" onclick="showPendingRequests()">
                                <i class="fas fa-clock"></i> Pending Requests 
                                <span class="badge bg-light text-dark"><?= $pendingDeposits + $pendingWithdrawals ?></span>
                            </button>
                            <button class="btn btn-info" onclick="showUserManagement()">
                                <i class="fas fa-users-cog"></i> User Management
                            </button>
                            <button class="btn btn-success" onclick="sendGlobalNotification()">
                                <i class="fas fa-bullhorn"></i> Send Notification
                            </button>
                            <button class="btn btn-primary" onclick="showReports()">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Today's Stats -->
                <div class="card mt-3">
                    <div class="card-header bg-purple text-white" style="background: #8B5CF6;">
                        <h6 class="mb-0"><i class="fas fa-calendar-day"></i> Today's Stats</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?= $todayUsers ?></h4>
                                <small class="text-muted">New Users</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?= number_format($todayStats['today_bets'] ?? 0) ?></h4>
                                <small class="text-muted">Total Bets</small>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h5 class="text-info"><?= formatCurrency($todayStats['today_volume'] ?? 0) ?></h5>
                            <small class="text-muted">Betting Volume</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Tables -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Recent Deposits</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentDeposits">Loading...</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-minus-circle"></i> Recent Withdrawals</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentWithdrawals">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Global Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="notificationForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Notification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize dashboard
        $(document).ready(function() {
            updateCountdown();
            loadRecentActivity();
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                updateCountdown();
                loadRecentActivity();
            }, 30000);
        });

        // Update countdown
        function updateCountdown() {
            fetch('../api/get_countdown.php')
                .then(response => response.json())
                .then(data => {
                    $('#adminCountdown').text(data.countdown || '--');
                })
                .catch(error => console.error('Error:', error));
        }

        // Load recent activity
        function loadRecentActivity() {
            // Load recent deposits
            fetch('../api/admin/get_recent_deposits.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.deposits.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-sm"><tbody>';
                        data.deposits.forEach(deposit => {
                            html += `<tr>
                                <td>${deposit.username}</td>
                                <td>${deposit.amount}</td>
                                <td><span class="badge bg-${deposit.status === 'pending' ? 'warning' : 'success'}">${deposit.status}</span></td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                        $('#recentDeposits').html(html);
                    } else {
                        $('#recentDeposits').html('<p class="text-muted">No recent deposits</p>');
                    }
                })
                .catch(error => {
                    $('#recentDeposits').html('<p class="text-danger">Error loading deposits</p>');
                });

            // Load recent withdrawals
            fetch('../api/admin/get_recent_withdrawals.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.withdrawals.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-sm"><tbody>';
                        data.withdrawals.forEach(withdrawal => {
                            html += `<tr>
                                <td>${withdrawal.username}</td>
                                <td>${withdrawal.amount}</td>
                                <td><span class="badge bg-${withdrawal.status === 'pending' ? 'warning' : 'success'}">${withdrawal.status}</span></td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                        $('#recentWithdrawals').html(html);
                    } else {
                        $('#recentWithdrawals').html('<p class="text-muted">No recent withdrawals</p>');
                    }
                })
                .catch(error => {
                    $('#recentWithdrawals').html('<p class="text-danger">Error loading withdrawals</p>');
                });
        }

        // Set round result
        $('#roundResultForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            if (!data.result_color || !data.result_number) {
                Swal.fire('Error', 'Please select both color and number', 'error');
                return;
            }
            
            // Auto-calculate size if not selected
            if (!data.result_size) {
                data.result_size = (parseInt(data.result_number) <= 4) ? 'small' : 'big';
            }
            
            fetch('../api/admin/set_round_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Round result set successfully!', 'success');
                    this.reset();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to set round result', 'error');
            });
        });

        // Send global notification
        function sendGlobalNotification() {
            $('#notificationModal').modal('show');
        }

        $('#notificationForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/admin/send_notification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Notification sent to all users!', 'success');
                    $('#notificationModal').modal('hide');
                    this.reset();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to send notification', 'error');
            });
        });

        // Quick action functions
        function showPendingRequests() {
            window.location.href = 'requests.php';
        }

        function showUserManagement() {
            window.location.href = 'users.php';
        }

        function showReports() {
            window.location.href = 'reports.php';
        }
    </script>
</body>
</html>