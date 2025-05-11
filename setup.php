<?php
// Création du dossier images s'il n'existe pas
if (!file_exists('images')) {
    mkdir('images', 0777, true);
    echo "Dossier images créé.<br>";
}

// Configuration de la base de données
$host = "localhost"; 
$username = "root";
$password = "";
$dbname = "vulnapp";

try {
    // Connexion à MySQL
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Création de la base de données
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);
    echo "Base de données créée.<br>";
    
    // Sélection de la base
    $conn->exec("USE $dbname");
    
    // Création de la table users
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50),
        password VARCHAR(50),
        email VARCHAR(100),
        full_name VARCHAR(100),
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'users' créée.<br>";

    // Insertion des utilisateurs initiaux
    $sql = "INSERT INTO users (username, password, email, full_name, role) 
            VALUES ('admin', 'admin', 'admin@example.com', 'Administrateur', 'admin')";
    $conn->exec($sql);
    $sql = "INSERT INTO users (username, password, email, full_name) 
            VALUES ('user1', 'password123', 'user1@example.com', 'Utilisateur Test')";
    $conn->exec($sql);
    echo "Utilisateurs ajoutés.<br>";
    
    // Création de la table products
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        description TEXT,
        price DECIMAL(10,2),
        image_url VARCHAR(255),
        stock INT
    )";
    $conn->exec($sql);
    echo "Table 'products' créée.<br>";
    
    // Insertion des produits
    $sql = "INSERT INTO products (name, description, price, image_url, stock)
            VALUES 
            ('Smartphone X2000', 'Le dernier smartphone avec plein de fonctionnalités', 899.99, 'phone.jpg', 50),
            ('Laptop ProBook', 'Ordinateur portable pour professionnels', 1299.99, 'laptop.jpg', 30),
            ('Casque Audio Premium', 'Son immersif et confortable', 199.99, 'headphones.jpg', 100)";
    $conn->exec($sql);
    echo "Produits ajoutés.<br>";
    
    // Création de la table comments
    $sql = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        product_id INT,
        comment TEXT,
        rating INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $conn->exec($sql);
    echo "Table 'comments' créée.<br>";
    
    // Création de la table orders
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10,2),
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $conn->exec($sql);
    echo "Table 'orders' créée.<br>";
    
    // Création de la table order_items
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT,
        price DECIMAL(10,2),
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $conn->exec($sql);
    echo "Table 'order_items' créée.<br>";
    
    // Message de confirmation
    echo "Installation terminée avec succès!";
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "<br>";
}

echo "<br><a href='index.php'>Retour à l'accueil</a>";
?>