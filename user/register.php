<?php
require_once '../config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Email already registered.';
            } else {
                // Generate unique UID
                $uid = generateUID();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (uid, username, email, phone, password) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$uid, $username, $email, $phone, $hashedPassword])) {
                    $userId = $pdo->lastInsertId();
                    
                    // Send welcome notification
                    sendNotification($userId, 'Welcome to 91CLUB!', 'Your account has been created successfully. Your UID is: ' . $uid, 'success');
                    
                    // Auto login
                    $_SESSION['user_id'] = $userId;
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - 91CLUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="row w-100">
            <div class="col-md-6 col-lg-5 mx-auto">
                <div class="card shadow-lg border-0 bg-white/10 backdrop-blur-md">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h2 class="text-white fw-bold">
                                <i class="fas fa-dice-d6 text-yellow-400"></i> 91CLUB
                            </h2>
                            <p class="text-light">Create your account</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form method="POST" id="registerForm">
                            <div class="mb-3">
                                <label class="form-label text-white">
                                    <i class="fas fa-user"></i> Username *
                                </label>
                                <input type="text" name="username" class="form-control" required 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">
                                    <i class="fas fa-phone"></i> Phone (Optional)
                                </label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input type="password" name="password" class="form-control" required 
                                       minlength="6" placeholder="At least 6 characters">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white">
                                    <i class="fas fa-lock"></i> Confirm Password *
                                </label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="termsCheck" required>
                                <label class="form-check-label text-light" for="termsCheck">
                                    I agree to the Terms of Service and Privacy Policy
                                </label>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </button>
                            </div>
                        </form>

                        <!-- Links -->
                        <div class="text-center">
                            <p class="text-light">
                                Already have an account? 
                                <a href="login.php" class="text-yellow-400 text-decoration-none">Login here</a>
                            </p>
                            <a href="../index.php" class="text-light text-decoration-none">
                                <i class="fas fa-arrow-left"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>