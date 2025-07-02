-- Restaurant Website Database Schema
-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS restaurant_website;
CREATE DATABASE restaurant_website;
USE restaurant_website;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dishes table
CREATE TABLE dishes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    visible BOOLEAN DEFAULT TRUE,
    category VARCHAR(50) DEFAULT 'main',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_dish (user_id, dish_id)
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    dish_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
);

-- Admin table
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_name VARCHAR(100) DEFAULT 'Premium Restaurant',
    logo_url VARCHAR(255) DEFAULT '/restaurant/assets/images/logo.png',
    theme_color VARCHAR(7) DEFAULT '#f59e0b',
    hero_title VARCHAR(200) DEFAULT 'Welcome to Premium Dining',
    hero_subtitle VARCHAR(300) DEFAULT 'Experience culinary excellence with our handcrafted dishes made from the finest ingredients.',
    contact_phone VARCHAR(20) DEFAULT '+1 (555) 123-4567',
    contact_email VARCHAR(100) DEFAULT 'contact@premiumrestaurant.com',
    contact_address TEXT DEFAULT '123 Gourmet Street, Food District, City 12345'
);

-- Insert default admin user (password: admin123)
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default site settings
INSERT INTO site_settings (restaurant_name, hero_title, hero_subtitle) VALUES 
('Gourmet Haven', 'Experience Culinary Excellence', 'Indulge in our premium selection of handcrafted dishes made with the finest ingredients and served with passion.');

-- Insert sample dishes
INSERT INTO dishes (name, description, price, image, category, visible) VALUES 
('Truffle Risotto', 'Creamy arborio rice cooked with black truffle, parmesan, and white wine', 28.99, '/restaurant/assets/images/dishes/truffle-risotto.jpg', 'main', TRUE),
('Grilled Salmon', 'Fresh Atlantic salmon grilled to perfection with lemon herbs', 24.99, '/restaurant/assets/images/dishes/grilled-salmon.jpg', 'main', TRUE),
('Beef Wellington', 'Premium beef tenderloin wrapped in puff pastry with mushroom duxelles', 45.99, '/restaurant/assets/images/dishes/beef-wellington.jpg', 'main', TRUE),
('Caesar Salad', 'Crisp romaine lettuce with house-made caesar dressing and croutons', 14.99, '/restaurant/assets/images/dishes/caesar-salad.jpg', 'appetizer', TRUE),
('Chocolate Soufflé', 'Warm chocolate soufflé served with vanilla ice cream', 12.99, '/restaurant/assets/images/dishes/chocolate-souffle.jpg', 'dessert', TRUE),
('Lobster Bisque', 'Rich and creamy lobster soup with cognac and herbs', 18.99, '/restaurant/assets/images/dishes/lobster-bisque.jpg', 'appetizer', TRUE),
('Pasta Carbonara', 'Traditional Italian pasta with pancetta, eggs, and parmesan', 19.99, '/restaurant/assets/images/dishes/pasta-carbonara.jpg', 'main', TRUE),
('Crème Brûlée', 'Classic French vanilla custard with caramelized sugar top', 10.99, '/restaurant/assets/images/dishes/creme-brulee.jpg', 'dessert', TRUE);

-- Insert a test user (password: user123)
INSERT INTO users (name, email, password, phone, address) VALUES 
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-123-4567', '456 Test Street, User City, State 12345');