<?php
// Get all users with their order statistics
$users = $db->fetchAll('
    SELECT u.*, 
           COUNT(o.id) as total_orders,
           COALESCE(SUM(CASE WHEN o.status = "delivered" THEN o.total ELSE 0 END), 0) as total_spent,
           MAX(o.created_at) as last_order_date
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
');

// Get search filter
$search = $_GET['search'] ?? '';
if ($search) {
    $users = array_filter($users, function($user) use ($search) {
        return stripos($user['name'], $search) !== false || 
               stripos($user['email'], $search) !== false ||
               stripos($user['phone'], $search) !== false;
    });
}
?>

<!-- Users Management Content -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold font-poppins text-white mb-2">User Management</h1>
            <p class="text-gray-300">View and manage registered users</p>
        </div>
        
        <!-- Search -->
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input
                type="text"
                placeholder="Search users..."
                value="<?php echo htmlspecialchars($search); ?>"
                onchange="searchUsers(this.value)"
                class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
            />
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid md:grid-cols-4 gap-6 mb-8">
    <?php
    $totalUsers = count($users);
    $activeUsers = count(array_filter($users, function($user) { return $user['total_orders'] > 0; }));
    $newUsersThisMonth = count(array_filter($users, function($user) { 
        return strtotime($user['created_at']) > strtotime('-30 days'); 
    }));
    $totalRevenue = array_sum(array_column($users, 'total_spent'));
    ?>
    
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1"><?php echo number_format($totalUsers); ?></h3>
        <p class="text-gray-300">Total Users</p>
    </div>
    
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-white text-xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1"><?php echo number_format($activeUsers); ?></h3>
        <p class="text-gray-300">Active Users</p>
    </div>
    
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-plus text-white text-xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1"><?php echo number_format($newUsersThisMonth); ?></h3>
        <p class="text-gray-300">New This Month</p>
    </div>
    
    <div class="glass rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-dollar-sign text-white text-xl"></i>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1">$<?php echo number_format($totalRevenue, 2); ?></h3>
        <p class="text-gray-300">Total Revenue</p>
    </div>
</div>

<!-- Users Table -->
<div class="glass rounded-2xl overflow-hidden">
    <div class="p-6 border-b border-white/10">
        <h2 class="text-2xl font-bold text-white font-montserrat">All Users (<?php echo count($users); ?>)</h2>
    </div>
    
    <?php if (empty($users)): ?>
        <div class="p-12 text-center">
            <i class="fas fa-users text-6xl text-gray-400 mb-6"></i>
            <h3 class="text-2xl font-bold text-white mb-4">No users found</h3>
            <p class="text-gray-400">
                <?php echo $search ? 'No users match your search criteria.' : 'No users have registered yet.'; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-white font-medium">User</th>
                        <th class="px-6 py-4 text-left text-white font-medium">Contact</th>
                        <th class="px-6 py-4 text-left text-white font-medium">Orders</th>
                        <th class="px-6 py-4 text-left text-white font-medium">Total Spent</th>
                        <th class="px-6 py-4 text-left text-white font-medium">Joined</th>
                        <th class="px-6 py-4 text-left text-white font-medium">Last Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <h4 class="text-white font-medium"><?php echo htmlspecialchars($user['name']); ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-300">
                                    <?php if ($user['phone']): ?>
                                        <p class="flex items-center gap-1 mb-1">
                                            <i class="fas fa-phone text-xs"></i>
                                            <?php echo htmlspecialchars($user['phone']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($user['address']): ?>
                                        <p class="flex items-start gap-1 text-sm">
                                            <i class="fas fa-map-marker-alt text-xs mt-1"></i>
                                            <span class="line-clamp-2"><?php echo htmlspecialchars($user['address']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-2xl font-bold text-white"><?php echo $user['total_orders']; ?></span>
                                    <p class="text-gray-400 text-xs">orders</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-xl font-bold text-green-400">$<?php echo number_format($user['total_spent'], 2); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-300 text-sm">
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-300 text-sm">
                                    <?php if ($user['last_order_date']): ?>
                                        <?php echo date('M j, Y', strtotime($user['last_order_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">Never</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function searchUsers(query) {
    const url = new URL(window.location);
    if (query) {
        url.searchParams.set('search', query);
    } else {
        url.searchParams.delete('search');
    }
    window.location.href = url.toString();
}
</script>