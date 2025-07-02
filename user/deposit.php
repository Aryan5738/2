<?php
require_once '../config/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUserData($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $transactionId = sanitizeInput($_POST['transaction_id'] ?? '');
    $paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'manual');
    
    // Validation
    if ($amount < 100) {
        $error = 'Minimum deposit amount is ₹100';
    } elseif ($amount > 100000) {
        $error = 'Maximum deposit amount is ₹1,00,000';
    } elseif (empty($transactionId)) {
        $error = 'Transaction ID is required';
    } else {
        try {
            // Check if transaction ID already exists
            $stmt = $pdo->prepare("SELECT id FROM deposits WHERE transaction_id = ?");
            $stmt->execute([$transactionId]);
            if ($stmt->rowCount() > 0) {
                $error = 'Transaction ID already exists. Please use a unique transaction ID.';
            } else {
                // Insert deposit request
                $stmt = $pdo->prepare("
                    INSERT INTO deposits (user_id, amount, transaction_id, payment_method, status) 
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                if ($stmt->execute([$user['id'], $amount, $transactionId, $paymentMethod])) {
                    // Send notification
                    sendNotification($user['id'], 'Deposit Request Submitted', 
                        "Your deposit request for ₹{$amount} has been submitted and is pending approval.", 'info');
                    
                    $success = 'Deposit request submitted successfully! It will be processed within 24 hours.';
                } else {
                    $error = 'Failed to submit deposit request. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit - 91CLUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-dice-d6 text-yellow-400"></i> 91CLUB
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Balance: <?= formatCurrency($user['balance']) ?></span>
                <a class="nav-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Add Money</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <!-- Payment Instructions -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Payment Instructions</h5>
                            <ol class="mb-0">
                                <li>Make payment using UPI, Bank Transfer, or Paytm</li>
                                <li>Note down the Transaction ID</li>
                                <li>Fill the form below with exact amount and Transaction ID</li>
                                <li>Money will be added to your account within 24 hours</li>
                            </ol>
                        </div>

                        <!-- Payment Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6><i class="fab fa-google-pay text-primary"></i> UPI Payment</h6>
                                        <p class="mb-0">UPI ID: <strong>91club@paytm</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6><i class="fas fa-university text-info"></i> Bank Transfer</h6>
                                        <p class="mb-0">A/C: <strong>1234567890</strong><br>IFSC: <strong>AXIS0001234</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deposit Form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-rupee-sign"></i> Amount *
                                </label>
                                <input type="number" name="amount" class="form-control" 
                                       min="100" max="100000" step="1" required
                                       placeholder="Enter amount (Min: ₹100, Max: ₹1,00,000)"
                                       value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-receipt"></i> Transaction ID *
                                </label>
                                <input type="text" name="transaction_id" class="form-control" required
                                       placeholder="Enter transaction ID from payment app"
                                       value="<?= htmlspecialchars($_POST['transaction_id'] ?? '') ?>">
                                <small class="text-muted">Enter the Transaction ID you received after making the payment</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-credit-card"></i> Payment Method
                                </label>
                                <select name="payment_method" class="form-select">
                                    <option value="upi" <?= ($_POST['payment_method'] ?? '') === 'upi' ? 'selected' : '' ?>>UPI</option>
                                    <option value="bank_transfer" <?= ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                    <option value="paytm" <?= ($_POST['payment_method'] ?? '') === 'paytm' ? 'selected' : '' ?>>Paytm</option>
                                    <option value="manual" <?= ($_POST['payment_method'] ?? '') === 'manual' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div class="alert alert-warning">
                                <small>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Important:</strong> Please ensure that the amount and transaction ID are correct. 
                                    Incorrect details may cause delays in processing your deposit.
                                </small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus-circle"></i> Submit Deposit Request
                                </button>
                            </div>
                        </form>

                        <!-- Quick Amounts -->
                        <div class="mt-3">
                            <p class="text-muted small mb-2">Quick Amount Selection:</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-primary btn-sm quick-amount" data-amount="100">₹100</button>
                                <button class="btn btn-outline-primary btn-sm quick-amount" data-amount="500">₹500</button>
                                <button class="btn btn-outline-primary btn-sm quick-amount" data-amount="1000">₹1000</button>
                                <button class="btn btn-outline-primary btn-sm quick-amount" data-amount="5000">₹5000</button>
                                <button class="btn btn-outline-primary btn-sm quick-amount" data-amount="10000">₹10000</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deposit History -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Deposits</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT amount, transaction_id, status, created_at 
                            FROM deposits 
                            WHERE user_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$user['id']]);
                        $recentDeposits = $stmt->fetchAll();
                        
                        if ($recentDeposits): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Amount</th>
                                            <th>Transaction ID</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentDeposits as $deposit): ?>
                                            <tr>
                                                <td><?= formatCurrency($deposit['amount']) ?></td>
                                                <td><?= htmlspecialchars($deposit['transaction_id']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $deposit['status'] === 'approved' ? 'success' : ($deposit['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                        <?= ucfirst($deposit['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y H:i', strtotime($deposit['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No deposits yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quick amount selection
        document.querySelectorAll('.quick-amount').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelector('input[name="amount"]').value = this.dataset.amount;
            });
        });
    </script>
</body>
</html>