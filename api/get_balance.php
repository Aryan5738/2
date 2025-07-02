<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $balance = getUserBalance($userId);
    
    echo json_encode([
        'success' => true,
        'balance' => $balance,
        'formatted_balance' => formatCurrency($balance)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'balance' => 0
    ]);
}
?>