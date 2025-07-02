<?php
/**
 * Auto Round Management Cron Script
 * This script should be run every minute via cron job
 * 
 * crontab entry:
 * * * * * * /usr/bin/php /path/to/your/project/cron/auto_round.php >/dev/null 2>&1
 */

require_once dirname(__DIR__) . '/config/functions.php';

// Log function for debugging
function cronLog($message) {
    $logFile = dirname(__DIR__) . '/logs/cron.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

try {
    cronLog("Auto round cron started");
    
    // Get current active round
    $currentRound = getCurrentRound();
    
    if ($currentRound) {
        $startTime = strtotime($currentRound['start_time']);
        $currentTime = time();
        $elapsed = $currentTime - $startTime;
        
        // Round duration is 60 seconds
        if ($elapsed >= 60) {
            cronLog("Round #{$currentRound['round_number']} has completed, processing results...");
            
            // Check if round already has results
            if (empty($currentRound['result_color'])) {
                // Generate random result
                $colors = ['red', 'green', 'violet'];
                $resultColor = $colors[array_rand($colors)];
                $resultNumber = rand(0, 9);
                $resultSize = ($resultNumber >= 0 && $resultNumber <= 4) ? 'small' : 'big';
                
                cronLog("Generated result: Color=$resultColor, Number=$resultNumber, Size=$resultSize");
                
                // Process round results
                if (processRoundResults($currentRound['id'], $resultColor, $resultSize, $resultNumber)) {
                    cronLog("Round #{$currentRound['round_number']} results processed successfully");
                    
                    // Send global notification about results
                    sendNotification(null, 'Round Results', 
                        "Round #{$currentRound['round_number']} Results: $resultColor $resultNumber ($resultSize)", 
                        'info', true);
                    
                } else {
                    cronLog("ERROR: Failed to process round #{$currentRound['round_number']} results");
                    throw new Exception("Failed to process round results");
                }
            } else {
                cronLog("Round #{$currentRound['round_number']} already has results");
            }
            
            // Create new round
            $newRoundId = createNewRound();
            if ($newRoundId) {
                cronLog("Created new round with ID: $newRoundId");
            } else {
                cronLog("ERROR: Failed to create new round");
                throw new Exception("Failed to create new round");
            }
        } else {
            $remaining = 60 - $elapsed;
            cronLog("Round #{$currentRound['round_number']} still active, $remaining seconds remaining");
        }
    } else {
        // No active round, create one
        cronLog("No active round found, creating new round...");
        $newRoundId = createNewRound();
        if ($newRoundId) {
            cronLog("Created initial round with ID: $newRoundId");
        } else {
            cronLog("ERROR: Failed to create initial round");
            throw new Exception("Failed to create initial round");
        }
    }
    
    // Cleanup old rounds and predictions (keep last 100 rounds)
    $stmt = $pdo->prepare("
        DELETE FROM rounds 
        WHERE status = 'completed' 
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM rounds 
                WHERE status = 'completed' 
                ORDER BY id DESC LIMIT 100
            ) as keep_rounds
        )
    ");
    $stmt->execute();
    $deletedRounds = $stmt->rowCount();
    
    if ($deletedRounds > 0) {
        cronLog("Cleaned up $deletedRounds old rounds");
    }
    
    // Update statistics (optional - for performance tracking)
    $stmt = $pdo->prepare("
        UPDATE admin_users 
        SET last_login = NOW() 
        WHERE username = 'system' 
        LIMIT 1
    ");
    $stmt->execute();
    
    cronLog("Auto round cron completed successfully");
    
} catch (Exception $e) {
    cronLog("ERROR: " . $e->getMessage());
    
    // Send error notification to admin (optional)
    try {
        sendNotification(null, 'Cron Error', 
            'Auto round cron failed: ' . $e->getMessage(), 
            'error', true);
    } catch (Exception $notifError) {
        cronLog("ERROR: Failed to send error notification: " . $notifError->getMessage());
    }
    
    exit(1);
}

// Optional: Memory and performance logging
$memoryUsage = memory_get_peak_usage(true);
$executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
cronLog("Memory usage: " . round($memoryUsage / 1024 / 1024, 2) . "MB, Execution time: " . round($executionTime, 3) . "s");

exit(0);
?>