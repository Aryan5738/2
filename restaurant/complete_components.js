// Profile Page Component
function ProfilePage() {
    const { user, logout, setCurrentPage, showNotification } = useApp();
    const [activeTab, setActiveTab] = useState('profile');
    const [profileData, setProfileData] = useState({
        name: user?.name || '',
        phone: user?.phone || '',
        address: user?.address || '',
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
    });
    const [orders, setOrders] = useState([]);
    const [stats, setStats] = useState({});
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        loadProfileData();
        loadOrders();
    }, []);

    const loadProfileData = async () => {
        try {
            const response = await axios.get('/restaurant/api/profile.php');
            if (response.data.success) {
                setStats(response.data.stats);
            }
        } catch (error) {
            console.error('Failed to load profile data:', error);
        }
    };

    const loadOrders = async () => {
        try {
            const response = await axios.get('/restaurant/api/orders.php');
            if (response.data.success) {
                setOrders(response.data.orders);
            }
        } catch (error) {
            console.error('Failed to load orders:', error);
        }
    };

    const handleChange = (e) => {
        setProfileData({
            ...profileData,
            [e.target.name]: e.target.value
        });
        if (errors[e.target.name]) {
            setErrors({
                ...errors,
                [e.target.name]: ''
            });
        }
    };

    const validateForm = () => {
        const newErrors = {};
        
        if (!profileData.name) newErrors.name = 'Name is required';
        
        if (profileData.newPassword) {
            if (!profileData.currentPassword) {
                newErrors.currentPassword = 'Current password is required';
            }
            if (profileData.newPassword.length < 6) {
                newErrors.newPassword = 'Password must be at least 6 characters';
            }
            if (profileData.newPassword !== profileData.confirmPassword) {
                newErrors.confirmPassword = 'Passwords do not match';
            }
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleUpdateProfile = async (e) => {
        e.preventDefault();
        
        if (!validateForm()) return;

        setLoading(true);
        try {
            const response = await axios.post('/restaurant/api/profile.php', {
                name: profileData.name,
                phone: profileData.phone,
                address: profileData.address,
                current_password: profileData.currentPassword,
                new_password: profileData.newPassword
            });
            
            if (response.data.success) {
                showNotification('Profile updated successfully!', 'success');
                setProfileData({
                    ...profileData,
                    currentPassword: '',
                    newPassword: '',
                    confirmPassword: ''
                });
            }
        } catch (error) {
            showNotification(error.response?.data?.error || 'Failed to update profile', 'error');
        } finally {
            setLoading(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'pending': return 'text-yellow-400 bg-yellow-400/10';
            case 'confirmed': return 'text-blue-400 bg-blue-400/10';
            case 'preparing': return 'text-orange-400 bg-orange-400/10';
            case 'delivered': return 'text-green-400 bg-green-400/10';
            case 'cancelled': return 'text-red-400 bg-red-400/10';
            default: return 'text-gray-400 bg-gray-400/10';
        }
    };

    return (
        <div className="min-h-screen py-8">
            <div className="container mx-auto px-4 max-w-4xl">
                {/* Header */}
                <div className="text-center mb-8">
                    <h1 className="text-4xl md:text-5xl font-bold font-poppins text-white mb-4">
                        My <span className="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Profile</span>
                    </h1>
                    <p className="text-gray-200">Manage your account and view order history</p>
                </div>

                {/* Stats Cards */}
                <div className="grid md:grid-cols-3 gap-6 mb-8">
                    <div className="glass rounded-2xl p-6 text-center">
                        <div className="w-12 h-12 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i className="fas fa-shopping-bag text-white text-xl"></i>
                        </div>
                        <h3 className="text-2xl font-bold text-white mb-1">{stats.total_orders || 0}</h3>
                        <p className="text-gray-300">Total Orders</p>
                    </div>
                    <div className="glass rounded-2xl p-6 text-center">
                        <div className="w-12 h-12 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i className="fas fa-dollar-sign text-white text-xl"></i>
                        </div>
                        <h3 className="text-2xl font-bold text-white mb-1">${stats.total_spent || 0}</h3>
                        <p className="text-gray-300">Total Spent</p>
                    </div>
                    <div className="glass rounded-2xl p-6 text-center">
                        <div className="w-12 h-12 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i className="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <h3 className="text-2xl font-bold text-white mb-1">{stats.completed_orders || 0}</h3>
                        <p className="text-gray-300">Completed</p>
                    </div>
                </div>

                {/* Tab Navigation */}
                <div className="glass rounded-2xl p-2 mb-8">
                    <div className="flex gap-2">
                        {[
                            { id: 'profile', label: 'Profile Settings', icon: 'fas fa-user' },
                            { id: 'orders', label: 'Order History', icon: 'fas fa-clock' }
                        ].map(tab => (
                            <button
                                key={tab.id}
                                onClick={() => setActiveTab(tab.id)}
                                className={`flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-medium transition-all duration-300 ${
                                    activeTab === tab.id
                                        ? 'btn-primary text-white shadow-lg'
                                        : 'text-gray-300 hover:bg-white/10'
                                }`}
                            >
                                <i className={tab.icon}></i>
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Tab Content */}
                {activeTab === 'profile' ? (
                    /* Profile Settings */
                    <div className="glass rounded-2xl p-8">
                        <h2 className="text-2xl font-bold text-white mb-6 font-montserrat">Profile Settings</h2>
                        
                        <form onSubmit={handleUpdateProfile} className="space-y-6">
                            {/* Name */}
                            <div>
                                <label className="block text-white font-medium mb-2">Full Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    value={profileData.name}
                                    onChange={handleChange}
                                    className={`w-full px-4 py-3 bg-white/10 border rounded-xl text-white placeholder-gray-400 focus:outline-none transition-colors ${
                                        errors.name ? 'border-red-400 focus:border-red-400' : 'border-white/20 focus:border-amber-400'
                                    }`}
                                />
                                {errors.name && <p className="text-red-400 text-sm mt-1">{errors.name}</p>}
                            </div>

                            {/* Email (readonly) */}
                            <div>
                                <label className="block text-white font-medium mb-2">Email Address</label>
                                <input
                                    type="email"
                                    value={user?.email || ''}
                                    disabled
                                    className="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-gray-400 cursor-not-allowed"
                                />
                                <p className="text-gray-400 text-sm mt-1">Email cannot be changed</p>
                            </div>

                            {/* Phone */}
                            <div>
                                <label className="block text-white font-medium mb-2">Phone Number</label>
                                <input
                                    type="tel"
                                    name="phone"
                                    value={profileData.phone}
                                    onChange={handleChange}
                                    className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                                />
                            </div>

                            {/* Address */}
                            <div>
                                <label className="block text-white font-medium mb-2">Address</label>
                                <textarea
                                    name="address"
                                    value={profileData.address}
                                    onChange={handleChange}
                                    rows="3"
                                    className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors resize-none"
                                ></textarea>
                            </div>

                            {/* Password Change Section */}
                            <div className="border-t border-white/20 pt-6">
                                <h3 className="text-lg font-bold text-white mb-4">Change Password</h3>
                                
                                <div className="grid md:grid-cols-3 gap-4">
                                    <div>
                                        <label className="block text-white font-medium mb-2">Current Password</label>
                                        <input
                                            type="password"
                                            name="currentPassword"
                                            value={profileData.currentPassword}
                                            onChange={handleChange}
                                            placeholder="Enter current password"
                                            className={`w-full px-4 py-3 bg-white/10 border rounded-xl text-white placeholder-gray-400 focus:outline-none transition-colors ${
                                                errors.currentPassword ? 'border-red-400 focus:border-red-400' : 'border-white/20 focus:border-amber-400'
                                            }`}
                                        />
                                        {errors.currentPassword && <p className="text-red-400 text-sm mt-1">{errors.currentPassword}</p>}
                                    </div>
                                    
                                    <div>
                                        <label className="block text-white font-medium mb-2">New Password</label>
                                        <input
                                            type="password"
                                            name="newPassword"
                                            value={profileData.newPassword}
                                            onChange={handleChange}
                                            placeholder="Enter new password"
                                            className={`w-full px-4 py-3 bg-white/10 border rounded-xl text-white placeholder-gray-400 focus:outline-none transition-colors ${
                                                errors.newPassword ? 'border-red-400 focus:border-red-400' : 'border-white/20 focus:border-amber-400'
                                            }`}
                                        />
                                        {errors.newPassword && <p className="text-red-400 text-sm mt-1">{errors.newPassword}</p>}
                                    </div>
                                    
                                    <div>
                                        <label className="block text-white font-medium mb-2">Confirm Password</label>
                                        <input
                                            type="password"
                                            name="confirmPassword"
                                            value={profileData.confirmPassword}
                                            onChange={handleChange}
                                            placeholder="Confirm new password"
                                            className={`w-full px-4 py-3 bg-white/10 border rounded-xl text-white placeholder-gray-400 focus:outline-none transition-colors ${
                                                errors.confirmPassword ? 'border-red-400 focus:border-red-400' : 'border-white/20 focus:border-amber-400'
                                            }`}
                                        />
                                        {errors.confirmPassword && <p className="text-red-400 text-sm mt-1">{errors.confirmPassword}</p>}
                                    </div>
                                </div>
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={loading}
                                className="btn-primary text-white px-8 py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                {loading ? (
                                    <>
                                        <i className="fas fa-spinner fa-spin"></i>
                                        Updating...
                                    </>
                                ) : (
                                    <>
                                        <i className="fas fa-save"></i>
                                        Update Profile
                                    </>
                                )}
                            </button>
                        </form>
                    </div>
                ) : (
                    /* Order History */
                    <div className="space-y-4">
                        {orders.length === 0 ? (
                            <div className="glass rounded-2xl p-12 text-center">
                                <i className="fas fa-receipt text-6xl text-gray-400 mb-6"></i>
                                <h3 className="text-2xl font-bold text-white mb-4">No orders yet</h3>
                                <p className="text-gray-300 mb-8">You haven't placed any orders. Start exploring our menu!</p>
                                <button 
                                    onClick={() => setCurrentPage('menu')}
                                    className="btn-primary text-white px-8 py-3 rounded-xl font-semibold inline-flex items-center gap-2"
                                >
                                    <i className="fas fa-utensils"></i>
                                    Browse Menu
                                </button>
                            </div>
                        ) : (
                            orders.map(order => (
                                <div key={order.id} className="glass rounded-2xl p-6">
                                    <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                                        <div>
                                            <h3 className="text-xl font-bold text-white mb-2">Order #{order.id}</h3>
                                            <p className="text-gray-300 text-sm">
                                                {new Date(order.created_at).toLocaleDateString('en-US', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-4 mt-4 md:mt-0">
                                            <span className={`px-3 py-1 rounded-full text-sm font-medium capitalize ${getStatusColor(order.status)}`}>
                                                {order.status}
                                            </span>
                                            <span className="text-xl font-bold text-white">${order.total}</span>
                                        </div>
                                    </div>

                                    {/* Order Items */}
                                    <div className="grid gap-3">
                                        {order.items.map(item => (
                                            <div key={item.id} className="flex items-center gap-4 p-3 bg-white/5 rounded-xl">
                                                <div className="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg overflow-hidden flex-shrink-0">
                                                    {item.image ? (
                                                        <img src={item.image} alt={item.dish_name} className="w-full h-full object-cover" />
                                                    ) : (
                                                        <div className="w-full h-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                                                            <i className="fas fa-utensils text-white text-sm"></i>
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1">
                                                    <h4 className="text-white font-medium">{item.dish_name}</h4>
                                                    <p className="text-gray-400 text-sm">Qty: {item.quantity} × ${item.price}</p>
                                                </div>
                                                <span className="text-white font-bold">${(item.quantity * item.price).toFixed(2)}</span>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Delivery Address */}
                                    <div className="mt-4 pt-4 border-t border-white/10">
                                        <p className="text-gray-300 text-sm">
                                            <i className="fas fa-map-marker-alt mr-2"></i>
                                            Delivery to: {order.address}
                                        </p>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

// Checkout Page Component
function CheckoutPage() {
    const { cart, cartTotal, user, setCurrentPage, showNotification } = useApp();
    const [orderData, setOrderData] = useState({
        address: user?.address || '',
        specialInstructions: ''
    });
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const deliveryFee = 2.99;
    const tax = cartTotal * 0.08;
    const total = cartTotal + deliveryFee + tax;

    const handleChange = (e) => {
        setOrderData({
            ...orderData,
            [e.target.name]: e.target.value
        });
        if (errors[e.target.name]) {
            setErrors({
                ...errors,
                [e.target.name]: ''
            });
        }
    };

    const validateForm = () => {
        const newErrors = {};
        
        if (!orderData.address.trim()) {
            newErrors.address = 'Delivery address is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handlePlaceOrder = async (e) => {
        e.preventDefault();
        
        if (!validateForm()) return;

        setLoading(true);
        try {
            const response = await axios.post('/restaurant/api/orders.php', {
                address: orderData.address,
                special_instructions: orderData.specialInstructions
            });
            
            if (response.data.success) {
                showNotification('Order placed successfully!', 'success');
                setCurrentPage('profile');
            }
        } catch (error) {
            showNotification(error.response?.data?.error || 'Failed to place order', 'error');
        } finally {
            setLoading(false);
        }
    };

    if (cart.length === 0) {
        return (
            <div className="min-h-screen flex items-center justify-center py-12">
                <div className="container mx-auto px-4 max-w-md">
                    <div className="glass rounded-3xl p-8 text-center">
                        <i className="fas fa-shopping-cart text-6xl text-gray-400 mb-6"></i>
                        <h3 className="text-2xl font-bold text-white mb-4">Cart is empty</h3>
                        <p className="text-gray-300 mb-8">Add some items to your cart before checkout.</p>
                        <button 
                            onClick={() => setCurrentPage('menu')}
                            className="btn-primary text-white px-8 py-3 rounded-xl font-semibold inline-flex items-center gap-2"
                        >
                            <i className="fas fa-utensils"></i>
                            Browse Menu
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen py-8">
            <div className="container mx-auto px-4 max-w-4xl">
                {/* Header */}
                <div className="text-center mb-8">
                    <h1 className="text-4xl md:text-5xl font-bold font-poppins text-white mb-4">
                        <span className="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Checkout</span>
                    </h1>
                    <p className="text-gray-200">Review your order and complete your purchase</p>
                </div>

                <div className="grid lg:grid-cols-3 gap-8">
                    {/* Order Form */}
                    <div className="lg:col-span-2">
                        <form onSubmit={handlePlaceOrder} className="space-y-6">
                            {/* Delivery Information */}
                            <div className="glass rounded-2xl p-6">
                                <h2 className="text-xl font-bold text-white mb-6 font-montserrat">
                                    <i className="fas fa-map-marker-alt mr-3"></i>
                                    Delivery Information
                                </h2>
                                
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-white font-medium mb-2">Delivery Address *</label>
                                        <textarea
                                            name="address"
                                            value={orderData.address}
                                            onChange={handleChange}
                                            placeholder="Enter your complete delivery address"
                                            rows="3"
                                            className={`w-full px-4 py-3 bg-white/10 border rounded-xl text-white placeholder-gray-400 focus:outline-none transition-colors resize-none ${
                                                errors.address ? 'border-red-400 focus:border-red-400' : 'border-white/20 focus:border-amber-400'
                                            }`}
                                        ></textarea>
                                        {errors.address && <p className="text-red-400 text-sm mt-1">{errors.address}</p>}
                                    </div>
                                    
                                    <div>
                                        <label className="block text-white font-medium mb-2">Special Instructions (Optional)</label>
                                        <textarea
                                            name="specialInstructions"
                                            value={orderData.specialInstructions}
                                            onChange={handleChange}
                                            placeholder="Any special requests or delivery instructions..."
                                            rows="2"
                                            className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            {/* Payment Method */}
                            <div className="glass rounded-2xl p-6">
                                <h2 className="text-xl font-bold text-white mb-6 font-montserrat">
                                    <i className="fas fa-credit-card mr-3"></i>
                                    Payment Method
                                </h2>
                                
                                <div className="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                                    <div className="flex items-center gap-3">
                                        <i className="fas fa-money-bill-wave text-amber-400 text-xl"></i>
                                        <div>
                                            <h3 className="text-white font-medium">Cash on Delivery</h3>
                                            <p className="text-amber-200 text-sm">Pay when your order arrives</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {/* Order Summary */}
                    <div className="lg:col-span-1">
                        <div className="glass rounded-2xl p-6 sticky top-24">
                            <h3 className="text-xl font-bold text-white mb-6 font-montserrat">Order Summary</h3>
                            
                            {/* Items */}
                            <div className="space-y-3 mb-6">
                                {cart.map(item => (
                                    <div key={item.dish_id} className="flex items-center gap-3">
                                        <div className="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg overflow-hidden flex-shrink-0">
                                            {item.image ? (
                                                <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                                            ) : (
                                                <div className="w-full h-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                                                    <i className="fas fa-utensils text-white text-sm"></i>
                                                </div>
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <h4 className="text-white font-medium text-sm">{item.name}</h4>
                                            <p className="text-gray-400 text-xs">Qty: {item.quantity}</p>
                                        </div>
                                        <span className="text-white font-bold text-sm">${(item.price * item.quantity).toFixed(2)}</span>
                                    </div>
                                ))}
                            </div>
                            
                            {/* Totals */}
                            <div className="space-y-3 mb-6">
                                <div className="flex justify-between text-gray-300">
                                    <span>Subtotal</span>
                                    <span>${cartTotal.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between text-gray-300">
                                    <span>Delivery Fee</span>
                                    <span>${deliveryFee.toFixed(2)}</span>
                                </div>
                                <div className="flex justify-between text-gray-300">
                                    <span>Tax</span>
                                    <span>${tax.toFixed(2)}</span>
                                </div>
                                <div className="border-t border-white/20 pt-3">
                                    <div className="flex justify-between text-white font-bold text-lg">
                                        <span>Total</span>
                                        <span>${total.toFixed(2)}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Action Buttons */}
                            <button 
                                onClick={handlePlaceOrder}
                                disabled={loading}
                                className="w-full btn-primary text-white py-3 rounded-xl font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mb-3"
                            >
                                {loading ? (
                                    <>
                                        <i className="fas fa-spinner fa-spin"></i>
                                        Placing Order...
                                    </>
                                ) : (
                                    <>
                                        <i className="fas fa-check"></i>
                                        Place Order
                                    </>
                                )}
                            </button>

                            <button 
                                onClick={() => setCurrentPage('cart')}
                                className="w-full glass text-white py-3 rounded-xl font-medium hover:bg-white/20 transition-colors"
                            >
                                <i className="fas fa-arrow-left mr-2"></i>
                                Back to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}