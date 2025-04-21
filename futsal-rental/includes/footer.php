</div> <!-- End of Main Content Container -->

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-8">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About Section -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">About Us</h3>
                    <p class="text-gray-300">
                        We provide high-quality futsal fields for rent. Experience the best facilities and service for your futsal games.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= BASE_URL ?>" class="text-gray-300 hover:text-white">Home</a></li>
                        <li><a href="<?= BASE_URL ?>/user/booking.php" class="text-gray-300 hover:text-white">Book Field</a></li>
                        <li><a href="<?= BASE_URL ?>/register.php" class="text-gray-300 hover:text-white">Register</a></li>
                        <li><a href="<?= BASE_URL ?>/login.php" class="text-gray-300 hover:text-white">Login</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <span class="text-gray-300">+1234567890</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <span class="text-gray-300">info@futsalrental.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span class="text-gray-300">123 Futsal Street, City</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-8 pt-8 border-t border-gray-700 text-center">
                <p class="text-gray-300">
                    © <?= date('Y') ?> Futsal Rental. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Close alert messages
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                const closeButton = alert.querySelector('svg[role="button"]');
                if (closeButton) {
                    closeButton.addEventListener('click', () => {
                        alert.remove();
                    });
                }
            });
        });

        // Booking form validation (if exists)
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.addEventListener('submit', function(e) {
                const dateField = document.getElementById('booking_date');
                const timeField = document.getElementById('time_slot');
                
                if (dateField && !dateField.value) {
                    e.preventDefault();
                    alert('Please select a date');
                    return;
                }
                
                if (timeField && !timeField.value) {
                    e.preventDefault();
                    alert('Please select a time slot');
                    return;
                }
            });
        }

        // Payment method toggle (if exists)
        const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
        const transferDetails = document.getElementById('transfer_details');
        
        if (paymentMethodRadios.length && transferDetails) {
            paymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'transfer') {
                        transferDetails.classList.remove('hidden');
                    } else {
                        transferDetails.classList.add('hidden');
                    }
                });
            });
        }
    </script>
</body>
</html>
