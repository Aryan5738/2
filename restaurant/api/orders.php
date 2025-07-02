<?php
require_once '../config/database.php';

corsHeaders();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $userId = requireAuth();
    getUserOrders($db, $userId);
} elseif ($method === 'POST') {
    $userId = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    placeOrder($db, $userId, $input);
} else {
    respondWithError('Method not allowed', 405);
}

function getUserOrders($db, $userId) {
    try {
        $orders = $db->fetchAll('
            SELECT o.*, COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ', [$userId]);

        // Get order items for each order
        foreach ($orders as &$order) {
            $order['items'] = $db->fetchAll('
                SELECT oi.*, d.image
                FROM order_items oi
                LEFT JOIN dishes d ON oi.dish_id = d.id
                WHERE oi.order_id = ?
            ', [$order['id']]);
        }

        respondWithJSON([
            'success' => true,
            'orders' => $orders,
            'total_orders' => count($orders)
        ]);
    } catch (Exception $e) {
        respondWithError('Failed to fetch orders: ' . $e->getMessage());
    }
}

function placeOrder($db, $userId, $data) {
    $address = sanitizeInput($data['address'] ?? '');
    $specialInstructions = sanitizeInput($data['special_instructions'] ?? '');

    if (empty($address)) {
        respondWithError('Delivery address is required');
    }

    try {
        // Begin transaction
        $db->getConnection()->beginTransaction();

        // Get cart items
        $cartItems = $db->fetchAll('
            SELECT c.*, d.name, d.price 
            FROM cart c 
            JOIN dishes d ON c.dish_id = d.id 
            WHERE c.user_id = ? AND d.visible = TRUE
        ', [$userId]);

        if (empty($cartItems)) {
            $db->getConnection()->rollback();
            respondWithError('Cart is empty');
        }

        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Create order
        $orderId = $db->insert('orders', [
            'user_id' => $userId,
            'total' => $total,
            'address' => $address,
            'status' => 'pending'
        ]);

        // Create order items
        foreach ($cartItems as $item) {
            $db->insert('order_items', [
                'order_id' => $orderId,
                'dish_id' => $item['dish_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'dish_name' => $item['name']
            ]);
        }

        // Clear cart
        $db->delete('cart', 'user_id = ?', [$userId]);

        // Commit transaction
        $db->getConnection()->commit();

        respondWithJSON([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'total' => $total
        ]);
    } catch (Exception $e) {
        $db->getConnection()->rollback();
        respondWithError('Failed to place order: ' . $e->getMessage());
    }
}
?>