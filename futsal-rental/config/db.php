<?php
require_once 'config.php';

function getDB() {
    try {
        // Use SQLite instead of MySQL
        $sqlite_file = ROOT_PATH . '/database.sqlite';
        
        // Create SQLite database if it doesn't exist
        if (!file_exists($sqlite_file)) {
            // Create new SQLite database
            $pdo = new PDO("sqlite:" . $sqlite_file);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    phone TEXT NOT NULL,
                    address TEXT NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    role TEXT DEFAULT 'user',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS fields (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    description TEXT,
                    price_per_hour DECIMAL(10,2) NOT NULL,
                    status TEXT DEFAULT 'available',
                    image TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS bookings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    field_id INTEGER NOT NULL,
                    booking_date DATE NOT NULL,
                    start_time TIME NOT NULL,
                    end_time TIME NOT NULL,
                    total_price DECIMAL(10,2) NOT NULL,
                    status TEXT DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    FOREIGN KEY (field_id) REFERENCES fields(id)
                );

                CREATE TABLE IF NOT EXISTS payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    booking_id INTEGER NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    payment_method TEXT NOT NULL,
                    payment_status TEXT DEFAULT 'pending',
                    payment_proof TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (booking_id) REFERENCES bookings(id)
                );
            ");

            // Insert default admin user (password: admin123)
            $pdo->exec("
                INSERT INTO users (name, phone, address, email, password, role) 
                VALUES (
                    'Admin',
                    '123456789',
                    'Admin Address',
                    'admin@futsal.com',
                    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                    'admin'
                )
            ");

            // Insert sample futsal fields
            $pdo->exec("
                INSERT INTO fields (name, description, price_per_hour, image) VALUES
                ('Field A', 'Indoor futsal field with high-quality synthetic grass', 100000, 'https://images.pexels.com/photos/1277126/pexels-photo-1277126.jpeg'),
                ('Field B', 'Outdoor futsal field with professional lighting system', 120000, 'https://images.pexels.com/photos/1383776/pexels-photo-1383776.jpeg'),
                ('Field C', 'Premium indoor field with air conditioning', 150000, 'https://images.pexels.com/photos/1171084/pexels-photo-1171084.jpeg')
            ");
        } else {
            $pdo = new PDO("sqlite:" . $sqlite_file);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $pdo;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        throw new PDOException("Connection failed. Please try again later.");
    }
}
