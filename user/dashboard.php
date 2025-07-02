<?php
require_once '../config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUserData($_SESSION['user_id']);
$currentRound = getCurrentRound();
$notifications = getUserNotifications($_SESSION['user_id'], 5);

// If no active round, create one
if (!$currentRound) {
    createNewRound();
    $currentRound = getCurrentRound();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - 91CLUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-dice-d6 text-yellow-400"></i> 91CLUB
            </a>
            
            <!-- User Info -->
            <div class="d-flex align-items-center">
                <div class="me-3 text-white d-none d-md-block">
                    <small>UID: <?= $user['uid'] ?></small><br>
                    <strong><?= formatCurrency($user['balance']) ?></strong>
                </div>
                
                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger" id="notificationCount"><?= count($notifications) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <div id="notificationList">
                            <?php foreach ($notifications as $notification): ?>
                                <li><a class="dropdown-item small" href="#">
                                    <strong><?= htmlspecialchars($notification['title']) ?></strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($notification['message']) ?></span>
                                </a></li>
                            <?php endforeach; ?>
                        </div>
                    </ul>
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($user['username']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="deposit.php"><i class="fas fa-plus-circle"></i> Deposit</a></li>
                        <li><a class="dropdown-item" href="withdraw.php"><i class="fas fa-minus-circle"></i> Withdraw</a></li>
                        <li><a class="dropdown-item" href="history.php"><i class="fas fa-history"></i> History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Balance Display -->
    <div class="d-md-none bg-primary text-white text-center py-2">
        <div>UID: <?= $user['uid'] ?> | Balance: <?= formatCurrency($user['balance']) ?></div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Game Panel -->
            <div class="col-lg-8">
                <!-- Round Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-0">Round #<?= $currentRound['round_number'] ?></h5>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="countdown-circle">
                                        <span id="countdown" class="display-6 fw-bold text-primary">60</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-primary" onclick="refreshRound()">
                                    <i class="fas fa-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Betting Panel -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-gamepad"></i> Color Prediction</h5>
                    </div>
                    <div class="card-body">
                        <!-- Bet Amount Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Amount:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-primary bet-amount" data-amount="10">₹10</button>
                                <button class="btn btn-outline-primary bet-amount" data-amount="50">₹50</button>
                                <button class="btn btn-outline-primary bet-amount" data-amount="100">₹100</button>
                                <button class="btn btn-outline-primary bet-amount" data-amount="500">₹500</button>
                                <button class="btn btn-outline-primary bet-amount" data-amount="1000">₹1000</button>
                                <input type="number" class="form-control" id="customAmount" placeholder="Custom" style="width: 120px;">
                            </div>
                        </div>

                        <!-- Size Betting -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Size Prediction (1.5x):</label>
                            <div class="row">
                                <div class="col-6">
                                    <button class="btn btn-success btn-lg w-100 bet-button" 
                                            data-type="size" data-value="small" data-multiplier="1.5">
                                        <i class="fas fa-arrow-down"></i><br>
                                        <strong>SMALL</strong><br>
                                        <small>1,2,3,4</small>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-warning btn-lg w-100 bet-button" 
                                            data-type="size" data-value="big" data-multiplier="1.5">
                                        <i class="fas fa-arrow-up"></i><br>
                                        <strong>BIG</strong><br>
                                        <small>6,7,8,9</small>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Color Betting -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Color Prediction:</label>
                            <div class="row">
                                <div class="col-4">
                                    <button class="btn btn-danger btn-lg w-100 bet-button" 
                                            data-type="color" data-value="red" data-multiplier="1.5">
                                        <i class="fas fa-circle"></i><br>
                                        <strong>RED</strong><br>
                                        <small>1.5x</small>
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success btn-lg w-100 bet-button" 
                                            data-type="color" data-value="green" data-multiplier="1.5">
                                        <i class="fas fa-circle"></i><br>
                                        <strong>GREEN</strong><br>
                                        <small>1.5x</small>
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-purple btn-lg w-100 bet-button" 
                                            data-type="color" data-value="violet" data-multiplier="5.0" 
                                            style="background: #8B5CF6; border-color: #8B5CF6; color: white;">
                                        <i class="fas fa-circle"></i><br>
                                        <strong>VIOLET</strong><br>
                                        <small>5x</small>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Number Betting -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Number Prediction (10x):</label>
                            <div class="row">
                                <?php for ($i = 0; $i <= 9; $i++): ?>
                                    <div class="col-2 mb-2">
                                        <button class="btn btn-outline-dark btn-sm w-100 bet-button" 
                                                data-type="number" data-value="<?= $i ?>" data-multiplier="10.0">
                                            <?= $i ?>
                                        </button>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Game Hub -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-th-large"></i> Game Hub</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <a href="games/minesweeper.php" class="text-decoration-none">
                                    <div class="card h-100 text-center hover-card">
                                        <div class="card-body">
                                            <i class="fas fa-bomb text-3xl text-orange-500"></i>
                                            <h6 class="mt-2">Minesweeper</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="games/dice.php" class="text-decoration-none">
                                    <div class="card h-100 text-center hover-card">
                                        <div class="card-body">
                                            <i class="fas fa-dice text-3xl text-blue-500"></i>
                                            <h6 class="mt-2">Dice Roll</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="games/coinflip.php" class="text-decoration-none">
                                    <div class="card h-100 text-center hover-card">
                                        <div class="card-body">
                                            <i class="fas fa-coins text-3xl text-yellow-500"></i>
                                            <h6 class="mt-2">Coin Flip</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="games/numbers.php" class="text-decoration-none">
                                    <div class="card h-100 text-center hover-card">
                                        <div class="card-body">
                                            <i class="fas fa-hashtag text-3xl text-purple-500"></i>
                                            <h6 class="mt-2">Number Game</h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Recent Results -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-history"></i> Recent Results</h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="recentResults">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="deposit.php" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Add Money
                            </a>
                            <a href="withdraw.php" class="btn btn-warning">
                                <i class="fas fa-minus-circle"></i> Withdraw
                            </a>
                            <a href="history.php" class="btn btn-info">
                                <i class="fas fa-history"></i> Game History
                            </a>
                            <a href="referral.php" class="btn btn-primary">
                                <i class="fas fa-share"></i> Refer & Earn
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Live Chat -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-comments"></i> Live Chat</h6>
                    </div>
                    <div class="card-body" style="height: 300px; overflow-y: auto;">
                        <div class="text-center text-muted">
                            <i class="fas fa-comment-slash"></i><br>
                            Chat feature coming soon!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Betting Confirmation Modal -->
    <div class="modal fade" id="betConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Bet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="betDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmBet">Confirm Bet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>