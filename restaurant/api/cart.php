<?php
require_once '../config/database.php';

corsHeaders();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $userId = requireAuth();
    getCart($db, $userId);
} elseif ($method === 'POST') {
    $userId = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'add':
            addToCart($db, $userId, $input);
            break;
        case 'update':
            updateCart($db, $userId, $input);
            break;
        case 'remove':
            removeFromCart($db, $userId, $input);
            break;
        case 'clear':
            clearCart($db, $userId);
            break;
        default:
            respondWithError('Invalid action');
    }
} else {
    respondWithError('Method not allowed', 405);
}

function getCart($db, $userId) {
    try {
        $cartItems = $db->fetchAll('
            SELECT c.*, d.name, d.description, d.price, d.image 
            FROM cart c 
            JOIN dishes d ON c.dish_id = d.id 
            WHERE c.user_id = ? AND d.visible = TRUE
            ORDER BY c.created_at DESC
        ', [$userId]);

        $total = 0;
        foreach ($cartItems as &$item) {
            $item['subtotal'] = $item['price'] * $item['quantity'];
            $total += $item['subtotal'];
        }

        respondWithJSON([
            'success' => true,
            'cart_items' => $cartItems,
            'total' => $total,
            'item_count' => count($cartItems)
        ]);
    } catch (Exception $e) {
        respondWithError('Failed to fetch cart: ' . $e->getMessage());
    }
}

function addToCart($db, $userId, $data) {
    $dishId = $data['dish_id'] ?? null;
    $quantity = max(1, intval($data['quantity'] ?? 1));

    if (!$dishId) {
        respondWithError('Dish ID is required');
    }

    try {
        // Check if dish exists and is visible
        $dish = $db->fetch('SELECT * FROM dishes WHERE id = ? AND visible = TRUE', [$dishId]);
        if (!$dish) {
            respondWithError('Dish not found or not available');
        }

        // Check if item already in cart
        $existingItem = $db->fetch('SELECT * FROM cart WHERE user_id = ? AND dish_id = ?', [$userId, $dishId]);
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $db->update('cart', ['quantity' => $newQuantity], 'user_id = ? AND dish_id = ?', [$userId, $dishId]);
        } else {
            // Add new item
            $db->insert('cart', [
                'user_id' => $userId,
                'dish_id' => $dishId,
                'quantity' => $quantity
            ]);
        }

        respondWithJSON(['success' => true, 'message' => 'Item added to cart']);
    } catch (Exception $e) {
        respondWithError('Failed to add to cart: ' . $e->getMessage());
    }
}

function updateCart($db, $userId, $data) {
    $dishId = $data['dish_id'] ?? null;
    $quantity = max(0, intval($data['quantity'] ?? 0));

    if (!$dishId) {
        respondWithError('Dish ID is required');
    }

    try {
        if ($quantity === 0) {
            // Remove item if quantity is 0
            $db->delete('cart', 'user_id = ? AND dish_id = ?', [$userId, $dishId]);
        } else {
            // Update quantity
            $db->update('cart', ['quantity' => $quantity], 'user_id = ? AND dish_id = ?', [$userId, $dishId]);
        }

        respondWithJSON(['success' => true, 'message' => 'Cart updated']);
    } catch (Exception $e) {
        respondWithError('Failed to update cart: ' . $e->getMessage());
    }
}

function removeFromCart($db, $userId, $data) {
    $dishId = $data['dish_id'] ?? null;

    if (!$dishId) {
        respondWithError('Dish ID is required');
    }

    try {
        $db->delete('cart', 'user_id = ? AND dish_id = ?', [$userId, $dishId]);
        respondWithJSON(['success' => true, 'message' => 'Item removed from cart']);
    } catch (Exception $e) {
        respondWithError('Failed to remove from cart: ' . $e->getMessage());
    }
}

function clearCart($db, $userId) {
    try {
        $db->delete('cart', 'user_id = ?', [$userId]);
        respondWithJSON(['success' => true, 'message' => 'Cart cleared']);
    } catch (Exception $e) {
        respondWithError('Failed to clear cart: ' . $e->getMessage());
    }
}
?>