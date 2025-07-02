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
    $bankName = sanitizeInput($_POST['bank_name'] ?? '');
    $accountNumber = sanitizeInput($_POST['account_number'] ?? '');
    $ifscCode = sanitizeInput($_POST['ifsc_code'] ?? '');
    $accountHolder = sanitizeInput($_POST['account_holder'] ?? '');
    $upiId = sanitizeInput($_POST['upi_id'] ?? '');
    $withdrawalPassword = $_POST['withdrawal_password'] ?? '';
    
    // Validation
    if ($amount < 500) {
        $error = 'Minimum withdrawal amount is ₹500';
    } elseif ($amount > $user['balance']) {
        $error = 'Insufficient balance. Your current balance is ' . formatCurrency($user['balance']);
    } elseif (empty($withdrawalPassword)) {
        $error = 'Withdrawal password is required for security';
    } elseif (!password_verify($withdrawalPassword, $user['password'])) {
        $error = 'Invalid withdrawal password';
    } elseif (empty($bankName) && empty($upiId)) {
        $error = 'Please provide either bank details or UPI ID';
    } else {
        try {
            // Insert withdrawal request
            $stmt = $pdo->prepare("
                INSERT INTO withdrawals (user_id, amount, bank_name, account_number, ifsc_code, account_holder, upi_id, withdrawal_password, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $hashedPassword = password_hash($withdrawalPassword, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$user['id'], $amount, $bankName, $accountNumber, $ifscCode, $accountHolder, $upiId, $hashedPassword])) {
                // Send notification
                sendNotification($user['id'], 'Withdrawal Request Submitted', 
                    "Your withdrawal request for ₹{$amount} has been submitted and is pending approval.", 'info');
                
                $success = 'Withdrawal request submitted successfully! It will be processed within 24-48 hours.';
            } else {
                $error = 'Failed to submit withdrawal request. Please try again.';
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
    <title>Withdraw - 91CLUB</title>
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
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-minus-circle"></i> Withdraw Money</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <!-- Balance Display -->
                        <div class="balance-display mb-4">
                            <h5><i class="fas fa-wallet"></i> Available Balance</h5>
                            <h2><?= formatCurrency($user['balance']) ?></h2>
                        </div>

                        <!-- Withdrawal Instructions -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Withdrawal Instructions</h6>
                            <ul class="mb-0 small">
                                <li>Minimum withdrawal amount: ₹500</li>
                                <li>Processing time: 24-48 hours</li>
                                <li>Withdrawal fee: No fee</li>
                                <li>Enter your account password for security verification</li>
                            </ul>
                        </div>

                        <!-- Withdrawal Form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-rupee-sign"></i> Amount *
                                </label>
                                <input type="number" name="amount" class="form-control" 
                                       min="500" max="<?= $user['balance'] ?>" step="1" required
                                       placeholder="Enter amount (Min: ₹500)"
                                       value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>">
                                <small class="text-muted">Maximum: <?= formatCurrency($user['balance']) ?></small>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="bank_method" value="bank" checked>
                                    <label class="btn btn-outline-primary" for="bank_method">
                                        <i class="fas fa-university"></i> Bank Transfer
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="payment_method" id="upi_method" value="upi">
                                    <label class="btn btn-outline-primary" for="upi_method">
                                        <i class="fab fa-google-pay"></i> UPI
                                    </label>
                                </div>
                            </div>

                            <!-- Bank Details Section -->
                            <div id="bankDetails">
                                <h6 class="text-primary"><i class="fas fa-university"></i> Bank Details</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Bank Name *</label>
                                    <input type="text" name="bank_name" class="form-control" 
                                           placeholder="Enter bank name"
                                           value="<?= htmlspecialchars($_POST['bank_name'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Account Number *</label>
                                    <input type="text" name="account_number" class="form-control" 
                                           placeholder="Enter account number"
                                           value="<?= htmlspecialchars($_POST['account_number'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">IFSC Code *</label>
                                    <input type="text" name="ifsc_code" class="form-control" 
                                           placeholder="Enter IFSC code"
                                           value="<?= htmlspecialchars($_POST['ifsc_code'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Account Holder Name *</label>
                                    <input type="text" name="account_holder" class="form-control" 
                                           placeholder="Enter account holder name"
                                           value="<?= htmlspecialchars($_POST['account_holder'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- UPI Details Section -->
                            <div id="upiDetails" style="display: none;">
                                <h6 class="text-primary"><i class="fab fa-google-pay"></i> UPI Details</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">UPI ID *</label>
                                    <input type="text" name="upi_id" class="form-control" 
                                           placeholder="Enter UPI ID (e.g., user@paytm)"
                                           value="<?= htmlspecialchars($_POST['upi_id'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Account Password * 
                                    <small class="text-muted">(for security verification)</small>
                                </label>
                                <input type="password" name="withdrawal_password" class="form-control" required
                                       placeholder="Enter your account password">
                            </div>

                            <div class="alert alert-warning">
                                <small>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Security Notice:</strong> Please ensure all details are correct. 
                                    Withdrawals are processed manually and cannot be reversed once completed.
                                </small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-minus-circle"></i> Submit Withdrawal Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Withdrawal History -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Withdrawals</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT amount, bank_name, upi_id, status, created_at 
                            FROM withdrawals 
                            WHERE user_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$user['id']]);
                        $recentWithdrawals = $stmt->fetchAll();
                        
                        if ($recentWithdrawals): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentWithdrawals as $withdrawal): ?>
                                            <tr>
                                                <td><?= formatCurrency($withdrawal['amount']) ?></td>
                                                <td>
                                                    <?= $withdrawal['upi_id'] ? 'UPI' : 'Bank' ?>
                                                    <small class="text-muted d-block">
                                                        <?= $withdrawal['upi_id'] ?: $withdrawal['bank_name'] ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $withdrawal['status'] === 'approved' ? 'success' : ($withdrawal['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                        <?= ucfirst($withdrawal['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y H:i', strtotime($withdrawal['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No withdrawals yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle payment method sections
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const bankDetails = document.getElementById('bankDetails');
                const upiDetails = document.getElementById('upiDetails');
                
                if (this.value === 'bank') {
                    bankDetails.style.display = 'block';
                    upiDetails.style.display = 'none';
                    // Make bank fields required
                    bankDetails.querySelectorAll('input').forEach(input => input.required = true);
                    upiDetails.querySelectorAll('input').forEach(input => input.required = false);
                } else {
                    bankDetails.style.display = 'none';
                    upiDetails.style.display = 'block';
                    // Make UPI fields required
                    bankDetails.querySelectorAll('input').forEach(input => input.required = false);
                    upiDetails.querySelectorAll('input').forEach(input => input.required = true);
                }
            });
        });
    </script>
</body>
</html>