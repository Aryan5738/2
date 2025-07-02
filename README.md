# 91CLUB - Multi-Game Prediction Platform

A comprehensive web-based gaming platform featuring color prediction, minesweeper, dice roll, coin flip, and number betting games with real money transactions.

## 🎯 Features

### User Features
- **Auto-generated UID system** (91CLUB + 5 digits)
- **Multiple Games:**
  - Color Prediction (Red/Green: 1.5x, Violet: 5x)
  - Minesweeper
  - Dice Roll
  - Coin Flip
  - Number Betting
- **Real-time gameplay** with 60-second rounds
- **Secure deposit/withdrawal** system
- **Live notifications** and game history
- **Responsive design** (mobile-friendly)

### Admin Features
- **Comprehensive dashboard** with statistics
- **User management** system
- **Deposit/withdrawal approval** workflow
- **Round result management**
- **Global notifications**
- **Detailed reporting**

### Technical Features
- **AJAX-powered** real-time updates
- **Auto-round management** via cron
- **Secure authentication** and sessions
- **Database transactions** for consistency
- **Modern UI** with Bootstrap + Tailwind CSS

## 🛠️ Installation & Setup

### Prerequisites
- PHP 7.4+ with MySQL/PDO support
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- cPanel or terminal access

### Step 1: Database Setup

1. Create a new MySQL database named `prediction_platform`
2. Import the database schema:
   ```bash
   mysql -u username -p prediction_platform < database.sql
   ```

### Step 2: Configuration

1. Update database credentials in `config/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'prediction_platform');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

2. Set proper file permissions:
   ```bash
   chmod 755 -R /path/to/your/project
   chmod 777 logs/ (create this directory)
   ```

### Step 3: Admin Account Setup

1. Generate a secure password hash:
   ```php
   echo password_hash('your_admin_password', PASSWORD_DEFAULT);
   ```

2. Update the admin password in `database.sql` or directly in database:
   ```sql
   UPDATE admin_users SET password = 'your_hashed_password' WHERE username = 'admin';
   ```

### Step 4: Cron Job Setup

Add the following to your crontab to auto-manage rounds:
```bash
* * * * * /usr/bin/php /path/to/your/project/cron/auto_round.php >/dev/null 2>&1
```

For cPanel, add this in Cron Jobs section:
- Command: `/usr/bin/php /home/username/public_html/cron/auto_round.php`
- Interval: Every minute

### Step 5: Payment Gateway Integration

Update payment details in:
- `user/deposit.php` (UPI ID, Bank details)
- Admin panel for withdrawal processing

## 📁 Directory Structure

```
/
├── api/                 # AJAX endpoints
│   ├── place_bet.php
│   ├── get_countdown.php
│   └── ...
├── admin/              # Admin panel
│   ├── dashboard.php
│   ├── login.php
│   └── ...
├── user/               # User panel
│   ├── dashboard.php
│   ├── login.php
│   ├── register.php
│   └── ...
├── assets/             # CSS, JS, images
│   ├── css/style.css
│   ├── js/dashboard.js
│   └── ...
├── config/             # Configuration files
│   ├── db.php
│   └── functions.php
├── cron/               # Cron scripts
│   └── auto_round.php
├── logs/               # Log files
├── vendor/             # Third-party libraries
├── index.php           # Landing page
├── database.sql        # Database schema
└── README.md
```

## 🔧 Configuration Options

### Game Settings
- **Round Duration:** 60 seconds (configurable in `functions.php`)
- **Betting Limits:** ₹10 minimum, ₹50,000 maximum
- **Payout Rates:**
  - Red/Green: 1.5x
  - Violet: 5x
  - Numbers: 10x

### Payment Settings
- **Deposit:** ₹100 minimum, ₹1,00,000 maximum
- **Withdrawal:** ₹500 minimum
- **Processing Time:** 24-48 hours

## 🚀 Deployment

### For cPanel Hosting:
1. Upload all files to `public_html` directory
2. Create database and import `database.sql`
3. Update `config/db.php` with your database credentials
4. Set up cron job for auto-rounds
5. Test the installation

### For VPS/Dedicated Server:
1. Configure web server (Apache/Nginx)
2. Set up PHP and MySQL
3. Upload files and configure database
4. Set proper file permissions
5. Configure cron jobs
6. Set up SSL certificate

## 🔒 Security Features

- **Password hashing** with PHP's password_hash()
- **SQL injection protection** with prepared statements
- **XSS protection** with input sanitization
- **CSRF protection** for forms
- **Session security** with secure headers
- **Admin access logging**

## 📊 Database Tables

### Core Tables:
- `users` - User accounts and balances
- `rounds` - Game rounds and results
- `predictions` - User bets and outcomes
- `deposits` - Deposit requests and approvals
- `withdrawals` - Withdrawal requests and processing
- `notifications` - User notifications
- `admin_users` - Admin accounts

## 🎮 Game Logic

### Color Prediction:
- 60-second rounds with automatic results
- Red/Green numbers: 1,2,3,4,6,7,8,9
- Violet numbers: 0,5
- Auto-calculated size: 0-4 (Small), 6-9 (Big)

### Payout System:
- Automatic balance updates on wins
- Transaction logging for all money movements
- Manual admin approval for deposits/withdrawals

## 🔍 Troubleshooting

### Common Issues:

1. **Database Connection Error:**
   - Check credentials in `config/db.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Cron Not Working:**
   - Check PHP path: `which php`
   - Verify file permissions
   - Check cron logs: `/var/log/cron`

3. **AJAX Errors:**
   - Check browser console for JavaScript errors
   - Verify API endpoints are accessible
   - Check PHP error logs

4. **Session Issues:**
   - Ensure session directory is writable
   - Check session configuration in PHP

## 📞 Support

For technical support or customization:
- Check logs in `/logs/` directory
- Review PHP error logs
- Test API endpoints individually
- Verify database structure matches schema

## ⚖️ Legal Notice

- Ensure compliance with local gambling laws
- Implement responsible gaming features
- Add proper terms of service and privacy policy
- Consider age verification systems
- Implement KYC procedures for large transactions

## 🔄 Updates & Maintenance

### Regular Tasks:
- Monitor cron job execution
- Clean up old game data
- Review user activity logs
- Update security patches
- Backup database regularly

### Performance Optimization:
- Index frequently queried columns
- Archive old rounds and predictions
- Optimize images and assets
- Use CDN for static files
- Enable gzip compression

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**License:** Proprietary  

For any questions or support, please contact the development team.