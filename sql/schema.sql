CREATE DATABASE IF NOT EXISTS nucleus_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nucleus_store;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    city VARCHAR(120) DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS phones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    specs TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    phone_id INT NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (order_id)
);

INSERT INTO phones (name, description, specs, price, image) VALUES
('iPhone 16 Pro Max', 'Флагман Apple с большим дисплеем и топовой производительностью.', 'Экран: 6.9\" OLED | Чип: A18 Pro | Камера: 48+12+12 МП | Память: 256 ГБ | Батарея: 4676 мАч', 189990, 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=900&q=80'),
('Samsung Galaxy S24 Ultra', 'Премиальный Android-смартфон с S Pen и продвинутой камерой.', 'Экран: 6.8\" AMOLED | Чип: Snapdragon 8 Gen 3 | Камера: 200+50+12+10 МП | Память: 256 ГБ | Батарея: 5000 мАч', 149990, 'https://images.unsplash.com/photo-1610792516307-ea5acd9c3b00?auto=format&fit=crop&w=900&q=80'),
('Xiaomi 14 Ultra', 'Флагман с акцентом на мобильную фотографию и быструю зарядку.', 'Экран: 6.73\" AMOLED | Чип: Snapdragon 8 Gen 3 | Камера: 50+50+50+50 МП | Память: 512 ГБ | Батарея: 5300 мАч', 119990, 'https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=900&q=80');


CREATE TABLE IF NOT EXISTS wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_phone (user_id, phone_id),
    INDEX (user_id),
    INDEX (phone_id)
);

CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    comment VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (order_id)
);
