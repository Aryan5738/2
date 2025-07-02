# 🍽️ Gourmet Haven - Modern Restaurant Website

A **premium, modern restaurant website** built with React.js (via CDN), Tailwind CSS, PHP backend, and MySQL database. Features a stunning glassmorphism design, full mobile responsiveness, and comprehensive admin panel.

## ✨ Features

### 🎨 **Premium UI/UX Design**
- 📱 **Fully mobile responsive** with premium glassmorphism effects
- 🎭 **Modern gradient backgrounds** with floating animations
- 🔮 **Glassmorphism cards and navigation** with blur effects
- 🎨 **Elegant fonts**: Poppins, Montserrat, Inter via Google Fonts
- 🌟 **Smooth animations and transitions** for enhanced user experience

### 💎 **User-Side Features**
- 🏠 **Home Page**: Premium hero banner with restaurant intro
- 🍽️ **Menu Page**: Dynamic dish display with categories, search, and filtering
- 🛒 **Shopping Cart**: Live cart with quantity controls and localStorage sync
- 📋 **Checkout System**: Address input, order confirmation, and cash-on-delivery
- 👤 **User Authentication**: Login/Register with secure password hashing
- 📊 **Profile Management**: User data, order history, and password updates
- 🧭 **Glassmorphism Bottom Navigation**: Home, Menu, Cart, Profile tabs

### 🛠️ **Admin Panel Features**
- 📈 **Dashboard**: Statistics, recent orders, and quick actions
- 🍴 **Dish Management**: Add, edit, delete, and toggle visibility
- 📦 **Order Management**: View, filter, and update order status
- 👥 **User Management**: View all users with statistics and search
- ⚙️ **Settings**: Restaurant info, contact details, and theme customization
- 🔐 **Secure admin authentication** with session management

### ⚡ **Technical Features**
- ⚛️ **React.js via CDN** for component-based UI
- 🎨 **Tailwind CSS** for utility-first styling
- 🐘 **PHP backend** with object-oriented database class
- 🗄️ **MySQL database** with optimized table structure
- 🔒 **Secure authentication** with password hashing
- 📱 **RESTful API** endpoints for all functionality
- 🔄 **Real-time updates** via AJAX calls

## 🏗️ Project Structure

```
restaurant/
├── index.html                 # Main frontend (React components)
├── components.html            # Additional React components
├── complete_components.js     # Profile & Checkout components
├── restaurant_database.sql   # Database schema with sample data
├── config/
│   └── database.php          # Database configuration & helper functions
├── api/
│   ├── auth.php             # Authentication endpoints
│   ├── dishes.php           # Menu management API
│   ├── cart.php             # Shopping cart API
│   ├── orders.php           # Order management API
│   └── profile.php          # User profile API
├── admin/
│   ├── index.php            # Admin login & main layout
│   ├── dashboard.php        # Admin dashboard
│   ├── dishes.php           # Dish management
│   ├── orders.php           # Order management
│   ├── users.php            # User management
│   └── settings.php         # Restaurant settings
└── README.md                # This file
```

## 🗄️ Database Schema

The application uses **7 main tables** for data management:

### **Core Tables:**
- `users` - Customer accounts and information
- `dishes` - Restaurant menu items with categories
- `cart` - Shopping cart items (linked to users)
- `orders` - Customer orders with delivery details
- `order_items` - Individual items within orders
- `admin` - Admin user accounts
- `site_settings` - Restaurant configuration

## 🚀 Installation & Setup

### **Prerequisites**
- 📦 PHP 7.4+ with PDO MySQL support
- 🗄️ MySQL 5.7+ or MariaDB 10.3+
- 🌐 Web server (Apache/Nginx)
- 🎯 Modern web browser with JavaScript enabled

### **Step 1: Database Setup**

1. **Create MySQL Database:**
   ```sql
   CREATE DATABASE restaurant_website;
   ```

2. **Import Database Schema:**
   ```bash
   mysql -u username -p restaurant_website < restaurant_database.sql
   ```

### **Step 2: Configuration**

1. **Update Database Credentials** in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'restaurant_website');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

2. **Set File Permissions:**
   ```bash
   chmod 755 -R /path/to/restaurant/
   ```

### **Step 3: Access the Application**

- 🌐 **Customer Site**: `http://yourserver.com/restaurant/index.html`
- 🛡️ **Admin Panel**: `http://yourserver.com/restaurant/admin/`

