<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

try {
    $db = getDB();
    
    // Get statistics
    $stats = [
        'total_bookings' => $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
        'pending_payments' => $db->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetchColumn(),
        'total_revenue' => $db->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'confirmed'")->fetchColumn() ?? 0,
        'active_fields' => $db->query("SELECT COUNT(*) FROM fields WHERE status = 'available'")->fetchColumn()
    ];
    
    // Get recent bookings with user and field details
    $stmt = $db->query("
        SELECT 
            b.*,
            u.name as user_name,
            u.phone as user_phone,
            f.name as field_name,
            p.payment_method,
            p.payment_status,
            p.payment_proof
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN fields f ON b.field_id = f.id
        LEFT JOIN payments p ON b.id = p.booking_id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $recent_bookings = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $_SESSION['error'] = "Unable to fetch dashboard data.";
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Bookings -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Bookings</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['total_bookings']) ?></p>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Pending Payments</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['pending_payments']) ?></p>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Active Fields -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-futbol text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Active Fields</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= number_format($stats['active_fields']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-xl font-bold text-white">Recent Bookings</h2>
        </div>
        <div class="p-6">
            <?php if (empty($recent_bookings)): ?>
                <div class="text-center py-4">
                    <p class="text-gray-500">No bookings found.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Booking Details
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Payment
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($booking['user_name']) ?></p>
                                            <p class="text-gray-500"><?= htmlspecialchars($booking['user_phone']) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($booking['field_name']) ?></p>
                                            <p class="text-gray-500">
                                                <?= date('F j, Y', strtotime($booking['booking_date'])) ?>
                                            </p>
                                            <p class="text-gray-500">
                                                <?= date('g:i A', strtotime($booking['start_time'])) ?> - 
                                                <?= date('g:i A', strtotime($booking['end_time'])) ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <p class="text-gray-900">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></p>
                                            <p class="text-gray-500"><?= ucfirst($booking['payment_method']) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="<?= BASE_URL ?>/admin/manage-fields.php" 
           class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-futbol text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Manage Fields</h3>
                    <p class="text-sm text-gray-500">Add, edit, or remove futsal fields</p>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/confirm-payments.php" 
           class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-money-check-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Payment Confirmation</h3>
                    <p class="text-sm text-gray-500">Review and confirm payments</p>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>" 
           class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">View Website</h3>
                    <p class="text-sm text-gray-500">Go to the main website</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
