<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        if (isset($_POST['confirm_payment'])) {
            // Start transaction
            $db->beginTransaction();

            // Update payment status
            $stmt = $db->prepare("UPDATE payments SET payment_status = 'confirmed' WHERE booking_id = ?");
            $stmt->execute([$_POST['booking_id']]);

            // Update booking status
            $stmt = $db->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$_POST['booking_id']]);

            $db->commit();
            $_SESSION['success'] = "Payment confirmed successfully.";
        }
        elseif (isset($_POST['reject_payment'])) {
            // Start transaction
            $db->beginTransaction();

            // Update payment status
            $stmt = $db->prepare("UPDATE payments SET payment_status = 'cancelled' WHERE booking_id = ?");
            $stmt->execute([$_POST['booking_id']]);

            // Update booking status
            $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$_POST['booking_id']]);

            $db->commit();
            $_SESSION['success'] = "Payment rejected successfully.";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        error_log("Payment confirmation error: " . $e->getMessage());
        $_SESSION['error'] = "Operation failed. Please try again.";
    }
}

// Fetch pending payments
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT 
            p.*,
            b.booking_date,
            b.start_time,
            b.end_time,
            b.total_price,
            u.name as user_name,
            u.phone as user_phone,
            f.name as field_name
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        JOIN users u ON b.user_id = u.id
        JOIN fields f ON b.field_id = f.id
        WHERE p.payment_status = 'pending'
        ORDER BY b.booking_date ASC, b.start_time ASC
    ");
    $pending_payments = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching pending payments: " . $e->getMessage());
    $_SESSION['error'] = "Unable to fetch pending payments.";
    $pending_payments = [];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-xl font-bold text-white">Pending Payments</h2>
        </div>
        
        <div class="p-6">
            <?php if (empty($pending_payments)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-green-400 text-5xl mb-4"></i>
                    <p class="text-gray-600">No pending payments to confirm.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($pending_payments as $payment): ?>
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                            <!-- Customer Info -->
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Customer Details</h3>
                                <p class="text-gray-600">
                                    <i class="fas fa-user mr-2"></i>
                                    <?= htmlspecialchars($payment['user_name']) ?>
                                </p>
                                <p class="text-gray-600">
                                    <i class="fas fa-phone mr-2"></i>
                                    <?= htmlspecialchars($payment['user_phone']) ?>
                                </p>
                            </div>

                            <!-- Booking Details -->
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Booking Details</h3>
                                <p class="text-gray-600">
                                    <i class="fas fa-futbol mr-2"></i>
                                    <?= htmlspecialchars($payment['field_name']) ?>
                                </p>
                                <p class="text-gray-600">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <?= date('F j, Y', strtotime($payment['booking_date'])) ?>
                                </p>
                                <p class="text-gray-600">
                                    <i class="fas fa-clock mr-2"></i>
                                    <?= date('g:i A', strtotime($payment['start_time'])) ?> - 
                                    <?= date('g:i A', strtotime($payment['end_time'])) ?>
                                </p>
                            </div>

                            <!-- Payment Details -->
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Payment Details</h3>
                                <p class="text-gray-600">
                                    <i class="fas fa-money-bill mr-2"></i>
                                    Rp <?= number_format($payment['amount'], 0, ',', '.') ?>
                                </p>
                                <p class="text-gray-600">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    <?= ucfirst($payment['payment_method']) ?>
                                </p>
                            </div>

                            <!-- Payment Proof -->
                            <?php if ($payment['payment_method'] === 'transfer' && $payment['payment_proof']): ?>
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Payment Proof</h3>
                                    <img src="<?= BASE_URL ?>/assets/uploads/payments/<?= htmlspecialchars($payment['payment_proof']) ?>" 
                                         alt="Payment Proof"
                                         class="w-full h-40 object-cover rounded">
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="flex justify-end space-x-2 mt-4">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="booking_id" value="<?= $payment['booking_id'] ?>">
                                    <button type="submit" name="reject_payment"
                                            onclick="return confirm('Are you sure you want to reject this payment?')"
                                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                                        Reject
                                    </button>
                                </form>
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="booking_id" value="<?= $payment['booking_id'] ?>">
                                    <button type="submit" name="confirm_payment"
                                            onclick="return confirm('Are you sure you want to confirm this payment?')"
                                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                                        Confirm
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
