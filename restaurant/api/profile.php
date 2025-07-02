<?php
require_once '../config/database.php';

corsHeaders();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $userId = requireAuth();
    getProfile($db, $userId);
} elseif ($method === 'POST') {
    $userId = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    updateProfile($db, $userId, $input);
} else {
    respondWithError('Method not allowed', 405);
}

function getProfile($db, $userId) {
    try {
        $user = $db->fetch('SELECT id, name, email, phone, address, created_at FROM users WHERE id = ?', [$userId]);
        
        if (!$user) {
            respondWithError('User not found', 404);
        }

        // Get order statistics
        $orderStats = $db->fetch('
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_spent,
                COUNT(CASE WHEN status = "delivered" THEN 1 END) as completed_orders
            FROM orders 
            WHERE user_id = ?
        ', [$userId]);

        // Get recent orders
        $recentOrders = $db->fetchAll('
            SELECT id, total, status, created_at
            FROM orders 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ', [$userId]);

        respondWithJSON([
            'success' => true,
            'user' => $user,
            'stats' => $orderStats,
            'recent_orders' => $recentOrders
        ]);
    } catch (Exception $e) {
        respondWithError('Failed to fetch profile: ' . $e->getMessage());
    }
}

function updateProfile($db, $userId, $data) {
    $name = sanitizeInput($data['name'] ?? '');
    $phone = sanitizeInput($data['phone'] ?? '');
    $address = sanitizeInput($data['address'] ?? '');
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';

    if (empty($name)) {
        respondWithError('Name is required');
    }

    try {
        $updateData = [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ];

        // Handle password update if provided
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                respondWithError('Current password is required to set new password');
            }

            // Verify current password
            $user = $db->fetch('SELECT password FROM users WHERE id = ?', [$userId]);
            if (!password_verify($currentPassword, $user['password'])) {
                respondWithError('Current password is incorrect');
            }

            if (strlen($newPassword) < 6) {
                respondWithError('New password must be at least 6 characters long');
            }

            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Update user
        $db->update('users', $updateData, 'id = ?', [$userId]);

        // Update session if name changed
        if (isset($_SESSION['user_name'])) {
            $_SESSION['user_name'] = $name;
        }

        respondWithJSON([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } catch (Exception $e) {
        respondWithError('Failed to update profile: ' . $e->getMessage());
    }
}
?>