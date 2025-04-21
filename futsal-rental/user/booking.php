<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to book a field.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// Get selected field if passed in URL
$selected_field_id = $_GET['field_id'] ?? null;

try {
    $db = getDB();
    
    // Fetch all available fields
    $stmt = $db->query("SELECT * FROM fields WHERE status = 'available'");
    $fields = $stmt->fetchAll();

    // If field_id is provided, get that field's details
    $selected_field = null;
    if ($selected_field_id) {
        $stmt = $db->prepare("SELECT * FROM fields WHERE id = ? AND status = 'available'");
        $stmt->execute([$selected_field_id]);
        $selected_field = $stmt->fetch();
    }
} catch(PDOException $e) {
    error_log("Error fetching fields: " . $e->getMessage());
    $_SESSION['error'] = "Unable to fetch fields at the moment.";
    $fields = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field_id = $_POST['field_id'] ?? '';
    $booking_date = $_POST['booking_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    $errors = [];

    // Validation
    if (empty($field_id)) {
        $errors['field_id'] = 'Please select a field';
    }
    if (empty($booking_date)) {
        $errors['booking_date'] = 'Please select a date';
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $errors['booking_date'] = 'Cannot book for past dates';
    }
    if (empty($start_time)) {
        $errors['start_time'] = 'Please select start time';
    }
    if (empty($end_time)) {
        $errors['end_time'] = 'Please select end time';
    }
    if ($start_time >= $end_time) {
        $errors['time'] = 'End time must be after start time';
    }
    if (empty($payment_method)) {
        $errors['payment_method'] = 'Please select a payment method';
    }

    // Check if the slot is available
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE field_id = ? 
                AND booking_date = ?
                AND status != 'cancelled'
                AND (
                    (start_time <= ? AND end_time > ?) OR
                    (start_time < ? AND end_time >= ?) OR
                    (start_time >= ? AND end_time <= ?)
                )
            ");
            $stmt->execute([
                $field_id,
                $booking_date,
                $start_time,
                $start_time,
                $end_time,
                $end_time,
                $start_time,
                $end_time
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                $errors['slot'] = 'This time slot is already booked';
            } else {
                // Calculate total hours and price
                $start = strtotime($start_time);
                $end = strtotime($end_time);
                $hours = ($end - $start) / 3600;
                
                // Get field price
                $stmt = $db->prepare("SELECT price_per_hour FROM fields WHERE id = ?");
                $stmt->execute([$field_id]);
                $field_price = $stmt->fetchColumn();
                
                $total_price = $hours * $field_price;

                // Create booking
                $stmt = $db->prepare("
                    INSERT INTO bookings (user_id, field_id, booking_date, start_time, end_time, total_price, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $field_id,
                    $booking_date,
                    $start_time,
                    $end_time,
                    $total_price
                ]);
                
                $booking_id = $db->lastInsertId();

                // Create payment record
                $stmt = $db->prepare("
                    INSERT INTO payments (booking_id, amount, payment_method, payment_status)
                    VALUES (?, ?, ?, 'pending')
                ");
                $stmt->execute([
                    $booking_id,
                    $total_price,
                    $payment_method
                ]);

                $_SESSION['success'] = "Booking successful! Please proceed with the payment.";
                header("Location: " . BASE_URL . "/user/profile.php");
                exit();
            }
        } catch(PDOException $e) {
            error_log("Booking error: " . $e->getMessage());
            $_SESSION['error'] = "Booking failed. Please try again.";
        }
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-2xl font-bold text-white">Book a Futsal Field</h2>
        </div>

        <form method="POST" action="" class="p-6 space-y-6">
            <!-- Field Selection -->
            <div>
                <label for="field_id" class="block text-sm font-medium text-gray-700">Select Field</label>
                <select id="field_id" name="field_id" required
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Choose a field</option>
                    <?php foreach ($fields as $field): ?>
                        <option value="<?= $field['id'] ?>" 
                                <?= ($selected_field_id == $field['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($field['name']) ?> - Rp <?= number_format($field['price_per_hour'], 0, ',', '.') ?>/hour
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['field_id'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= $errors['field_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Date Selection -->
            <div>
                <label for="booking_date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" id="booking_date" name="booking_date" required
                       min="<?= date('Y-m-d') ?>"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       value="<?= $_POST['booking_date'] ?? '' ?>">
                <?php if (isset($errors['booking_date'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= $errors['booking_date'] ?></p>
                <?php endif; ?>
            </div>

            <!-- Time Selection -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="time" id="start_time" name="start_time" required
                           min="08:00" max="21:00"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           value="<?= $_POST['start_time'] ?? '' ?>">
                    <?php if (isset($errors['start_time'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= $errors['start_time'] ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" id="end_time" name="end_time" required
                           min="09:00" max="22:00"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           value="<?= $_POST['end_time'] ?? '' ?>">
                    <?php if (isset($errors['end_time'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= $errors['end_time'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (isset($errors['time'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= $errors['time'] ?></p>
            <?php endif; ?>

            <!-- Payment Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="radio" id="cod" name="payment_method" value="cod"
                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                               <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') ? 'checked' : '' ?>>
                        <label for="cod" class="ml-3 block text-sm font-medium text-gray-700">
                            Cash on Delivery (COD)
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" id="transfer" name="payment_method" value="transfer"
                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                               <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'transfer') ? 'checked' : '' ?>>
                        <label for="transfer" class="ml-3 block text-sm font-medium text-gray-700">
                            Bank Transfer
                        </label>
                    </div>
                </div>
                <?php if (isset($errors['payment_method'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= $errors['payment_method'] ?></p>
                <?php endif; ?>
            </div>

            <?php if (isset($errors['slot'])): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= $errors['slot'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Book Now
                </button>
            </div>
        </form>
    </div>

    <!-- Operating Hours Notice -->
    <div class="mt-6 bg-blue-50 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Operating Hours</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Our fields are available for booking from 08:00 AM to 10:00 PM daily.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
