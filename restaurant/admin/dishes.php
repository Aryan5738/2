<?php
// Handle dish operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_dish'])) {
        // Add new dish
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = floatval($_POST['price']);
        $category = sanitizeInput($_POST['category']);
        $image = sanitizeInput($_POST['image'] ?? '');
        
        if ($name && $description && $price > 0) {
            try {
                $db->insert('dishes', [
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'category' => $category,
                    'image' => $image,
                    'visible' => true
                ]);
                $message = 'Dish added successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error adding dish: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill in all required fields';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_dish'])) {
        // Update dish
        $id = intval($_POST['dish_id']);
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = floatval($_POST['price']);
        $category = sanitizeInput($_POST['category']);
        $image = sanitizeInput($_POST['image'] ?? '');
        
        if ($id && $name && $description && $price > 0) {
            try {
                $db->update('dishes', [
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'category' => $category,
                    'image' => $image
                ], 'id = ?', [$id]);
                $message = 'Dish updated successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error updating dish: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
    
    if (isset($_POST['toggle_visibility'])) {
        // Toggle dish visibility
        $id = intval($_POST['dish_id']);
        $visible = isset($_POST['visible']) ? 1 : 0;
        
        try {
            $db->update('dishes', ['visible' => $visible], 'id = ?', [$id]);
            $message = 'Dish visibility updated!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating visibility: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['delete_dish'])) {
        // Delete dish
        $id = intval($_POST['dish_id']);
        
        try {
            $db->delete('dishes', 'id = ?', [$id]);
            $message = 'Dish deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting dish: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all dishes
$dishes = $db->fetchAll('SELECT * FROM dishes ORDER BY category, name');

// Get dish for editing if specified
$editDish = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editDish = $db->fetch('SELECT * FROM dishes WHERE id = ?', [$editId]);
}
?>

<!-- Dishes Management Content -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold font-poppins text-white mb-2">Manage Dishes</h1>
            <p class="text-gray-300">Add, edit, and organize your menu items</p>
        </div>
        <button onclick="toggleAddForm()" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Add New Dish
        </button>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-500/20 border border-green-500/50 text-green-300' : 'bg-red-500/20 border border-red-500/50 text-red-300'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Dish Form -->
<div id="dishForm" class="<?php echo $editDish ? 'block' : 'hidden'; ?> mb-8">
    <div class="glass rounded-2xl p-6">
        <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">
            <?php echo $editDish ? 'Edit Dish' : 'Add New Dish'; ?>
        </h2>
        
        <form method="POST" class="grid md:grid-cols-2 gap-6">
            <?php if ($editDish): ?>
                <input type="hidden" name="dish_id" value="<?php echo $editDish['id']; ?>">
            <?php endif; ?>
            
            <div>
                <label class="block text-white font-medium mb-2">Dish Name *</label>
                <input
                    type="text"
                    name="name"
                    value="<?php echo $editDish['name'] ?? ''; ?>"
                    required
                    placeholder="Enter dish name"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Category *</label>
                <select
                    name="category"
                    required
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:border-amber-400 transition-colors"
                >
                    <option value="appetizer" <?php echo ($editDish['category'] ?? '') === 'appetizer' ? 'selected' : ''; ?>>Appetizer</option>
                    <option value="main" <?php echo ($editDish['category'] ?? '') === 'main' ? 'selected' : ''; ?>>Main Course</option>
                    <option value="dessert" <?php echo ($editDish['category'] ?? '') === 'dessert' ? 'selected' : ''; ?>>Dessert</option>
                </select>
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Price ($) *</label>
                <input
                    type="number"
                    name="price"
                    value="<?php echo $editDish['price'] ?? ''; ?>"
                    step="0.01"
                    min="0"
                    required
                    placeholder="0.00"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div>
                <label class="block text-white font-medium mb-2">Image URL</label>
                <input
                    type="url"
                    name="image"
                    value="<?php echo $editDish['image'] ?? ''; ?>"
                    placeholder="https://example.com/image.jpg"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors"
                />
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-white font-medium mb-2">Description *</label>
                <textarea
                    name="description"
                    required
                    rows="3"
                    placeholder="Enter dish description"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-amber-400 transition-colors resize-none"
                ><?php echo $editDish['description'] ?? ''; ?></textarea>
            </div>
            
            <div class="md:col-span-2 flex gap-4">
                <button
                    type="submit"
                    name="<?php echo $editDish ? 'update_dish' : 'add_dish'; ?>"
                    class="btn-primary text-white px-8 py-3 rounded-xl font-semibold flex items-center gap-2"
                >
                    <i class="fas fa-save"></i>
                    <?php echo $editDish ? 'Update Dish' : 'Add Dish'; ?>
                </button>
                <button
                    type="button"
                    onclick="cancelEdit()"
                    class="glass text-white px-6 py-3 rounded-xl font-medium hover:bg-white/20 transition-colors"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dishes List -->
<div class="glass rounded-2xl p-6">
    <h2 class="text-2xl font-bold text-white mb-6 font-montserrat">All Dishes (<?php echo count($dishes); ?>)</h2>
    
    <?php if (empty($dishes)): ?>
        <div class="text-center py-12">
            <i class="fas fa-utensils text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">No dishes found</h3>
            <p class="text-gray-400 mb-6">Start by adding your first dish to the menu.</p>
            <button onclick="toggleAddForm()" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold">
                Add Your First Dish
            </button>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($dishes as $dish): ?>
                <div class="bg-white/5 rounded-xl p-4 hover:bg-white/10 transition-colors">
                    <!-- Dish Image -->
                    <div class="h-40 bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg mb-4 overflow-hidden">
                        <?php if ($dish['image']): ?>
                            <img src="<?php echo $dish['image']; ?>" alt="<?php echo $dish['name']; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                                <i class="fas fa-utensils text-white text-3xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Dish Info -->
                    <div class="mb-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-white font-bold text-lg"><?php echo $dish['name']; ?></h3>
                            <span class="text-amber-400 font-bold text-lg">$<?php echo number_format($dish['price'], 2); ?></span>
                        </div>
                        <p class="text-gray-300 text-sm mb-2 line-clamp-2"><?php echo $dish['description']; ?></p>
                        <span class="inline-block bg-blue-500/20 text-blue-400 px-2 py-1 rounded-full text-xs font-medium capitalize">
                            <?php echo $dish['category']; ?>
                        </span>
                    </div>
                    
                    <!-- Visibility Toggle -->
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="visible"
                                value="1"
                                <?php echo $dish['visible'] ? 'checked' : ''; ?>
                                onchange="this.form.submit()"
                                class="sr-only"
                            >
                            <div class="relative">
                                <div class="w-10 h-6 bg-gray-600 rounded-full transition-colors <?php echo $dish['visible'] ? 'bg-green-500' : ''; ?>"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform <?php echo $dish['visible'] ? 'transform translate-x-4' : ''; ?>"></div>
                            </div>
                            <span class="ml-3 text-white text-sm">
                                <?php echo $dish['visible'] ? 'Visible' : 'Hidden'; ?>
                            </span>
                        </label>
                        <input type="hidden" name="toggle_visibility" value="1">
                    </form>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="?page=dishes&edit=<?php echo $dish['id']; ?>" class="flex-1 text-center bg-blue-500/20 text-blue-400 px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-500/30 transition-colors">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>
                        <form method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this dish?')">
                            <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                            <button type="submit" name="delete_dish" class="w-full bg-red-500/20 text-red-400 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-500/30 transition-colors">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleAddForm() {
    const form = document.getElementById('dishForm');
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.classList.add('hidden');
    }
}

function cancelEdit() {
    window.location.href = '?page=dishes';
}

// Auto-show form if editing
<?php if ($editDish): ?>
document.getElementById('dishForm').classList.remove('hidden');
<?php endif; ?>
</script>