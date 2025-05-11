CREATE DATABASE vulnapp;
USE vulnapp;

-- Table utilisateurs avec plus de champs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(50),
    email VARCHAR(100),
    full_name VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Données initiales
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('admin', 'admin', 'admin@example.com', 'Administrateur', 'admin');
INSERT INTO users (username, password, email, full_name) 
VALUES ('user1', 'password123', 'user1@example.com', 'Utilisateur Test');

-- Table de produits (pour une boutique en ligne)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    image_url VARCHAR(255),
    stock INT
);

-- Quelques produits
INSERT INTO products (name, description, price, image_url, stock)
VALUES 
('Smartphone X2000', 'Le dernier smartphone avec plein de fonctionnalités', 899.99, 'phone.jpg', 50),
('Laptop ProBook', 'Ordinateur portable pour professionnels', 1299.99, 'laptop.jpg', 30),
('Casque Audio Premium', 'Son immersif et confortable', 199.99, 'headphones.jpg', 100);

-- Table de commentaires
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    comment TEXT,
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table de commandes
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Détails des commandes
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
