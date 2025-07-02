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
    $title = sanitizeInput($_POST['title'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    $type = sanitizeInput($_POST['type'] ?? 'info');
    
    // Validation
    if (empty($title) || empty($message)) {
        throw new Exception('Title and message are required');
    }
    
    if (!in_array($type, ['info', 'success', 'warning', 'error'])) {
        $type = 'info';
    }
    
    // Send global notification
    if (sendNotification(null, $title, $message, $type, true)) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification sent to all users successfully'
        ]);
    } else {
        throw new Exception('Failed to send notification');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>