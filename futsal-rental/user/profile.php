<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your profile.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

try {
    $db = getDB();
    
    // Fetch user details
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Fetch user's bookings with field details and payment status
    $stmt = $db->prepare("
        SELECT 
            b.*, 
            f.name as field_name, 
            f.price_per_hour,
            p.payment_method,
            p.payment_status,
            p.payment_proof
        FROM bookings b
        JOIN fields f ON b.field_id = f.id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC, b.start_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    $_SESSION['error'] = "Unable to fetch your profile data.";
}

// Handle payment proof upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_payment'])) {
    $booking_id = $_POST['booking_id'] ?? '';
    $payment_proof = $_FILES['payment_proof'] ?? null;

    if ($payment_proof && $payment_proof['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($payment_proof['type'], $allowed_types)) {
            $_SESSION['error'] = "Only JPG, JPEG & PNG files are allowed.";
        } elseif ($payment_proof['size'] > $max_size) {
            $_SESSION['error'] = "File size must be less than 5MB.";
        } else {
            $file_name = 'payment_' . time() . '_' . $booking_id . '.' . pathinfo($payment_proof['name'], PATHINFO_EXTENSION);
            $upload_path = '../assets/uploads/payments/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            if (move_uploaded_file($payment_proof['tmp_name'], $upload_path . $file_name)) {
                try {
                    $stmt = $db->prepare("UPDATE payments SET payment_proof = ? WHERE booking_id = ?");
                    $stmt->execute([$file_name, $booking_id]);
                    $_SESSION['success'] = "Payment proof uploaded successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } catch(PDOException $e) {
                    error_log("Payment proof upload error: " . $e->getMessage());
                    $_SESSION['error'] = "Failed to update payment proof.";
                }
            } else {
                $_SESSION['error'] = "Failed to upload file.";
            }
        }
    } else {
        $_SESSION['error'] = "Please select a file to upload.";
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- User Profile Section -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-2xl font-bold text-white">My Profile</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                    <div class="space-y-3">
                        <p><span class="font-medium">Name:</span> <?= htmlspecialchars($user['name']) ?></p>
                        <p><span class="font-medium">Email:</span> <?= htmlspecialchars($user['email']) ?></p>
                        <p><span class="font-medium">Phone:</span> <?= htmlspecialchars($user['phone']) ?></p>
                        <p><span class="font-medium">Address:</span> <?= htmlspecialchars($user['address']) ?></p>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Summary</h3>
                    <div class="space-y-3">
                        <p><span class="font-medium">Member Since:</span> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                        <p><span class="font-medium">Total Bookings:</span> <?= count($bookings) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Section -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-2xl font-bold text-white">My Bookings</h2>
        </div>
        <div class="p-6">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-600">You haven't made any bookings yet.</p>
                    <a href="<?= BASE_URL ?>/user/booking.php" 
                       class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                        Book Now
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Booking Details
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Payment Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($booking['field_name']) ?></p>
                                            <p class="text-gray-600">
                                                Date: <?= date('F j, Y', strtotime($booking['booking_date'])) ?>
                                            </p>
                                            <p class="text-gray-600">
                                                Time: <?= date('g:i A', strtotime($booking['start_time'])) ?> - 
                                                      <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                            </p>
                                            <p class="text-gray-600">
                                                Total: Rp <?= number_format($booking['total_price'], 0, ',', '.') ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p>
                                                <span class="font-medium">Method:</span> 
                                                <?= ucfirst($booking['payment_method']) ?>
                                            </p>
                                            <p>
                                                <span class="font-medium">Status:</span>
                                                <?php
                                                $statusClass = match($booking['payment_status']) {
                                                    'confirmed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-yellow-100 text-yellow-800'
                                                };
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                                    <?= ucfirst($booking['payment_status']) ?>
                                                </span>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($booking['payment_method'] === 'transfer' && $booking['payment_status'] === 'pending'): ?>
                                            <?php if (empty($booking['payment_proof'])): ?>
                                                <button onclick="document.getElementById('uploadForm<?= $booking['id'] ?>').classList.toggle('hidden')"
                                                        class="text-blue-600 hover:text-blue-800">
                                                    Upload Payment Proof
                                                </button>
                                                <form id="uploadForm<?= $booking['id'] ?>" 
                                                      method="POST" 
                                                      enctype="multipart/form-data"
                                                      class="hidden mt-2">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    <input type="file" name="payment_proof" 
                                                           accept="image/jpeg,image/png,image/jpg"
                                                           class="block w-full text-sm text-gray-500 mb-2
                                                                  file:mr-4 file:py-2 file:px-4
                                                                  file:rounded-full file:border-0
                                                                  file:text-sm file:font-semibold
                                                                  file:bg-blue-50 file:text-blue-700
                                                                  hover:file:bg-blue-100">
                                                    <button type="submit" 
                                                            name="upload_payment"
                                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                                                        Upload
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-green-600">
                                                    <i class="fas fa-check-circle"></i> Proof Uploaded
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
