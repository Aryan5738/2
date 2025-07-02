<?php
require_once '../config/database.php';

corsHeaders();
session_start();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'register':
            register($db, $input);
            break;
        case 'login':
            login($db, $input);
            break;
        case 'logout':
            logout();
            break;
        case 'check_auth':
            checkAuth();
            break;
        default:
            respondWithError('Invalid action');
    }
} else {
    respondWithError('Method not allowed', 405);
}

function register($db, $data) {
    // Validate input
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $phone = sanitizeInput($data['phone'] ?? '');
    $address = sanitizeInput($data['address'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        respondWithError('Name, email, and password are required');
    }

    if (!validateEmail($email)) {
        respondWithError('Invalid email format');
    }

    if (strlen($password) < 6) {
        respondWithError('Password must be at least 6 characters long');
    }

    // Check if user already exists
    $existingUser = $db->fetch('SELECT id FROM users WHERE email = ?', [$email]);
    if ($existingUser) {
        respondWithError('User with this email already exists');
    }

    try {
        // Hash password and create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = $db->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => $phone,
            'address' => $address
        ]);

        // Auto login after registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        respondWithJSON([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address
            ]
        ]);
    } catch (Exception $e) {
        respondWithError('Registration failed: ' . $e->getMessage());
    }
}

function login($db, $data) {
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        respondWithError('Email and password are required');
    }

    // Find user
    $user = $db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    if (!$user) {
        respondWithError('Invalid email or password');
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        respondWithError('Invalid email or password');
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    respondWithJSON([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'address' => $user['address']
        ]
    ]);
}

function logout() {
    session_destroy();
    respondWithJSON(['success' => true, 'message' => 'Logout successful']);
}

function checkAuth() {
    if (isset($_SESSION['user_id'])) {
        $db = new Database();
        $user = $db->fetch('SELECT id, name, email, phone, address FROM users WHERE id = ?', [$_SESSION['user_id']]);
        if ($user) {
            respondWithJSON([
                'authenticated' => true,
                'user' => $user
            ]);
        }
    }
    
    respondWithJSON(['authenticated' => false]);
}
?>