### **Default Login Credentials**

#### **Customer Account:**
- **Email**: `john@example.com`
- **Password**: `user123`

#### **Admin Account:**
- **Username**: `admin`
- **Password**: `admin123`

## 🎯 Usage Guide

### **For Customers:**

1. **Browse Menu**: Explore categorized dishes with search and filtering
2. **Add to Cart**: Select items and specify quantities
3. **Register/Login**: Create account or sign in for checkout
4. **Place Orders**: Enter delivery address and confirm order
5. **Track Orders**: View order history and status in profile

### **For Administrators:**

1. **Dashboard**: Monitor key metrics and recent activity
2. **Manage Menu**: Add, edit, or hide dishes with image support
3. **Process Orders**: Update order status from pending to delivered
4. **View Users**: Monitor customer registrations and activity
5. **Settings**: Customize restaurant information and branding

## 🔧 API Endpoints

### **Authentication API** (`/api/auth.php`)
- `POST` - Register new user
- `POST` - User login
- `POST` - User logout
- `POST` - Check authentication status

### **Dishes API** (`/api/dishes.php`)
- `GET` - Fetch all visible dishes
- `GET` - Fetch dish by ID with category filtering

### **Cart API** (`/api/cart.php`)
- `GET` - Get user's cart items
- `POST` - Add item to cart
- `POST` - Update item quantity
- `POST` - Remove item from cart
- `POST` - Clear entire cart

### **Orders API** (`/api/orders.php`)
- `GET` - Get user's order history
- `POST` - Place new order

### **Profile API** (`/api/profile.php`)
- `GET` - Get user profile with statistics
- `POST` - Update user profile information

## 🎨 Customization

### **Design Customization:**
- 🎨 **Colors**: Modify gradient variables in CSS
- 🖼️ **Images**: Update dish images via admin panel
- 📱 **Layout**: Adjust Tailwind classes for spacing/sizing
- 🔤 **Fonts**: Change Google Fonts imports in HTML head

### **Functionality Extensions:**
- 💳 **Payment Gateway**: Integrate Stripe/PayPal for online payments
- 📧 **Email Notifications**: Add order confirmation emails
- 📊 **Analytics**: Implement Google Analytics tracking
- 🔔 **Push Notifications**: Add real-time order updates

## 🔒 Security Features

- 🛡️ **Password Hashing**: Using PHP's `password_hash()` function
- 🔐 **SQL Injection Protection**: PDO prepared statements
- 🚫 **XSS Protection**: Input sanitization and validation
- 🎭 **Session Security**: Secure session management
- 🔍 **Input Validation**: Server-side and client-side validation

## 📱 Mobile Experience

- 📱 **Responsive Design**: Optimized for all screen sizes
- 👆 **Touch-Friendly**: Large buttons and easy navigation
- ⚡ **Fast Loading**: Optimized images and minimal dependencies
- 🧭 **Bottom Navigation**: Mobile-first navigation design

## 🎭 Design Philosophy

The website follows **premium design principles**:

- **Glassmorphism**: Modern translucent glass-like effects
- **Minimalism**: Clean, uncluttered interface design
- **Premium Typography**: Elegant font combinations
- **Smooth Interactions**: Fluid animations and transitions
- **Visual Hierarchy**: Clear content organization

## 🔧 Browser Support

- ✅ **Chrome**: 90+
- ✅ **Firefox**: 88+
- ✅ **Safari**: 14+
- ✅ **Edge**: 90+
- ✅ **Mobile Browsers**: iOS Safari, Chrome Mobile

## 📞 Support & Maintenance

### **Performance Optimization:**
- 🖼️ Optimize images for web delivery
- 🗄️ Index frequently queried database columns
- 🗂️ Archive old order data periodically
- 🔄 Enable gzip compression
- 📊 Monitor database query performance

### **Backup Strategy:**
- 🗄️ Regular database backups
- 📁 File system backups
- 🔄 Version control with Git
- ☁️ Cloud storage for redundancy

## 📄 License

This project is **proprietary software**. All rights reserved.

## 🤝 Contributing

For feature requests, bug reports, or customization needs, please contact the development team.

---

**🍽️ Gourmet Haven** - *Experience Culinary Excellence*

*Built with ❤️ using React.js, Tailwind CSS, PHP, and MySQL*