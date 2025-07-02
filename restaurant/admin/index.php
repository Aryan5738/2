<?php
session_start();
require_once '../config/database.php';

$db = new Database();

// Check if admin is logged in
$isLoggedIn = isset($_SESSION['admin_id']);

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $admin = $db->fetch('SELECT * FROM admin WHERE username = ?', [$username]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $loginError = 'Invalid username or password';
        }
    } else {
        $loginError = 'Please enter both username and password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get dashboard stats if logged in
if ($isLoggedIn) {
    $stats = [
        'total_users' => $db->fetch('SELECT COUNT(*) as count FROM users')['count'],
        'total_orders' => $db->fetch('SELECT COUNT(*) as count FROM orders')['count'],
        'total_dishes' => $db->fetch('SELECT COUNT(*) as count FROM dishes')['count'],
        'total_revenue' => $db->fetch('SELECT COALESCE(SUM(total), 0) as revenue FROM orders WHERE status = "delivered"')['revenue'],
        'pending_orders' => $db->fetch('SELECT COUNT(*) as count FROM orders WHERE status = "pending"')['count'],
        'recent_orders' => $db->fetchAll('
            SELECT o.*, u.name as user_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 10
        ')
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isLoggedIn ? 'Admin Dashboard' : 'Admin Login'; ?> - Gourmet Haven</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Montserrat:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #3730a3 100%);
            min-height: 100vh;
        }
        
        .font-poppins { font-family: 'Poppins', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-dark {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }
        
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }
        
        .sidebar-item.active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <!-- Login Page -->
    <div class="min-h-screen flex items-center justify-center py-12">
        <div class="container mx-auto px-4 max-w-md">
            <div class="glass-dark rounded-3xl p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-gradient-to-r from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shield-alt text-white text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold font-poppins text-white mb-2">Admin Login</h1>
                    <p class="text-gray-300">Access restaurant management system</p>
                </div>

                <?php if (isset($loginError)): ?>
                    <div class="bg-red-500/20 border border-red-500/50 rounded-xl p-4 mb-6">
                        <p class="text-red-300 text-center"><?php echo $loginError; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-white font-medium mb-2">Username</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="text"
                                name="username"
                                required
                                placeholder="Enter admin username"
                                class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-white font-medium mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="password"
                                name="password"
                                required
                                placeholder="Enter admin password"
                                class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        name="login"
                        class="w-full btn-primary text-white py-3 rounded-xl font-semibold text-lg flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 p-4 bg-white/5 rounded-xl border border-white/10">
                    <p class="text-gray-300 text-sm text-center mb-2">Demo Credentials:</p>
                    <p class="text-white text-sm text-center">
                        Username: admin<br/>
                        Password: admin123
                    </p>
                </div>

                <!-- Back to Site -->
                <div class="mt-6 text-center">
                    <a href="../index.html" class="text-amber-400 hover:text-amber-300 font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Restaurant
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Admin Dashboard -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 glass-dark p-6">
            <div class="flex items-center mb-8">
                <div class="w-10 h-10 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl flex items-center justify-center mr-3">
                    <i class="fas fa-utensils text-white"></i>
                </div>
                <div>
                    <h2 class="text-white font-bold font-poppins">Gourmet Haven</h2>
                    <p class="text-gray-400 text-sm">Admin Panel</p>
                </div>
            </div>

            <nav class="space-y-2">
                <a href="?page=dashboard" class="sidebar-item <?php echo (!isset($_GET['page']) || $_GET['page'] === 'dashboard') ? 'active' : ''; ?> flex items-center px-4 py-3 rounded-xl text-gray-300">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Dashboard
                </a>
                <a href="?page=dishes" class="sidebar-item <?php echo ($_GET['page'] ?? '') === 'dishes' ? 'active' : ''; ?> flex items-center px-4 py-3 rounded-xl text-gray-300">
                    <i class="fas fa-utensils mr-3"></i>
                    Manage Dishes
                </a>
                <a href="?page=orders" class="sidebar-item <?php echo ($_GET['page'] ?? '') === 'orders' ? 'active' : ''; ?> flex items-center px-4 py-3 rounded-xl text-gray-300">
                    <i class="fas fa-shopping-bag mr-3"></i>
                    Orders
                </a>
                <a href="?page=users" class="sidebar-item <?php echo ($_GET['page'] ?? '') === 'users' ? 'active' : ''; ?> flex items-center px-4 py-3 rounded-xl text-gray-300">
                    <i class="fas fa-users mr-3"></i>
                    Users
                </a>
                <a href="?page=settings" class="sidebar-item <?php echo ($_GET['page'] ?? '') === 'settings' ? 'active' : ''; ?> flex items-center px-4 py-3 rounded-xl text-gray-300">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>

            <div class="mt-auto pt-8">
                <div class="glass rounded-xl p-4 mb-4">
                    <p class="text-white font-medium">Welcome back!</p>
                    <p class="text-gray-400 text-sm"><?php echo $_SESSION['admin_username']; ?></p>
                </div>
                <a href="?logout=1" class="flex items-center px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <?php
            $page = $_GET['page'] ?? 'dashboard';
            
            switch ($page) {
                case 'dashboard':
                    include 'dashboard.php';
                    break;
                case 'dishes':
                    include 'dishes.php';
                    break;
                case 'orders':
                    include 'orders.php';
                    break;
                case 'users':
                    include 'users.php';
                    break;
                case 'settings':
                    include 'settings.php';
                    break;
                default:
                    include 'dashboard.php';
            }
            ?>
        </div>
    </div>

<?php endif; ?>

</body>
</html>