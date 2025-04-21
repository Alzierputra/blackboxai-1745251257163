<?php 
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Rental System</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="<?= BASE_URL ?>" class="text-2xl font-bold text-blue-600">
                            <i class="fas fa-futbol mr-2"></i>Futsal Rental
                        </a>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="<?= BASE_URL ?>" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    
                    <?php if ($isLoggedIn && !$isAdmin): ?>
                    <a href="<?= BASE_URL ?>/user/booking.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-calendar-alt mr-1"></i> Book Field
                    </a>
                    <a href="<?= BASE_URL ?>/user/profile.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-user mr-1"></i> My Profile
                    </a>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/admin/manage-fields.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-futbol mr-1"></i> Manage Fields
                    </a>
                    <a href="<?= BASE_URL ?>/admin/confirm-payments.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-money-bill mr-1"></i> Payments
                    </a>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                    <a href="<?= BASE_URL ?>/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="<?= BASE_URL ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Home</a>
                
                <?php if ($isLoggedIn && !$isAdmin): ?>
                <a href="<?= BASE_URL ?>/user/booking.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Book Field</a>
                <a href="<?= BASE_URL ?>/user/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">My Profile</a>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Dashboard</a>
                <a href="<?= BASE_URL ?>/admin/manage-fields.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Manage Fields</a>
                <a href="<?= BASE_URL ?>/admin/confirm-payments.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Payments</a>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                <a href="<?= BASE_URL ?>/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-500 hover:bg-red-600">Logout</a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-blue-500 hover:bg-blue-600">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </span>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </span>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 py-6">
