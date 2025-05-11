<?php
// products.php
include 'db.php';
session_start();

// Récupération des paramètres (vulnérable à l'injection SQL)
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Construction de la requête SQL (vulnérable à l'injection SQL)
$sql = "SELECT * FROM products WHERE 1=1";

if (!empty($category)) {
    $sql .= " AND category = '$category'";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

// Tri (vulnérable à l'injection SQL)
$sql .= " ORDER BY $sort";

$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Produits - TechShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .product-card {
            height: 100%;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .filters {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .page-title {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">TechShop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Produits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comment.php">Avis</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <form class="d-flex" action="products.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Rechercher" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-primary" type="submit">Rechercher</button>
                </form>
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Mon compte</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="container my-5">
        <h1 class="page-title">Nos Produits</h1>
        
        <!-- Filtres et tri (vulnérables à XSS et injection SQL) -->
        <div class="filters">
            <form action="products.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo $search; ?>">
                </div>
                <div class="col-md-4">
                    <label for="sort" class="form-label">Trier par</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nom (A-Z)</option>
                        <option value="name DESC" <?php echo $sort == 'name DESC' ? 'selected' : ''; ?>>Nom (Z-A)</option>
                        <option value="price" <?php echo $sort == 'price' ? 'selected' : ''; ?>>Prix croissant</option>
                        <option value="price DESC" <?php echo $sort == 'price DESC' ? 'selected' : ''; ?>>Prix décroissant</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Appliquer les filtres</button>
                </div>
            </form>
        </div>
        
        <!-- Liste des produits -->
        <div class="row">
            <?php if (count($products) > 0): ?>
                <?php foreach($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card">
                            <img src="images/<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold"><?php echo number_format($product['price'], 2); ?> €</span>
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Aucun produit ne correspond à votre recherche.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pied de page -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>TechShop</h5>
                    <p>Votre boutique spécialisée en produits technologiques.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">À propos</a></li>
                        <li><a href="comment.php" class="text-white">Commentaires</a></li>
                        <li><a href="contact.php" class="text-white">Nous contacter</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Administrateur</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="text-white">Connexion admin</a></li>
                        <li><a href="change_password.php" class="text-white">Changer mot de passe</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> TechShop - Application de démonstration (vulnérable pour tests de sécurité)</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>