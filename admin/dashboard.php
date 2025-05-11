<?php
// Définir une constante pour sécuriser l'inclusion
define('SECURE_ACCESS', true);

// Démarrer la session avant toute sortie
session_start();

// Inclusion de la connexion à la base de données
include '../db.php';

// Inclusion du système d'authentification admin sécurisé
include 'admin_auth.php';

// À ce stade, si l'utilisateur n'est pas admin, il a déjà été redirigé

// Utilisation de requêtes préparées pour récupérer les statistiques en toute sécurité
$stmt_users = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt_users->execute();
$total_users = $stmt_users->get_result()->fetch_assoc()['count'];

$stmt_products = $conn->prepare("SELECT COUNT(*) as count FROM products");
$stmt_products->execute();
$total_products = $stmt_products->get_result()->fetch_assoc()['count'];

$stmt_comments = $conn->prepare("SELECT COUNT(*) as count FROM comments");
$stmt_comments->execute();
$total_comments = $stmt_comments->get_result()->fetch_assoc()['count'];

// Traitement sécurisé de la suppression de produit
if (isset($_GET['delete_product'])) {
    // Vérification CSRF
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Erreur de validation du formulaire. Veuillez réessayer.";
        header("Location: dashboard.php");
        exit;
    }
    
    // Conversion en entier pour éviter l'injection SQL
    $product_id = (int)$_GET['delete_product'];
    
    // Utilisation de requête préparée pour la suppression
    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete_stmt->bind_param("i", $product_id);
    $delete_stmt->execute();
    
    header("Location: dashboard.php?success=1");
    exit;
}

// Génération d'un nouveau token CSRF si nécessaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration TechShop</title>
    
    <!-- Ajout d'en-têtes de sécurité -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; script-src 'self' https://cdn.jsdelivr.net;">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar {
            background-color: #212529;
            color: white;
            height: 100vh;
            position: sticky;
            top: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.8rem 1rem;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .stats-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .stats-card .card-body {
            padding: 1.5rem;
        }
        
        .stats-icon {
            font-size: 2rem;
            color: #0066cc;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }
        
        .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            padding: 0 1rem;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .admin-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Barre latérale -->
            <div class="col-md-3 col-lg-2 p-0 sidebar">
                <div class="logo">
                    <i class="fas fa-laptop-code"></i>
                    TechShop
                </div>
                <hr class="mx-3">
                <div class="d-flex flex-column px-3">
                    <span class="mb-3 text-secondary">ADMINISTRATION</span>
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                    <a href="products.php" class="nav-link">
                        <i class="fas fa-box"></i> Gestion des produits
                    </a>
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i> Utilisateurs
                    </a>
                    <a href="comments.php" class="nav-link">
                        <i class="fas fa-comments"></i> Commentaires
                    </a>
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                    <hr>
                    <a href="../logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-9 col-lg-10 main-content p-0">
                <!-- En-tête -->
                <div class="admin-header mb-4">
                    <h2 class="m-0">Tableau de bord</h2>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="../index.php" class="btn btn-sm btn-outline-secondary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Voir le site
                        </a>
                    </div>
                </div>
                
                <div class="container-fluid px-4">
                    <!-- Message de succès sécurisé -->
                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Opération effectuée avec succès !
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo htmlspecialchars($_SESSION['error_message']);
                            unset($_SESSION['error_message']); 
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Statistiques -->
                    <h4 class="mb-4">Statistiques générales</h4>
                    <div class="row mb-5">
                        <div class="col-md-4 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Utilisateurs</h6>
                                            <p class="stats-number"><?php echo $total_users; ?></p>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Produits</h6>
                                            <p class="stats-number"><?php echo $total_products; ?></p>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Commentaires</h6>
                                            <p class="stats-number"><?php echo $total_comments; ?></p>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-comments"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Liste des produits récents -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4>Produits récents</h4>
                                <a href="products.php" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Ajouter un produit
                                </a>
                            </div>
                            
                            <div class="table-container">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Nom</th>
                                            <th>Prix</th>
                                            <th>Stock</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Récupération des produits récents avec requête préparée
                                        $products_stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC LIMIT 5");
                                        $products_stmt->execute();
                                        $products_result = $products_stmt->get_result();
                                        
                                        if ($products_result && $products_result->num_rows > 0) {
                                            while ($product = $products_result->fetch_assoc()): 
                                        ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td>
                                                    <img src="../images/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         width="50" height="50" 
                                                         class="img-thumbnail">
                                                </td>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo number_format($product['price'], 2); ?> €</td>
                                                <td>
                                                    <?php if ($product['stock'] > 0): ?>
                                                        <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="dashboard.php?delete_product=<?php echo $product['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        } else {
                                            echo '<tr><td colspan="6" class="text-center">Aucun produit trouvé</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Commentaires récents -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <h4 class="mb-4">Commentaires récents</h4>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Utilisateur</th>
                                            <th>Produit</th>
                                            <th>Commentaire</th>
                                            <th>Note</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Récupération des commentaires récents avec requête préparée
                                        $comments_stmt = $conn->prepare(
                                            "SELECT c.*, u.username, p.name as product_name 
                                             FROM comments c 
                                             JOIN users u ON c.user_id = u.id 
                                             JOIN products p ON c.product_id = p.id 
                                             ORDER BY c.created_at DESC LIMIT 5"
                                        );
                                        $comments_stmt->execute();
                                        $comments_result = $comments_stmt->get_result();
                                        
                                        if ($comments_result && $comments_result->num_rows > 0) {
                                            while ($comment = $comments_result->fetch_assoc()): 
                                        ?>
                                            <tr>
                                                <td><?php echo $comment['id']; ?></td>
                                                <td><?php echo htmlspecialchars($comment['username']); ?></td>
                                                <td><?php echo htmlspecialchars($comment['product_name']); ?></td>
                                                <td><?php echo substr(htmlspecialchars($comment['comment']), 0, 50) . '...'; ?></td>
                                                <td>
                                                    <?php for($i=1; $i<=5; $i++): ?>
                                                        <?php if($i <= $comment['rating']): ?>
                                                            <i class="fas fa-star text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-warning"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        } else {
                                            echo '<tr><td colspan="6" class="text-center">Aucun commentaire trouvé</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>