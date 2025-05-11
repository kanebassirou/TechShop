# TechShop - Application Web Vulnérable pour Audit de Sécurité

Cette application est conçue à des fins éducatives pour illustrer et pratiquer différentes phases d'un audit de sécurité complet.

## ⚠️ AVERTISSEMENT

Cette application contient intentionnellement des vulnérabilités de sécurité. NE DÉPLOYEZ JAMAIS cette application sur un serveur de production ou sur un serveur accessible publiquement.

## Installation

1. Placez les fichiers dans le répertoire `htdocs` de votre serveur XAMPP
2. Démarrez les services Apache et MySQL
3. Importez la base de données en visitant `http://localhost/vulnerable-app/setup.php`
4. Accédez à l'application via `http://localhost/vulnerable-app/`

## Compte administrateur par défaut

- Nom d'utilisateur: `admin`
- Mot de passe: `admin`

## Vulnérabilités présentes

L'application contient volontairement les vulnérabilités suivantes à des fins de test et d'apprentissage :

### 1. Injection SQL

- Page de connexion (`login.php`)
- Page de changement de mot de passe (`change_password.php`)
- Détails du produit (`product.php`)

### 2. Cross-Site Scripting (XSS)

- Page de commentaires (`comment.php`)
- Recherche sur la page d'accueil (`index.php`)
- Affichage des commentaires sur les produits (`product.php`)

### 3. Cross-Site Request Forgery (CSRF)

- Changement de mot de passe (`change_password.php`)
- Formulaire de contact (`contact.php`)

### 4. Injection de commandes

- Page de contact (`contact.php`, commentée pour éviter l'exécution réelle)

### 5. Upload de fichier non sécurisé

- Page de profil utilisateur (`profile.php`)

### 6. Divulgation d'informations sensibles

- Divulgation des versions de logiciels
- Affichage des erreurs PHP
- Commentaires contenant des informations sensibles

### 7. Contrôle d'accès défectueux

- Zone d'administration accessible aux utilisateurs non administrateurs (`admin/dashboard.php`)
- Manque de validation des droits d'accès sur les fonctionnalités administratives

## Exemples d'exploitation

### Injection SQL

```
' OR '1'='1
' OR '1'='1' --
' UNION SELECT username, password FROM users --
```

### XSS

```
<script>alert('XSS')</script>
<img src="x" onerror="alert('XSS')">
<svg onload="alert('XSS')">
```

### Accès non autorisé

```
# Connexion en tant qu'utilisateur normal puis accès à :
http://localhost/vulnerable-app/admin/dashboard.php
```

## Méthodologie d'audit de sécurité

Cette application sert de support pour pratiquer les différentes phases d'un audit de sécurité :

### 1. Phase de test

- **Reconnaissance** : Identification des technologies utilisées et cartographie de l'application
- **Analyse automatisée** : Utilisation d'outils comme OWASP ZAP, Nikto, SQLmap
- **Test manuel** : Exploitation des vulnérabilités identifiées
- **Documentation** : Consignation précise des vulnérabilités et vecteurs d'attaque

### 2. Phase de remédiation

- **Priorisation** : Classification des vulnérabilités selon leur gravité (CVSS)
- **Développement de correctifs** : Implémentation de solutions pour chaque vulnérabilité
- **Mesures de contournement** : Configuration de barrières de sécurité temporaires

### 3. Phase finale

- **Tests de validation** : Vérification que les vulnérabilités ont bien été corrigées
- **Revue de code** : Analyse approfondie du code pour s'assurer qu'aucune nouvelle vulnérabilité n'a été introduite
- **Documentation finale** : Rapport détaillant l'état initial, les corrections apportées et les recommandations

## Livrables du projet d'audit

- Rapport initial détaillant les vulnérabilités (avec preuves de concept)
- Plan de remédiation avec priorisation des correctifs
- Code source sécurisé après corrections
- Rapport final comparant l'état initial et final de l'application
- Présentation des résultats et recommandations

## Objectifs d'apprentissage

Cette application peut être utilisée pour:

- Comprendre comment fonctionnent les vulnérabilités web courantes
- Pratiquer les techniques de test de sécurité
- Apprendre à sécuriser une application web en identifiant et corrigeant les vulnérabilités
- Maîtriser la méthodologie complète d'un audit de sécurité

## Ressources supplémentaires

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [Common Vulnerability Scoring System (CVSS)](https://www.first.org/cvss/)
- [ISO 27001 - Management de la sécurité de l'information](https://www.iso.org/fr/isoiec-27001-information-security.html)