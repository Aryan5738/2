<!-- Dashboard Content -->
<div class="mb-8">
    <h1 class="text-4xl font-bold font-poppins text-white mb-2">Dashboard</h1>
    <p class="text-gray-300">Welcome to your restaurant management dashboard</p>
</div>

<!-- Stats Cards -->
<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="glass stat-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
            <span class="text-blue-400 text-sm font-medium">+12% from last month</span>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1"><?php echo number_format($stats['total_users']); ?></h3>
        <p class="text-gray-300">Total Users</p>
    </div>

    <!-- Total Orders -->
    <div class="glass stat-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-shopping-bag text-white text-xl"></i>
            </div>
            <span class="text-green-400 text-sm font-medium">+8% from last month</span>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1"><?php echo number_format($stats['total_orders']); ?></h3>
        <p class="text-gray-300">Total Orders</p>
    </div>

    <!-- Total Revenue -->
    <div class="glass stat-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-dollar-sign text-white text-xl"></i>
            </div>
            <span class="text-amber-400 text-sm font-medium">+15% from last month</span>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1">$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
        <p class="text-gray-300">Total Revenue</p>
    </div>

    <!-- Pending Orders -->
    <div class="glass stat-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-gradient-to-r from-red-400 to-pink-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-white text-xl"></i>
            </div>
            <span class="text-red-400 text-sm font-medium">Needs attention</span>
        </div>
        <h3 class="text-3xl font-bold text-white mb-1"><?php echo number_format($stats['pending_orders']); ?></h3>
        <p class="text-gray-300">Pending Orders</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-8">
    <!-- Recent Orders -->
    <div class="lg:col-span-2">
        <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white font-montserrat">Recent Orders</h2>
                <a href="?page=orders" class="text-amber-400 hover:text-amber-300 font-medium transition-colors">
                    View All
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                <?php if (empty($stats['recent_orders'])): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-shopping-bag text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-400">No orders yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                        <div class="bg-white/5 rounded-xl p-4 hover:bg-white/10 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-white font-medium">Order #<?php echo $order['id']; ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo $order['user_name']; ?></p>
                                    <p class="text-gray-500 text-xs">
                                        <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-white font-bold">$<?php echo number_format($order['total'], 2); ?></span>
                                    <div class="mt-1">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-500/20 text-yellow-400',
                                            'confirmed' => 'bg-blue-500/20 text-blue-400',
                                            'preparing' => 'bg-orange-500/20 text-orange-400',
                                            'delivered' => 'bg-green-500/20 text-green-400',
                                            'cancelled' => 'bg-red-500/20 text-red-400'
                                        ];
                                        $statusClass = $statusColors[$order['status']] ?? 'bg-gray-500/20 text-gray-400';
                                        ?>
                                        <span class="<?php echo $statusClass; ?> px-2 py-1 rounded-full text-xs font-medium capitalize">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Stats -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Quick Actions -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4 font-montserrat">Quick Actions</h3>
            <div class="space-y-3">
                <a href="?page=dishes&action=add" class="w-full btn-primary text-white p-3 rounded-xl font-medium flex items-center justify-center gap-2 hover:shadow-lg transition-all">
                    <i class="fas fa-plus"></i>
                    Add New Dish
                </a>
                <a href="?page=orders" class="w-full glass text-white p-3 rounded-xl font-medium flex items-center justify-center gap-2 hover:bg-white/20 transition-colors">
                    <i class="fas fa-list"></i>
                    Manage Orders
                </a>
                <a href="?page=settings" class="w-full glass text-white p-3 rounded-xl font-medium flex items-center justify-center gap-2 hover:bg-white/20 transition-colors">
                    <i class="fas fa-cog"></i>
                    Restaurant Settings
                </a>
            </div>
        </div>

        <!-- Menu Stats -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4 font-montserrat">Menu Overview</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-300">Total Dishes</span>
                    <span class="text-white font-bold"><?php echo $stats['total_dishes']; ?></span>
                </div>
                <?php
                $categoryCounts = $db->fetchAll('
                    SELECT category, COUNT(*) as count 
                    FROM dishes 
                    WHERE visible = TRUE 
                    GROUP BY category
                ');
                ?>
                <?php foreach ($categoryCounts as $cat): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 text-sm capitalize"><?php echo $cat['category']; ?></span>
                        <span class="text-gray-300 text-sm"><?php echo $cat['count']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- System Status -->
        <div class="glass rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4 font-montserrat">System Status</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-300">Database</span>
                    <span class="text-green-400 flex items-center gap-1">
                        <i class="fas fa-circle text-xs"></i>
                        Online
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-300">API Status</span>
                    <span class="text-green-400 flex items-center gap-1">
                        <i class="fas fa-circle text-xs"></i>
                        Active
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-300">Last Backup</span>
                    <span class="text-gray-400 text-sm">2 hours ago</span>
                </div>
            </div>
        </div>
    </div>
</div>