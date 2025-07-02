<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

try {
    // Get recent completed rounds
    $stmt = $pdo->prepare("
        SELECT round_number, result_color, result_size, result_number, end_time 
        FROM rounds 
        WHERE status = 'completed' 
        ORDER BY id DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // Format results for display
    $formattedResults = [];
    foreach ($results as $result) {
        $formattedResults[] = [
            'round_number' => $result['round_number'],
            'result_color' => $result['result_color'],
            'result_size' => $result['result_size'],
            'result_number' => $result['result_number'],
            'end_time' => $result['end_time'],
            'display_time' => date('H:i', strtotime($result['end_time']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults,
        'count' => count($formattedResults)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'results' => []
    ]);
}
?>