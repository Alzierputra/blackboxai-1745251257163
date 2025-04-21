<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Fetch available fields
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM fields WHERE status = 'available'");
    $fields = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching fields: " . $e->getMessage());
    $_SESSION['error'] = "Unable to fetch fields at the moment.";
    $fields = [];
}
?>

<!-- Hero Section -->
<div class="relative bg-blue-600 h-[500px] -mt-6">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="https://images.pexels.com/photos/1277126/pexels-photo-1277126.jpeg" 
             alt="Futsal Field" 
             class="w-full h-full object-cover"
             style="filter: brightness(0.6);">
    </div>
    
    <!-- Hero Content -->
    <div class="relative max-w-7xl mx-auto px-4 py-24 sm:py-32">
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
            Book Your Perfect <br>Futsal Field
        </h1>
        <p class="text-xl text-white mb-8 max-w-xl">
            Experience premium futsal fields with top-notch facilities. 
            Book your slot today and enjoy the game with your team!
        </p>
        <?php if (!$isLoggedIn): ?>
        <div class="space-x-4">
            <a href="<?= BASE_URL ?>/register.php" 
               class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300">
                Register Now
            </a>
            <a href="<?= BASE_URL ?>/login.php" 
               class="inline-block bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                Login
            </a>
        </div>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/user/booking.php" 
           class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300">
            Book Now
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Why Choose Our Futsal Fields?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="text-center p-6">
                <div class="text-4xl text-blue-600 mb-4">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Premium Quality</h3>
                <p class="text-gray-600">High-quality synthetic grass and professional equipment for the best playing experience.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="text-center p-6">
                <div class="text-4xl text-blue-600 mb-4">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Flexible Hours</h3>
                <p class="text-gray-600">Book your preferred time slot that suits your schedule best.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="text-center p-6">
                <div class="text-4xl text-blue-600 mb-4">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Safe & Clean</h3>
                <p class="text-gray-600">Regularly maintained facilities ensuring safety and cleanliness.</p>
            </div>
        </div>
    </div>
</div>

<!-- Available Fields Section -->
<div class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Our Available Fields</h2>
        
        <?php if (!empty($fields)): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($fields as $field): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <img src="<?= htmlspecialchars($field['image']) ?>" 
                     alt="<?= htmlspecialchars($field['name']) ?>" 
                     class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($field['name']) ?></h3>
                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($field['description']) ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-blue-600 font-semibold">
                            Rp <?= number_format($field['price_per_hour'], 0, ',', '.') ?>/hour
                        </span>
                        <?php if ($isLoggedIn): ?>
                        <a href="<?= BASE_URL ?>/user/booking.php?field_id=<?= $field['id'] ?>" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                            Book Now
                        </a>
                        <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                            Login to Book
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center text-gray-600">
            <p>No fields available at the moment. Please check back later.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Contact Section -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Contact Us</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Contact Information -->
            <div class="bg-gray-50 p-8 rounded-lg">
                <h3 class="text-xl font-semibold mb-6">Get in Touch</h3>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i class="fas fa-phone text-blue-600 w-8"></i>
                        <span>+1234567890</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-blue-600 w-8"></i>
                        <span>info@futsalrental.com</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-blue-600 w-8"></i>
                        <span>123 Futsal Street, City</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock text-blue-600 w-8"></i>
                        <span>Open daily: 08:00 - 22:00</span>
                    </div>
                </div>
            </div>
            
            <!-- Google Maps (Placeholder) -->
            <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                <p class="text-gray-600">Google Maps will be integrated here</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
