<?php
include 'db.php';
session_start();

// Récupération des produits depuis la base de données
$sql = "SELECT * FROM products LIMIT 6";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Récupération du paramètre de recherche (vulnérable à XSS)
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechShop - Votre Boutique Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    :root {
      --primary-color: #0066cc;
      --primary-dark: #004080;
      --secondary-color: #e9ecef;
      --accent-color: #ffc107;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
      background-color: #f8f9fa;
    }
    
    .navbar-brand {
      font-weight: 700;
      color: var(--primary-color) !important;
      font-size: 1.5rem;
    }
    
    .hero-section {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      padding: 80px 0;
      margin-bottom: 50px;
      border-radius: 0 0 30px 30px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .hero-title {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    
    .hero-subtitle {
      font-size: 1.25rem;
      margin-bottom: 30px;
      font-weight: 300;
    }
    
    .btn-hero {
      padding: 10px 25px;
      font-weight: 600;
      border-radius: 30px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      transition: all 0.3s;
    }
    
    .btn-hero:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
    }
    
    .section-title {
      color: var(--primary-dark);
      text-align: center;
      margin-bottom: 40px;
      font-size: 2.2rem;
      font-weight: 700;
      position: relative;
      padding-bottom: 15px;
    }
    
    .section-title::after {
      content: "";
      position: absolute;
      left: 50%;
      bottom: 0;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background-color: var(--primary-color);
    }
    
    .product-card {
      height: 100%;
      border: none;
      border-radius: 15px;
      overflow: hidden;
      transition: all 0.3s;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .card-img-top {
      height: 200px;
      object-fit: cover;
    }
    
    .card-body {
      padding: 20px;
    }
    
    .card-title {
      font-weight: 600;
      margin-bottom: 15px;
      color: var(--primary-dark);
    }
    
    .card-text {
      color: #666;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    
    .btn-outline-primary {
      border-color: var(--primary-color);
      color: var(--primary-color);
      border-radius: 20px;
      padding: 5px 15px;
      font-weight: 500;
      transition: all 0.3s;
    }
    
    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      color: white;
      transform: translateY(-2px);
    }
    
    .price {
      font-weight: 700;
      color: var(--primary-color);
      font-size: 1.1rem;
    }
    
    .search-result {
      margin: 20px 0 30px;
      padding: 20px;
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }
    
    .feature-box {
      text-align: center;
      padding: 30px 20px;
      margin-bottom: 30px;
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s;
    }
    
    .feature-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 20px;
    }
    
    footer {
      background-color: #212529;
      color: #f8f9fa;
      padding: 60px 0 20px;
      margin-top: 70px;
      border-radius: 30px 30px 0 0;
    }
    
    footer h5 {
      font-weight: 600;
      margin-bottom: 20px;
      color: white;
    }
    
    footer a.text-white {
      text-decoration: none;
      transition: all 0.2s;
      display: block;
      padding: 5px 0;
    }
    
    footer a.text-white:hover {
      color: var(--accent-color) !important;
      text-decoration: none;
      transform: translateX(3px);
    }
    
    .navbar .nav-link {
      font-weight: 500;
      transition: all 0.2s;
    }
    
    .navbar .nav-link:hover {
      color: var(--primary-color);
      transform: translateY(-2px);
    }
    
    .search-form {
      position: relative;
    }
    
    .search-form .form-control {
      border-radius: 20px;
      padding-right: 40px;
      border: 1px solid #ddd;
    }
    
    .search-form .btn {
      position: absolute;
      right: 5px;
      top: 5px;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }
  </style>
</head>
<body>
  <!-- Barre de navigation -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-laptop-code me-2"></i>TechShop
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="index.php">
              <i class="fas fa-home me-1"></i> Accueil
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="products.php">
              <i class="fas fa-boxes me-1"></i> Produits
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="comment.php">
              <i class="fas fa-comments me-1"></i> Avis
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="contact.php">
              <i class="fas fa-envelope me-1"></i> Contact
            </a>
          </li>
        </ul>
        <form class="d-flex search-form me-3" action="index.php" method="GET">
          <input class="form-control" type="search" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search_term); ?>">
          <button class="btn btn-primary" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </form>
        <ul class="navbar-nav">
          <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="profile.php">
                <i class="fas fa-user-circle me-1"></i> Mon compte
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">
                <i class="fas fa-sign-in-alt me-1"></i> Connexion
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="register.php">
                <i class="fas fa-user-plus me-1"></i> Inscription
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Message de recherche (vulnérable au XSS) -->
  <?php if(!empty($search_term)): ?>
  <div class="container">
    <div class="search-result">
      <h4><i class="fas fa-search me-2"></i>Résultats pour : <?php echo $search_term; ?></h4>
    </div>
  </div>
  <?php endif; ?>

  <!-- Section héro -->
  <section class="hero-section">
    <div class="container text-center">
      <h1 class="hero-title">Bienvenue sur TechShop</h1>
      <p class="hero-subtitle">Votre destination pour les derniers gadgets et équipements technologiques</p>
      <a href="products.php" class="btn btn-light btn-lg btn-hero">
        <i class="fas fa-shopping-bag me-2"></i>Découvrir nos produits
      </a>
    </div>
  </section>

  <!-- Caractéristiques -->
  <div class="container mb-5">
    <div class="row">
      <div class="col-md-4">
        <div class="feature-box">
          <i class="fas fa-truck feature-icon"></i>
          <h4>Livraison rapide</h4>
          <p>Livraison gratuite à partir de 50€ d'achat</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-box">
          <i class="fas fa-shield-alt feature-icon"></i>
          <h4>Garantie 2 ans</h4>
          <p>Sur tous nos produits technologiques</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-box">
          <i class="fas fa-headset feature-icon"></i>
          <h4>Support 24/7</h4>
          <p>Une équipe à votre service pour vous accompagner</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Produits en vedette -->
  <div class="container mb-5">
    <h2 class="section-title">Produits populaires</h2>
    <div class="row">
      <?php foreach($products as $product): ?>
      <div class="col-md-4 mb-4">
        <div class="card product-card">
          <img src="images/<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
            <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price"><?php echo number_format($product['price'], 2); ?> €</span>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i>Détails
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="products.php" class="btn btn-primary px-4 py-2">
        <i class="fas fa-th-list me-2"></i>Voir tous nos produits
      </a>
    </div>
  </div>

  <!-- Section newsletter -->
  <div class="container mb-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-5 text-center">
            <h3 class="mb-4">Abonnez-vous à notre newsletter</h3>
            <p class="mb-4">Recevez nos dernières offres et actualités directement dans votre boîte mail</p>
            <!-- Formulaire vulnérable -->
            <form class="row g-2 justify-content-center">
              <div class="col-auto flex-grow-1">
                <input type="email" class="form-control form-control-lg" placeholder="Votre adresse email">
              </div>
              <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-lg">S'abonner</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pied de page -->
  <footer class="bg-dark text-white">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4">
          <h5><i class="fas fa-laptop-code me-2"></i>TechShop</h5>
          <p>Votre boutique spécialisée en produits technologiques de haute qualité depuis 2023.</p>
          <div class="social-icons mt-3">
            <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <h5>Liens rapides</h5>
          <ul class="list-unstyled">
            <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>À propos de nous</a></li>
            <li><a href="products.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Nos produits</a></li>
            <li><a href="comment.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Commentaires</a></li>
            <li><a href="contact.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Nous contacter</a></li>
          </ul>
        </div>
        <div class="col-md-3 mb-4">
          <h5>Service client</h5>
          <ul class="list-unstyled">
            <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>FAQ</a></li>
            <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>Livraison</a></li>
            <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>Retours</a></li>
            <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>Conditions générales</a></li>
          </ul>
        </div>
        <div class="col-md-2 mb-4">
          <h5>Administration</h5>
          <ul class="list-unstyled">
            <li><a href="login.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Connexion admin</a></li>
            <li><a href="change_password.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Mot de passe</a></li>
          </ul>
        </div>
      </div>
      <hr>
      <div class="text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> TechShop - Application de démonstration (vulnérable pour tests de sécurité)</p>
        <p class="small text-muted mt-2">Ne pas utiliser en environnement de production</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>