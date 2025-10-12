# Projet Limousine - Ayoub Informatique

**Site web de location de limousines développé par Ayoub Informatique**

## À propos du développeur

**Ayoub Informatique** - Service informatique professionnel spécialisé dans le développement web et la création de sites internet sur mesure.

- **Email** : proayoubfarkh@gmail.com
- **Site web** : [ayoub-informatique.netlify.app](https://ayoub-informatique.netlify.app)
- **Spécialités** : Développement web, création de sites vitrine, applications web

## Description du projet

Site web professionnel pour une entreprise de location de limousines de luxe, incluant :

- **Page d'accueil** - Présentation des services et flotte de véhicules
- **Galerie photos** - Vitrine des limousines disponibles
- **Configuration serveur** - Scripts d'optimisation et sécurité
- **Gestion PDF** - Système de réception et traitement des documents
- **Monitoring** - Outils de surveillance du site

## Fonctionnalités

- Interface responsive et moderne
- Galerie photos optimisée
- Configuration automatique serveur OVH
- Optimisation des performances
- Sécurité renforcée
- Gestion documentaire PDF

## Technologies utilisées

- **Frontend** : HTML5, CSS3, JavaScript, jQuery
- **Backend** : PHP 7+
- **Serveur** : Apache avec configuration optimisée
- **Sécurité** : Headers de sécurité, protection CSRF
- **Performance** : Compression, cache, optimisation images

## Structure du projet

```
├── index.html              # Page d'accueil
├── galerie.html           # Galerie photos
├── .htaccess              # Configuration serveur
├── monitor.php            # Surveillance du site
├── security.php           # Configuration sécurité
├── performance-config.php # Optimisation performances
├── optimize-images.php    # Optimisation des images
├── receive-pdf.php        # Gestion des PDFs
├── README.md              # Documentation
├── assets/                # Ressources frontend
│   ├── css/              # Feuilles de styles
│   ├── js/               # Scripts JavaScript
│   ├── images/           # Images du site
│   └── webfonts/         # Polices d'écriture
├── images/               # Images des véhicules
└── pdfs/                 # Documents PDF
```

## Installation et déploiement

### Prérequis
- Serveur web Apache
- PHP 7.4 ou supérieur
- Module `mod_rewrite` activé

### Installation
1. Cloner le repository :
   ```bash
   git clone https://github.com/Ayoub-FARKH/frlimousine.git
   cd frlimousine
   ```

2. Déployer sur le serveur OVH :
   - Uploader les fichiers via FTP
   - S'assurer que `.htaccess` est présent à la racine

3. Configuration automatique :
   - Les scripts de sécurité et performance se configurent automatiquement

## Scripts disponibles

- `monitor.php` - Surveillance de l'état du site
- `security.php` - Configuration des headers de sécurité
- `performance-config.php` - Optimisation des performances
- `optimize-images.php` - Traitement et optimisation des images
- `receive-pdf.php` - Réception et gestion des documents PDF

## Sécurité

- Headers de sécurité avancés
- Protection contre les attaques XSS
- Configuration CORS sécurisée
- Cache et compression automatique

## Support et Contact

Pour toute question concernant le développement de ce site ou pour vos projets informatiques :

- **Email** : proayoubfarkh@gmail.com
- **Site web** : [ayoub-informatique.netlify.app](https://ayoub-informatique.netlify.app)

**Ayoub Informatique** - Votre partenaire pour tous vos projets web et informatiques.