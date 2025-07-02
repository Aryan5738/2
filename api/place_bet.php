<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    $userId = $_SESSION['user_id'];
    $type = sanitizeInput($input['type'] ?? '');
    $value = sanitizeInput($input['value'] ?? '');
    $multiplier = floatval($input['multiplier'] ?? 0);
    $amount = floatval($input['amount'] ?? 0);
    $potentialWin = floatval($input['potential_win'] ?? 0);
    
    // Validation
    if (empty($type) || empty($value) || $amount <= 0 || $multiplier <= 0) {
        throw new Exception('Invalid bet parameters');
    }
    
    if (!in_array($type, ['color', 'size', 'number'])) {
        throw new Exception('Invalid bet type');
    }
    
    if ($amount < 10) {
        throw new Exception('Minimum bet amount is ₹10');
    }
    
    if ($amount > 50000) {
        throw new Exception('Maximum bet amount is ₹50,000');
    }
    
    // Get current round
    $currentRound = getCurrentRound();
    if (!$currentRound) {
        throw new Exception('No active round available');
    }
    
    // Check round countdown
    $countdown = getRoundCountdown($currentRound['id']);
    if ($countdown <= 0) {
        throw new Exception('Betting time has ended for this round');
    }
    
    // Check user balance
    $userBalance = getUserBalance($userId);
    if ($userBalance < $amount) {
        throw new Exception('Insufficient balance. Please add money to your account.');
    }
    
    // Calculate correct potential win
    $calculatedMultiplier = getMultiplier($type, $value);
    $calculatedPotentialWin = $amount * $calculatedMultiplier;
    
    // Verify multiplier matches expected
    if (abs($multiplier - $calculatedMultiplier) > 0.01) {
        throw new Exception('Invalid multiplier');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Deduct amount from user balance
    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
    $stmt->execute([$amount, $userId, $amount]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Insufficient balance or update failed');
    }
    
    // Insert prediction
    $stmt = $pdo->prepare("
        INSERT INTO predictions (user_id, round_id, game_type, bet_type, bet_value, bet_amount, multiplier, potential_win, status) 
        VALUES (?, ?, 'color_prediction', ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $userId, 
        $currentRound['id'], 
        $type, 
        $value, 
        $amount, 
        $calculatedMultiplier, 
        $calculatedPotentialWin
    ]);
    
    // Get new balance
    $newBalance = getUserBalance($userId);
    
    // Log activity
    logActivity($userId, 'Bet Placed', "Placed {$type} bet on {$value} for ₹{$amount}");
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Bet placed successfully! Good luck!",
        'new_balance' => $newBalance,
        'bet_details' => [
            'type' => $type,
            'value' => $value,
            'amount' => $amount,
            'potential_win' => $calculatedPotentialWin,
            'round' => $currentRound['round_number']
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction if active
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>