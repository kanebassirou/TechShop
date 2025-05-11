# TechShop - Application Web Sécurisée (Version Corrigée)

Cette application était initialement conçue à des fins éducatives pour illustrer différentes vulnérabilités web. Cette version a été corrigée et sécurisée suite à un audit complet.

## Installation

1. Placez les fichiers dans le répertoire `htdocs` de votre serveur XAMPP
2. Démarrez les services Apache et MySQL
3. Importez la base de données en visitant `http://localhost/vulnerable-app/setup.php`
4. Accédez à l'application via `http://localhost/vulnerable-app/`

## Compte administrateur

- Nom d'utilisateur: `admin`
- Utilisez le mot de passe créé lors de l'installation

## Vulnérabilités corrigées

L'application a été sécurisée contre les vulnérabilités suivantes :

### 1. Contournement de l'authentification
- Vérification des permissions avant tout accès aux pages d'administration
- Contrôle systématique du rôle utilisateur dans chaque requête administrative
- Protection des URLs directes contre les accès non autorisés
- Implémentation d'une couche de vérification au niveau du routeur de l'application

### 2. Injection SQL
- Utilisation de requêtes préparées et PDO
- Validation des entrées utilisateur
- Limitation des privilèges de la base de données

### 2. Cross-Site Scripting (XSS)
- Échappement des sorties HTML
- Implémentation de Content Security Policy (CSP)
- Validation des entrées utilisateur

### 3. Cross-Site Request Forgery (CSRF)
- Tokens CSRF sur tous les formulaires
- Vérification de l'origine des requêtes
- Implémentation de SameSite cookies

### 4. Injection de commandes
- Suppression des fonctions d'exécution de commande
- Implémentation d'une liste blanche pour les entrées
- Validation stricte des données

### 5. Upload de fichiers
- Validation du type MIME
- Renommage aléatoire des fichiers
- Vérification du contenu des fichiers
- Stockage hors de la racine web

### 6. Protection des informations sensibles
- Suppression des informations de débogage et versions
- Gestion appropriée des erreurs
- Nettoyage des commentaires sensibles

### 7. Contrôle d'accès
- Implémentation de RBAC (Role-Based Access Control)
- Vérification systématique des permissions
- Sessions sécurisées

## Bonnes pratiques implémentées

- **Authentification** : Hachage sécurisé des mots de passe avec Argon2id
- **Sessions** : Configuration sécurisée (httpOnly, secure, SameSite)
- **Base de données** : Utilisation de requêtes préparées exclusivement
- **Journalisation** : Enregistrement des événements de sécurité
- **En-têtes HTTP** : Headers de sécurité (CSP, X-XSS-Protection, etc.)
- **Validation** : Filtrage des entrées et sorties systématique

## Résultats de l'audit final

- Élimination de toutes les vulnérabilités critiques et majeures
- Réduction significative de la surface d'attaque
- Implémentation des recommandations OWASP
- Conformité aux bonnes pratiques de sécurité actuelles

## Ressources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)
