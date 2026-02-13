CREATE DATABASE IF NOT EXISTS nucleus_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nucleus_store;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS phones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO phones (name, description, price, image) VALUES
('Nucleus Air 11', 'Легкий смартфон с AMOLED-экраном 6.5" и батареей 5000 мАч.', 39990, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=900&q=80'),
('Nucleus Pro Max', 'Флагманский процессор, камера 200 МП и зарядка 120W.', 79990, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=900&q=80'),
('Nucleus Fold', 'Складной экран, поддержка стилуса и мультизадачность.', 99990, 'https://images.unsplash.com/photo-1520923642038-b4259acecbd7?auto=format&fit=crop&w=900&q=80');
