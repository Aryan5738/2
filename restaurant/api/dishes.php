<?php
require_once '../config/database.php';

corsHeaders();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $dishId = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;
    
    if ($dishId) {
        getDishById($db, $dishId);
    } else {
        getAllDishes($db, $category);
    }
} else {
    respondWithError('Method not allowed', 405);
}

function getAllDishes($db, $category = null) {
    try {
        $sql = 'SELECT * FROM dishes WHERE visible = TRUE';
        $params = [];
        
        if ($category) {
            $sql .= ' AND category = ?';
            $params[] = $category;
        }
        
        $sql .= ' ORDER BY category, name';
        
        $dishes = $db->fetchAll($sql, $params);
        
        // Group dishes by category
        $groupedDishes = [];
        foreach ($dishes as $dish) {
            $groupedDishes[$dish['category']][] = $dish;
        }
        
        respondWithJSON([
            'success' => true,
            'dishes' => $dishes,
            'grouped_dishes' => $groupedDishes,
            'total_count' => count($dishes)
        ]);
    } catch (Exception $e) {
        respondWithError('Failed to fetch dishes: ' . $e->getMessage());
    }
}

function getDishById($db, $dishId) {
    try {
        $dish = $db->fetch('SELECT * FROM dishes WHERE id = ? AND visible = TRUE', [$dishId]);
        
        if (!$dish) {
            respondWithError('Dish not found', 404);
        }
        
        respondWithJSON([
            'success' => true,
            'dish' => $dish
        ]);
    } catch (Exception $e) {
        respondWithError('Failed to fetch dish: ' . $e->getMessage());
    }
}
?>