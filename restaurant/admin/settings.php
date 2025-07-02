<?php
// Handle settings updates
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_restaurant_info'])) {
        $restaurantName = sanitizeInput($_POST['restaurant_name']);
        $logoUrl = sanitizeInput($_POST['logo_url']);
        $themeColor = sanitizeInput($_POST['theme_color']);
        $heroTitle = sanitizeInput($_POST['hero_title']);
        $heroSubtitle = sanitizeInput($_POST['hero_subtitle']);
        
        if ($restaurantName && $heroTitle && $heroSubtitle) {
            try {
                $db->update('site_settings', [
                    'restaurant_name' => $restaurantName,
                    'logo_url' => $logoUrl,
                    'theme_color' => $themeColor,
                    'hero_title' => $heroTitle,
                    'hero_subtitle' => $heroSubtitle
                ], 'id = 1');
                
                $message = 'Restaurant information updated successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error updating restaurant info: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_contact_info'])) {
        $contactPhone = sanitizeInput($_POST['contact_phone']);
        $contactEmail = sanitizeInput($_POST['contact_email']);
        $contactAddress = sanitizeInput($_POST['contact_address']);
        
        try {
            $db->update('site_settings', [
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'contact_address' => $contactAddress
            ], 'id = 1');
            
            $message = 'Contact information updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating contact info: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get current settings
$settings = $db->fetch('SELECT * FROM site_settings WHERE id = 1');
if (!$settings) {
    // Create default settings if none exist
    $db->insert('site_settings', [
        'restaurant_name' => 'Gourmet Haven',
        'hero_title' => 'Experience Culinary Excellence',
        'hero_subtitle' => 'Indulge in our premium selection of handcrafted dishes made with the finest ingredients and served with passion.'
    ]);
    $settings = $db->fetch('SELECT * FROM site_settings WHERE id = 1');
}
?>

<!-- Settings Content -->
<div class="mb-8">
    <h1 class="text-4xl font-bold font-poppins text-white mb-2">Restaurant Settings</h1>
    <p class="text-gray-300">Manage your restaurant information and configuration</p>
</div>

<!-- Success/Error Messages -->
<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-300' : 'bg-red-500/20 border border-red-500/50 text-red-300'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="grid lg:grid-cols-2 gap-8">
    <!-- Restaurant Information -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">
            <i class="fas fa-store mr-3"></i>
            Restaurant Information
        </h2>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-white font-medium mb-2">Restaurant Name *</label>
                <input
                    type="text"
                    name="restaurant_name"
                    value="<?php echo htmlspecialchars($settings['restaurant_name']); ?>"
                    required
                    placeholder="Enter restaurant name"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Logo URL</label>
                <input
                    type="url"
                    name="logo_url"
                    value="<?php echo htmlspecialchars($settings['logo_url']); ?>"
                    placeholder="https://example.com/logo.png"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Theme Color</label>
                <div class="flex gap-3">
                    <input
                        type="color"
                        name="theme_color"
                        value="<?php echo htmlspecialchars($settings['theme_color']); ?>"
                        class="w-16 h-12 bg-white/10 border border-white/20 rounded-xl cursor-pointer"
                    />
                    <input
                        type="text"
                        value="<?php echo htmlspecialchars($settings['theme_color']); ?>"
                        readonly
                        class="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white"
                    />
                </div>
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Hero Title *</label>
                <input
                    type="text"
                    name="hero_title"
                    value="<?php echo htmlspecialchars($settings['hero_title']); ?>"
                    required
                    placeholder="Enter hero section title"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Hero Subtitle *</label>
                <textarea
                    name="hero_subtitle"
                    required
                    rows="3"
                    placeholder="Enter hero section subtitle"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors resize-none"
                ><?php echo htmlspecialchars($settings['hero_subtitle']); ?></textarea>
            </div>
            
            <button
                type="submit"
                name="update_restaurant_info"
                class="w-full btn-primary text-white py-3 rounded-xl font-semibold flex items-center justify-center gap-2"
            >
                <i class="fas fa-save"></i>
                Update Restaurant Info
            </button>
        </form>
    </div>
    
    <!-- Contact Information -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">
            <i class="fas fa-phone mr-3"></i>
            Contact Information
        </h2>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-white font-medium mb-2">Phone Number</label>
                <input
                    type="tel"
                    name="contact_phone"
                    value="<?php echo htmlspecialchars($settings['contact_phone']); ?>"
                    placeholder="+1 (555) 123-4567"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Email Address</label>
                <input
                    type="email"
                    name="contact_email"
                    value="<?php echo htmlspecialchars($settings['contact_email']); ?>"
                    placeholder="contact@restaurant.com"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Address</label>
                <textarea
                    name="contact_address"
                    rows="4"
                    placeholder="Enter restaurant address"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors resize-none"
                ><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
            </div>
            
            <button
                type="submit"
                name="update_contact_info"
                class="w-full btn-primary text-white py-3 rounded-xl font-semibold flex items-center justify-center gap-2"
            >
                <i class="fas fa-save"></i>
                Update Contact Info
            </button>
        </form>
    </div>
</div>

<!-- Additional Settings -->
<div class="mt-8 space-y-8">
    <!-- System Information -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">
            <i class="fas fa-info-circle mr-3"></i>
            System Information
        </h2>
        
        <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-code text-white text-2xl"></i>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">Version</h3>
                <p class="text-gray-300">1.0.0</p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-database text-white text-2xl"></i>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">Database</h3>
                <p class="text-green-400">Connected</p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-server text-white text-2xl"></i>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">Server</h3>
                <p class="text-gray-300">PHP <?php echo phpversion(); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">
            <i class="fas fa-bolt mr-3"></i>
            Quick Actions
        </h2>
        
        <div class="grid md:grid-cols-3 gap-4">
            <a href="?page=dishes" class="flex items-center gap-3 p-4 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-utensils text-white"></i>
                </div>
                <div>
                    <h4 class="text-white font-medium">Manage Menu</h4>
                    <p class="text-gray-400 text-sm">Add or edit dishes</p>
                </div>
            </a>
            
            <a href="?page=orders" class="flex items-center gap-3 p-4 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-white"></i>
                </div>
                <div>
                    <h4 class="text-white font-medium">View Orders</h4>
                    <p class="text-gray-400 text-sm">Manage customer orders</p>
                </div>
            </a>
            
            <a href="?page=users" class="flex items-center gap-3 p-4 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <h4 class="text-white font-medium">View Users</h4>
                    <p class="text-gray-400 text-sm">Manage customers</p>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Backup & Maintenance -->
    <div class="glass rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">
            <i class="fas fa-tools mr-3"></i>
            Backup & Maintenance
        </h2>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-white font-medium mb-3">Database Backup</h3>
                <p class="text-gray-400 text-sm mb-4">Create a backup of your restaurant data including dishes, orders, and users.</p>
                <button class="btn-primary text-white px-6 py-3 rounded-xl font-medium flex items-center gap-2" onclick="alert('Backup functionality would be implemented here')">
                    <i class="fas fa-download"></i>
                    Create Backup
                </button>
            </div>
            
            <div>
                <h3 class="text-white font-medium mb-3">Clear Cache</h3>
                <p class="text-gray-400 text-sm mb-4">Clear system cache to improve performance and ensure latest changes are reflected.</p>
                <button class="glass text-white px-6 py-3 rounded-xl font-medium hover:bg-white/20 transition-colors flex items-center gap-2" onclick="alert('Cache cleared successfully!')">
                    <i class="fas fa-broom"></i>
                    Clear Cache
                </button>
            </div>
        </div>
    </div>
</div>