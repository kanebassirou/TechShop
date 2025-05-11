<?php

include '../db.php';
session_start();

// Vérification de l'authentification vulnérable (ne vérifie que si l'utilisateur est connecté)
// La vérification du rôle admin est implémentée mais facilement contournable
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header('Location: ../login.php');
    exit;
}

// Faille : pas de vérification du rôle admin ici
// Un utilisateur normal peut simplement taper l'URL pour accéder au panneau d'administration

// Même si on tente de vérifier le rôle plus loin dans une condition, le code continue à s'exécuter
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

// Récupération des statistiques (requêtes vulnérables)
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];

// Si une action de suppression est demandée (vulnérable)
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $conn->query("DELETE FROM products WHERE id = $product_id");
    header("Location: dashboard.php?success=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration TechShop</title>
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
        
        .warning-banner {
            background-color: #fcf8e3;
            border-left: 5px solid #f0ad4e;
            padding: 10px 15px;
            margin-bottom: 20px;
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
                    <!-- Affichage conditionnel d'un avertissement si l'utilisateur n'est pas admin -->
                    <?php if(!$is_admin): ?>
                    <div class="warning-banner">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <strong>Attention :</strong> Vous accédez à une zone réservée aux administrateurs. Cette faille de sécurité est intentionnelle pour des fins éducatives.
                    </div>
                    <?php endif; ?>
                    
                    <!-- Message de succès (vulnérable à XSS) -->
                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Opération effectuée avec succès !
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
                                        // Récupération des produits récents (vulnérable à l'injection SQL)
                                        $products_query = "SELECT * FROM products ORDER BY id DESC LIMIT 5";
                                        $products_result = $conn->query($products_query);
                                        
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
                                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="dashboard.php?delete_product=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger"
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
                    
                    <!-- Commandes récentes -->
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
                                        // Récupération des commentaires récents (vulnérable à l'injection SQL)
                                        $comments_query = "SELECT c.*, u.username, p.name as product_name 
                                                         FROM comments c 
                                                         JOIN users u ON c.user_id = u.id 
                                                         JOIN products p ON c.product_id = p.id 
                                                         ORDER BY c.created_at DESC LIMIT 5";
                                        $comments_result = $conn->query($comments_query);
                                        
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