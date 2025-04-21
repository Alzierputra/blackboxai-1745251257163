<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// Handle field operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();

        // Add new field
        if (isset($_POST['add_field'])) {
            $stmt = $db->prepare("
                INSERT INTO fields (name, description, price_per_hour, image, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['price_per_hour'],
                $_POST['image'],
                $_POST['status']
            ]);
            $_SESSION['success'] = "Field added successfully.";
        }

        // Update field
        elseif (isset($_POST['update_field'])) {
            $stmt = $db->prepare("
                UPDATE fields 
                SET name = ?, description = ?, price_per_hour = ?, image = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['price_per_hour'],
                $_POST['image'],
                $_POST['status'],
                $_POST['field_id']
            ]);
            $_SESSION['success'] = "Field updated successfully.";
        }

        // Delete field
        elseif (isset($_POST['delete_field'])) {
            // Check if field has any bookings
            $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE field_id = ?");
            $stmt->execute([$_POST['field_id']]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Cannot delete field with existing bookings.";
            } else {
                $stmt = $db->prepare("DELETE FROM fields WHERE id = ?");
                $stmt->execute([$_POST['field_id']]);
                $_SESSION['success'] = "Field deleted successfully.";
            }
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        error_log("Field management error: " . $e->getMessage());
        $_SESSION['error'] = "Operation failed. Please try again.";
    }
}

// Fetch all fields
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM fields ORDER BY name");
    $fields = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching fields: " . $e->getMessage());
    $_SESSION['error'] = "Unable to fetch fields.";
    $fields = [];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Add New Field Form -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-xl font-bold text-white">Add New Field</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Field Name</label>
                        <input type="text" id="name" name="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="price_per_hour" class="block text-sm font-medium text-gray-700">Price per Hour (Rp)</label>
                        <input type="number" id="price_per_hour" name="price_per_hour" required min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Image URL</label>
                        <input type="url" id="image" name="image" required
                               placeholder="https://example.com/image.jpg"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="add_field"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add Field
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Fields -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-blue-600">
            <h2 class="text-xl font-bold text-white">Manage Fields</h2>
        </div>
        <div class="p-6">
            <?php if (empty($fields)): ?>
                <div class="text-center py-4">
                    <p class="text-gray-500">No fields found.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($fields as $field): ?>
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                            <img src="<?= htmlspecialchars($field['image']) ?>" 
                                 alt="<?= htmlspecialchars($field['name']) ?>"
                                 class="w-full h-48 object-cover rounded-t-lg">
                            
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?= htmlspecialchars($field['name']) ?>
                                    </h3>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $field['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($field['status']) ?>
                                    </span>
                                </div>
                                
                                <p class="text-gray-600 text-sm mb-2">
                                    <?= htmlspecialchars($field['description']) ?>
                                </p>
                                
                                <p class="text-blue-600 font-semibold mb-4">
                                    Rp <?= number_format($field['price_per_hour'], 0, ',', '.') ?>/hour
                                </p>

                                <!-- Edit Form (Initially Hidden) -->
                                <div id="editForm<?= $field['id'] ?>" class="hidden">
                                    <form method="POST" action="" class="space-y-4">
                                        <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Name</label>
                                            <input type="text" name="name" value="<?= htmlspecialchars($field['name']) ?>" required
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Description</label>
                                            <textarea name="description" rows="2" required
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"><?= htmlspecialchars($field['description']) ?></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Price per Hour</label>
                                            <input type="number" name="price_per_hour" value="<?= $field['price_per_hour'] ?>" required
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Image URL</label>
                                            <input type="url" name="image" value="<?= htmlspecialchars($field['image']) ?>" required
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Status</label>
                                            <select name="status" required
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                <option value="available" <?= $field['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                                                <option value="maintenance" <?= $field['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                            </select>
                                        </div>

                                        <div class="flex justify-end space-x-2">
                                            <button type="button" 
                                                    onclick="document.getElementById('editForm<?= $field['id'] ?>').classList.add('hidden')"
                                                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800">
                                                Cancel
                                            </button>
                                            <button type="submit" name="update_field"
                                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-end space-x-2">
                                    <button onclick="document.getElementById('editForm<?= $field['id'] ?>').classList.remove('hidden')"
                                            class="px-3 py-1 text-blue-600 hover:text-blue-800 text-sm">
                                        Edit
                                    </button>
                                    <form method="POST" action="" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this field?');">
                                        <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                        <button type="submit" name="delete_field"
                                                class="px-3 py-1 text-red-600 hover:text-red-800 text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
