CREATE DATABASE IF NOT EXISTS way2go CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE way2go;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    destination VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    audience ENUM('individual','couple','group') NOT NULL DEFAULT 'individual',
    duration_days INT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    seats_available INT UNSIGNED NOT NULL,
    image_theme VARCHAR(40) NOT NULL DEFAULT 'coast',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    package_id INT UNSIGNED NOT NULL,
    travel_date DATE NOT NULL,
    travelers INT UNSIGNED NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_package FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password_hash, role)
SELECT 'Admin User', 'admin@way2go.test', '$2y$10$VPI//pvDSq55cjfz8blGrOSscnKI7t0Ul6qNQrgjgyC/ErFgN/ufG', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@way2go.test');

INSERT INTO packages (title, destination, description, audience, duration_days, price, seats_available, image_theme)
SELECT * FROM (
    SELECT 'Ceylon Coast Escape', 'Galle', 'Fort walks, southern beaches, turtle hatchery visit, and boutique coastal stays.', 'couple', 3, 48500.00, 18, 'coast'
    UNION ALL SELECT 'Hill Country Tea Trail', 'Nuwara Eliya', 'Scenic train ride, tea estate tour, waterfalls, and cool-climate gardens.', 'individual', 4, 72500.00, 14, 'hills'
    UNION ALL SELECT 'Ancient Kingdoms Tour', 'Anuradhapura', 'Sacred city landmarks, heritage guides, village lunch, and sunset viewpoints.', 'group', 2, 39500.00, 20, 'heritage'
    UNION ALL SELECT 'Wild Yala Safari', 'Yala', 'Jeep safari, wildlife lodge, birding routes, and guided nature experiences.', 'group', 3, 84000.00, 10, 'wild'
) seed
WHERE NOT EXISTS (SELECT 1 FROM packages);
