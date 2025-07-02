<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

try {
    // Get current round
    $currentRound = getCurrentRound();
    
    if (!$currentRound) {
        // Create new round if none exists
        $newRoundId = createNewRound();
        if ($newRoundId) {
            $currentRound = getCurrentRound();
        } else {
            throw new Exception('Failed to create new round');
        }
    }
    
    // Calculate countdown
    $countdown = getRoundCountdown($currentRound['id']);
    
    // If countdown is 0 or negative, check if we need to create a new round
    if ($countdown <= 0) {
        // Check if this round should be completed
        $stmt = $pdo->prepare("SELECT * FROM rounds WHERE id = ? AND status = 'active'");
        $stmt->execute([$currentRound['id']]);
        $activeRound = $stmt->fetch();
        
        if ($activeRound) {
            $startTime = strtotime($activeRound['start_time']);
            $currentTime = time();
            $elapsed = $currentTime - $startTime;
            
            // If more than 65 seconds have passed, auto-complete this round
            if ($elapsed > 65) {
                // Auto-generate result
                $resultColor = ['red', 'green', 'violet'][array_rand(['red', 'green', 'violet'])];
                $resultNumber = rand(0, 9);
                $resultSize = ($resultNumber >= 0 && $resultNumber <= 4) ? 'small' : 'big';
                
                // Process round results
                processRoundResults($currentRound['id'], $resultColor, $resultSize, $resultNumber);
                
                // Create new round
                createNewRound();
                $currentRound = getCurrentRound();
                $countdown = getRoundCountdown($currentRound['id']);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'countdown' => max(0, $countdown),
        'round_number' => $currentRound['round_number'],
        'round_id' => $currentRound['id'],
        'status' => $currentRound['status']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'countdown' => 0,
        'round_number' => 1
    ]);
}
?>