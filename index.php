<?php
require_once 'config/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: user/dashboard.php');
    exit;
}

// Check if admin is logged in
if (isAdminLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>91CLUB - Multi-Game Prediction Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent">
        <div class="container">
            <a class="navbar-brand fw-bold text-2xl" href="#">
                <i class="fas fa-dice-d6 text-yellow-400"></i> 91CLUB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link btn btn-outline-light mx-2" href="user/login.php">Login</a>
                <a class="nav-link btn btn-primary mx-2" href="user/register.php">Register</a>
                <a class="nav-link btn btn-outline-warning mx-2" href="admin/login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container-fluid px-0">
        <div class="row min-vh-100 align-items-center">
            <div class="col-lg-6 px-5">
                <div class="text-white">
                    <h1 class="display-4 fw-bold mb-4">
                        🎯 Welcome to <span class="text-yellow-400">91CLUB</span>
                    </h1>
                    <p class="lead mb-4">
                        Experience the thrill of multi-game predictions! Play Color Prediction, Minesweeper, Dice, Coin Flip and more. 
                        Join thousands of players and start winning today!
                    </p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-red-600 border-0 text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-circle text-3xl mb-2"></i>
                                    <h5>Red/Green</h5>
                                    <p class="mb-0">1.5x Payout</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-purple-600 border-0 text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-circle text-3xl mb-2"></i>
                                    <h5>Violet</h5>
                                    <p class="mb-0">5x Payout</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="user/register.php" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-user-plus"></i> Start Playing
                        </a>
                        <a href="user/login.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="p-5">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <div class="card bg-gradient-to-r from-red-500 to-pink-500 border-0 text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-palette text-4xl mb-3"></i>
                                    <h5>Color Prediction</h5>
                                    <p class="small">Predict colors and win big!</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="card bg-gradient-to-r from-green-500 to-emerald-500 border-0 text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-bomb text-4xl mb-3"></i>
                                    <h5>Minesweeper</h5>
                                    <p class="small">Classic game with rewards!</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="card bg-gradient-to-r from-blue-500 to-cyan-500 border-0 text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-dice text-4xl mb-3"></i>
                                    <h5>Dice Roll</h5>
                                    <p class="small">Roll the dice and win!</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="card bg-gradient-to-r from-purple-500 to-indigo-500 border-0 text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-coins text-4xl mb-3"></i>
                                    <h5>Coin Flip</h5>
                                    <p class="small">Heads or tails challenge!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="row text-white">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-shield-alt text-yellow-400 text-5xl mb-3"></i>
                    <h4>Secure & Safe</h4>
                    <p>Your data and transactions are protected with bank-level security.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-bolt text-yellow-400 text-5xl mb-3"></i>
                    <h4>Instant Payouts</h4>
                    <p>Quick and hassle-free withdrawals to your bank account.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-users text-yellow-400 text-5xl mb-3"></i>
                    <h4>24/7 Support</h4>
                    <p>Our support team is always ready to help you.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 91CLUB. All rights reserved. Play responsibly.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>