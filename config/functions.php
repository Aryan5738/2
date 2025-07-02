<?php
require_once 'db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate unique user ID (91CLUB + 5 digits)
 */
function generateUID() {
    global $pdo;
    
    do {
        $uid = '91CLUB' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE uid = ?");
        $stmt->execute([$uid]);
    } while ($stmt->rowCount() > 0);
    
    return $uid;
}

/**
 * Update user balance
 */
function updateUserBalance($userId, $amount, $operation = 'add') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        if ($operation === 'add') {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        }
        
        $stmt->execute([$amount, $userId]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Get current active round
 */
function getCurrentRound() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM rounds WHERE status = 'active' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Get user balance
 */
function getUserBalance($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? $result['balance'] : 0;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get user data
 */
function getUserData($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Calculate multiplier based on bet type
 */
function getMultiplier($betType, $betValue) {
    switch ($betType) {
        case 'color':
            return ($betValue === 'violet') ? 5.0 : 1.5;
        case 'size':
            return 1.5;
        case 'number':
            return 10.0;
        default:
            return 1.5;
    }
}

/**
 * Process round results
 */
function processRoundResults($roundId, $resultColor, $resultSize, $resultNumber) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Update round with results
        $stmt = $pdo->prepare("UPDATE rounds SET result_color = ?, result_size = ?, result_number = ?, status = 'completed', end_time = NOW() WHERE id = ?");
        $stmt->execute([$resultColor, $resultSize, $resultNumber, $roundId]);
        
        // Get all predictions for this round
        $stmt = $pdo->prepare("SELECT * FROM predictions WHERE round_id = ? AND status = 'pending'");
        $stmt->execute([$roundId]);
        $predictions = $stmt->fetchAll();
        
        foreach ($predictions as $prediction) {
            $isWinner = false;
            
            // Check winning conditions based on bet type
            if ($prediction['bet_type'] === 'color' && $prediction['bet_value'] === $resultColor) {
                $isWinner = true;
            } elseif ($prediction['bet_type'] === 'size' && $prediction['bet_value'] === $resultSize) {
                $isWinner = true;
            } elseif ($prediction['bet_type'] === 'number' && $prediction['bet_value'] == $resultNumber) {
                $isWinner = true;
            }
            
            if ($isWinner) {
                // Update prediction as won
                $stmt = $pdo->prepare("UPDATE predictions SET status = 'won', payout_amount = ? WHERE id = ?");
                $stmt->execute([$prediction['potential_win'], $prediction['id']]);
                
                // Credit user balance
                updateUserBalance($prediction['user_id'], $prediction['potential_win'], 'add');
            } else {
                // Update prediction as lost
                $stmt = $pdo->prepare("UPDATE predictions SET status = 'lost' WHERE id = ?");
                $stmt->execute([$prediction['id']]);
            }
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

/**
 * Create new round
 */
function createNewRound() {
    global $pdo;
    
    try {
        // Get next round number
        $stmt = $pdo->prepare("SELECT MAX(round_number) as max_round FROM rounds");
        $stmt->execute();
        $result = $stmt->fetch();
        $nextRound = ($result['max_round'] ?? 0) + 1;
        
        // Create new round
        $stmt = $pdo->prepare("INSERT INTO rounds (round_number, start_time, status) VALUES (?, NOW(), 'active')");
        $stmt->execute([$nextRound]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get round countdown (seconds remaining)
 */
function getRoundCountdown($roundId = null) {
    global $pdo;
    
    if (!$roundId) {
        $round = getCurrentRound();
        if (!$round) return 0;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM rounds WHERE id = ?");
        $stmt->execute([$roundId]);
        $round = $stmt->fetch();
    }
    
    $startTime = strtotime($round['start_time']);
    $currentTime = time();
    $elapsed = $currentTime - $startTime;
    $remaining = 60 - $elapsed; // 60 seconds per round
    
    return max(0, $remaining);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'info')");
        $stmt->execute([$userId, $action, $details]);
    } catch (Exception $e) {
        // Silent fail for logging
    }
}

/**
 * Send notification
 */
function sendNotification($userId, $title, $message, $type = 'info', $isGlobal = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_global) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$isGlobal ? null : $userId, $title, $message, $type, $isGlobal ? 1 : 0]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE (user_id = ? OR is_global = 1) 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}
?>