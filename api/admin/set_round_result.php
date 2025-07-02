<?php
require_once '../../config/functions.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isAdminLoggedIn()) {
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
    
    $resultColor = sanitizeInput($input['result_color'] ?? '');
    $resultNumber = intval($input['result_number'] ?? -1);
    $resultSize = sanitizeInput($input['result_size'] ?? '');
    
    // Validation
    if (empty($resultColor) || $resultNumber < 0 || $resultNumber > 9) {
        throw new Exception('Invalid result parameters');
    }
    
    if (!in_array($resultColor, ['red', 'green', 'violet'])) {
        throw new Exception('Invalid color');
    }
    
    // Auto-calculate size if not provided
    if (empty($resultSize)) {
        $resultSize = ($resultNumber >= 0 && $resultNumber <= 4) ? 'small' : 'big';
    }
    
    // Get current active round
    $currentRound = getCurrentRound();
    if (!$currentRound) {
        throw new Exception('No active round found');
    }
    
    // Check if round already has results
    if (!empty($currentRound['result_color'])) {
        throw new Exception('Round already has results');
    }
    
    // Process round results
    if (processRoundResults($currentRound['id'], $resultColor, $resultSize, $resultNumber)) {
        // Send notification about results
        sendNotification(null, 'Round Results', 
            "Round #{$currentRound['round_number']} Results: $resultColor $resultNumber ($resultSize)", 
            'info', true);
        
        // Create new round
        createNewRound();
        
        echo json_encode([
            'success' => true,
            'message' => 'Round result set successfully',
            'result' => [
                'round' => $currentRound['round_number'],
                'color' => $resultColor,
                'number' => $resultNumber,
                'size' => $resultSize
            ]
        ]);
    } else {
        throw new Exception('Failed to process round results');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>