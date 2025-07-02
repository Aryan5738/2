<?php
// Handle order status updates
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = sanitizeInput($_POST['status']);
    
    $validStatuses = ['pending', 'confirmed', 'preparing', 'delivered', 'cancelled'];
    
    if ($orderId && in_array($newStatus, $validStatuses)) {
        try {
            $db->update('orders', ['status' => $newStatus], 'id = ?', [$orderId]);
            $message = 'Order status updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating order status: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid order or status';
        $messageType = 'error';
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$dateFilter = $_GET['date'] ?? 'all';

// Build query
$whereConditions = [];
$params = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = 'o.status = ?';
    $params[] = $statusFilter;
}

if ($dateFilter !== 'all') {
    switch ($dateFilter) {
        case 'today':
            $whereConditions[] = 'DATE(o.created_at) = CURDATE()';
            break;
        case 'week':
            $whereConditions[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            break;
        case 'month':
            $whereConditions[] = 'o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            break;
    }
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get orders with user information
$orders = $db->fetchAll("
    SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
           COUNT(oi.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    {$whereClause}
    GROUP BY o.id
    ORDER BY o.created_at DESC
", $params);

// Get order items for each order
foreach ($orders as &$order) {
    $order['items'] = $db->fetchAll('
        SELECT oi.*, d.image
        FROM order_items oi
        LEFT JOIN dishes d ON oi.dish_id = d.id
        WHERE oi.order_id = ?
    ', [$order['id']]);
}

// Get status counts for filters
$statusCounts = [];
$allStatuses = ['pending', 'confirmed', 'preparing', 'delivered', 'cancelled'];
foreach ($allStatuses as $status) {
    $count = $db->fetch('SELECT COUNT(*) as count FROM orders WHERE status = ?', [$status])['count'];
    $statusCounts[$status] = $count;
}
$statusCounts['all'] = array_sum($statusCounts);
?>

<!-- Orders Management Content -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold font-poppins text-white mb-2">Order Management</h1>
            <p class="text-gray-300">View and manage all restaurant orders</p>
        </div>
        
        <!-- Filters -->
        <div class="flex gap-4">
            <!-- Status Filter -->
            <select onchange="updateFilter('status', this.value)" class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:border-amber-400">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status (<?php echo $statusCounts['all']; ?>)</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending (<?php echo $statusCounts['pending']; ?>)</option>
                <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed (<?php echo $statusCounts['confirmed']; ?>)</option>
                <option value="preparing" <?php echo $statusFilter === 'preparing' ? 'selected' : ''; ?>>Preparing (<?php echo $statusCounts['preparing']; ?>)</option>
                <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered (<?php echo $statusCounts['delivered']; ?>)</option>
                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled (<?php echo $statusCounts['cancelled']; ?>)</option>
            </select>
            
            <!-- Date Filter -->
            <select onchange="updateFilter('date', this.value)" class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:border-amber-400">
                <option value="all" <?php echo $dateFilter === 'all' ? 'selected' : ''; ?>>All Time</option>
                <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $dateFilter === 'week' ? 'selected' : ''; ?>>This Week</option>
                <option value="month" <?php echo $dateFilter === 'month' ? 'selected' : ''; ?>>This Month</option>
            </select>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-300' : 'bg-red-500/20 border border-red-500/50 text-red-300'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Orders List -->
<div class="space-y-6">
    <?php if (empty($orders)): ?>
        <div class="glass rounded-2xl p-12 text-center">
            <i class="fas fa-shopping-bag text-6xl text-gray-400 mb-6"></i>
            <h3 class="text-2xl font-bold text-white mb-4">No orders found</h3>
            <p class="text-gray-400">No orders match your current filters.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="glass rounded-2xl p-6">
                <!-- Order Header -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-2">Order #<?php echo $order['id']; ?></h3>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <span class="text-gray-300">
                                <i class="fas fa-user mr-1"></i>
                                <?php echo $order['user_name']; ?>
                            </span>
                            <span class="text-gray-300">
                                <i class="fas fa-envelope mr-1"></i>
                                <?php echo $order['user_email']; ?>
                            </span>
                            <?php if ($order['user_phone']): ?>
                                <span class="text-gray-300">
                                    <i class="fas fa-phone mr-1"></i>
                                    <?php echo $order['user_phone']; ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-gray-300">
                                <i class="fas fa-clock mr-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 mt-4 lg:mt-0">
                        <span class="text-2xl font-bold text-white">$<?php echo number_format($order['total'], 2); ?></span>
                        
                        <!-- Status Update Form -->
                        <form method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select 
                                name="status" 
                                onchange="this.form.submit()" 
                                class="px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white text-sm focus:outline-none focus:border-amber-400"
                            >
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-white mb-4">Order Items (<?php echo $order['item_count']; ?>)</h4>
                    <div class="grid gap-3">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="flex items-center gap-4 p-3 bg-white/5 rounded-xl">
                                <div class="w-16 h-16 bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg overflow-hidden flex-shrink-0">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['dish_name']; ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                                            <i class="fas fa-utensils text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-white font-medium"><?php echo $item['dish_name']; ?></h5>
                                    <p class="text-gray-400 text-sm">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <span class="text-white font-bold">$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Delivery Address -->
                <div class="border-t border-white/10 pt-4">
                    <h4 class="text-white font-medium mb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Delivery Address
                    </h4>
                    <p class="text-gray-300"><?php echo nl2br($order['address']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function updateFilter(type, value) {
    const url = new URL(window.location);
    url.searchParams.set(type, value);
    window.location.href = url.toString();
}
</script>