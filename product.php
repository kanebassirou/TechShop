<?php
include 'db.php';
session_start();

// Récupération de l'ID du produit (vulnérable à l'injection SQL)
$product_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Requête vulnérable
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    header('Location: index.php');
    exit;
}

// Traitement des commentaires
if (isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $comment_text = $_POST['comment'];
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    
    // Insertion vulnérable
    $sql = "INSERT INTO comments (user_id, product_id, comment, rating) 
            VALUES ($user_id, $product_id, '$comment_text', $rating)";
    $conn->query($sql);
}

// Récupération des commentaires - modifiée pour être plus robuste face aux erreurs SQL
try {
    $sql = "SELECT c.*, u.username FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.product_id = $product_id 
            ORDER BY c.created_at DESC";
    $comments_result = $conn->query($sql);
    $comments = [];

    if ($comments_result && $comments_result->num_rows > 0) {
        while ($row = $comments_result->fetch_assoc()) {
            $comments[] = $row;
        }
    }
} catch (Exception $e) {
    // En cas d'erreur SQL, on continue avec un tableau vide de commentaires
    $comments = [];
    // Pour déboguer pendant le développement, décommentez :
    // echo "<div class='alert alert-danger'>Erreur: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['name']); ?> - TechShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    .product-image {
      max-height: 400px;
      object-fit: contain;
    }
    .rating {
      color: #ffc107;
    }
    .comment-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }
    .comment-item:last-child {
      border-bottom: none;
    }
    .add-to-cart-section {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <!-- Barre de navigation simplifiée -->
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
            <a class="nav-link" href="products.php">Produits</a>
          </li>
        </ul>
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
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Détails du produit -->
  <div class="container my-5">
    <div class="row">
      <div class="col-md-6">
        <img src="images/<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid product-image">
      </div>
      <div class="col-md-6">
        <h1><?php echo $product['name']; ?></h1>
        <div class="rating mb-3">
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="fas fa-star"></i>
          <i class="far fa-star"></i>
          <span class="ms-2 text-muted">(<?php echo count($comments); ?> avis)</span>
        </div>
        <h3 class="text-primary mb-4"><?php echo number_format($product['price'], 2); ?> €</h3>
        <p class="mb-4"><?php echo nl2br($product['description']); ?></p>
        
        <div class="add-to-cart-section">
          <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div class="row g-3 align-items-center mb-3">
              <div class="col-auto">
                <label for="quantity" class="col-form-label">Quantité</label>
              </div>
              <div class="col-auto">
                <select name="quantity" id="quantity" class="form-select">
                  <?php for($i=1; $i<=10; $i++): ?>
                  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-shopping-cart me-2"></i> Ajouter au panier
            </button>
          </form>
        </div>
        
        <div class="mt-4">
          <p>
            <i class="fas fa-check-circle text-success me-2"></i> 
            <?php echo $product['stock'] > 0 ? 'En stock' : 'Rupture de stock'; ?>
          </p>
          <p><i class="fas fa-truck me-2"></i> Livraison gratuite à partir de 50€</p>
        </div>
      </div>
    </div>
    
    <!-- Section commentaires -->
    <div class="row mt-5">
      <div class="col-12">
        <h3 class="mb-4">Commentaires et avis</h3>
        
        <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Formulaire d'ajout de commentaire (vulnérable à XSS) -->
        <div class="card mb-4">
          <div class="card-body">
            <form method="POST">
              <div class="mb-3">
                <label for="rating" class="form-label">Votre note</label>
                <select name="rating" id="rating" class="form-select">
                  <option value="5">5 étoiles - Excellent</option>
                  <option value="4">4 étoiles - Très bien</option>
                  <option value="3">3 étoiles - Bien</option>
                  <option value="2">2 étoiles - Moyen</option>
                  <option value="1">1 étoile - Décevant</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="comment" class="form-label">Votre commentaire</label>
                <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Publier</button>
            </form>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
          <a href="login.php">Connectez-vous</a> pour laisser un commentaire.
        </div>
        <?php endif; ?>
        
        <!-- Liste des commentaires -->
        <div>
          <?php if(count($comments) > 0): ?>
            <?php foreach($comments as $comment): ?>
              <div class="comment-item">
                <div class="d-flex justify-content-between">
                  <h5><?php echo htmlspecialchars($comment['username']); ?></h5>
                  <small class="text-muted"><?php echo date('d/m/Y à H:i', strtotime($comment['created_at'])); ?></small>
                </div>
                <div class="rating mb-2">
                  <?php for($i=1; $i<=5; $i++): ?>
                    <?php if($i <= $comment['rating']): ?>
                      <i class="fas fa-star"></i>
                    <?php else: ?>
                      <i class="far fa-star"></i>
                    <?php endif; ?>
                  <?php endfor; ?>
                </div>
                <!-- Affichage vulnérable à XSS -->
                <p><?php echo $comment['comment']; ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucun commentaire pour ce produit. Soyez le premier à donner votre avis!</p>
          <?php endif; ?>
        </div>
      </div>
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
          <h5>Nous contacter</h5>
          <address>
            <p><i class="fas fa-map-marker-alt me-2"></i> 123 Rue du Web, 75001 Paris</p>
            <p><i class="fas fa-phone me-2"></i> +33 1 23 45 67 89</p>
            <p><i class="fas fa-envelope me-2"></i> contact@techshop.test</p>
          </address>
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