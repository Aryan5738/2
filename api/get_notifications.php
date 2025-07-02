<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'notifications' => [], 'count' => 0]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get user notifications (including global ones)
    $notifications = getUserNotifications($userId, 10);
    
    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'title' => htmlspecialchars($notification['title']),
            'message' => htmlspecialchars($notification['message']),
            'type' => $notification['type'],
            'is_read' => $notification['is_read'],
            'is_global' => $notification['is_global'],
            'created_at' => $notification['created_at'],
            'time_ago' => timeAgo($notification['created_at'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications,
        'count' => count($formattedNotifications)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'notifications' => [],
        'count' => 0
    ]);
}

// Helper function for time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j', strtotime($datetime));
}
?>