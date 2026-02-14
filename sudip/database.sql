-- Create the Database
CREATE DATABASE IF NOT EXISTS cargo_record;
USE cargo_record;

-- Create Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    user_type ENUM('admin', 'user') NOT NULL DEFAULT 'user', -- User types: 'admin' or 'user'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Users
INSERT INTO users (username, password_hash, email, user_type) VALUES
('admin', '$2b$10$ht1mjjrsOfUNJnTn.o/MhOOEOGhsXr4sbCdrwU3WG/qsQDfl9cIre', 'admin@gmail.com', 'admin'),
('user1', '$2y$10$eImiTXuWVxfM37uY4JANjA.u9WvaLSVR7jY.y5MD3cAlpIkj7IDd.', 'user1@example.com', 'user'),
('user2', '$2y$10$eImiTXuWVxfM37uY4JANjA.u9WvaLSVR7jY.y5MD3cAlpIkj7IDd.', 'user2@example.com', 'user');

-- Create Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Insert Default Categories
INSERT INTO categories (category_name) VALUES
('Electronics'),
('Furniture'),
('Clothing'),
('Food');

-- Create Customers Table
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL
);


INSERT INTO customers (name, address) 
VALUES 
    ('Sohan Kafle', '123 Main St, Kathmandu'),
    ('Jane Smith', '456 Elm St, Bhaktapur'),
    ('Alice Johnson', '789 Oak St, Lalitpur');


-- Create Locations Table
CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(255) NOT NULL UNIQUE
);

-- Insert Default Locations
INSERT INTO locations (location_name) VALUES
('Kathmandu'),
('Bhaktapur'),
('Lalitpur'),
('Kavre');

-- Create Cargo Items Table
CREATE TABLE cargo_items (
    cargo_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    customer_id INT NOT NULL,
    pickup_location_id INT NOT NULL,
    dropoff_location_id INT NOT NULL,
    distance DECIMAL(10, 2) NOT NULL,
    price_per_km DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) GENERATED ALWAYS AS (distance * price_per_km) STORED,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (pickup_location_id) REFERENCES locations(location_id),
    FOREIGN KEY (dropoff_location_id) REFERENCES locations(location_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Insert Default Cargo Items for Testing
INSERT INTO cargo_items (name, quantity, price, category_id, customer_id, pickup_location_id, dropoff_location_id, distance, price_per_km, user_id) VALUES
('Laptop', 10, 500.00, 1, 1, 1, 2, 10.00, 5.00, 2),
('Sofa Set', 5, 1000.00, 2, 2, 3, 4, 20.00, 10.00, 2),
('T-Shirts', 50, 20.00, 3, 1, 2, 1, 5.00, 2.00, 3);

CREATE TABLE admin (
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
 
);

CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    system_name VARCHAR(255) NOT NULL,
    timezone VARCHAR(50) NOT NULL DEFAULT 'UTC',
    max_users INT NOT NULL DEFAULT 100,
    default_weight_unit VARCHAR(10) NOT NULL DEFAULT 'kg',
    max_cargo_weight INT NOT NULL DEFAULT 1000,
    email_notifications BOOLEAN NOT NULL DEFAULT 0,
    sms_notifications BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



ALTER TABLE users
ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE,
ADD COLUMN sms_notifications BOOLEAN DEFAULT FALSE,
ADD COLUMN theme_preference VARCHAR(10) DEFAULT 'light';

ALTER TABLE cargo_items
ADD COLUMN weight_range VARCHAR(20) NOT NULL;




CREATE TABLE IF NOT EXISTS admin_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    affected_user_id INT NOT NULL,
    action_timestamp DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES users(user_id),
    FOREIGN KEY (affected_user_id) REFERENCES users(user_id)
);

-- Add is_blocked column to users table if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_blocked BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS last_modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;



ALTER TABLE users ADD COLUMN IF NOT EXISTS is_blocked TINYINT(1) DEFAULT 0;