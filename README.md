# FR Limousine

**Location de Limousines de Luxe à Paris depuis 2001**

Site web professionnel pour FRLimousine, entreprise française leader dans la location de limousines de luxe et le transport privé haut de gamme à Paris et en Île-de-France.

## À propos de l'entreprise

FRLimousine est une entreprise française spécialisée dans la **location de limousines de luxe** et le **transport privé haut de gamme** à Paris et en Île-de-France. Depuis notre création en **2001**, nous nous engageons à offrir à notre clientèle un service d'excellence, alliant **raffinement**, **confort** et **professionnalisme**.

Notre flotte de véhicules d'exception, entretenue avec le plus grand soin, répond aux exigences les plus élevées de notre clientèle. Que ce soit pour un **mariage prestigieux**, un **événement d'entreprise**, un **transfert aéroport** ou une **soirée privée**, nous mettons tout en œuvre pour faire de chaque déplacement un moment inoubliable.

## Services proposés

- **Location de Limousines pour Mariage** - Rendez votre journée de mariage inoubliable avec nos limousines d'exception
- **Événements d'Entreprise** - Renforcez l'image de votre entreprise avec nos services de transport VIP
- **Transferts Aéroport & Gare** - Service de transfert premium vers tous les aéroports et gares de Paris
- **Soirées & Événements Privés** - Transformez vos soirées privées en moments d'exception

## Flotte de véhicules

- **Limousine Hummer** (jusqu'à 20 passagers) - 150€/heure
- **Limousine Lincoln** (jusqu'à 10 passagers) - 120€/heure
- **Mercedes Classe S** (jusqu'à 4 passagers) - 80€/heure

## Informations de contact

- **Téléphone** : 06 12 94 05 40 (24h/24 - 7j/7)
- **Email** : contact@transvoyage.fr (réponse sous 2 heures)
- **Zone d'intervention** : Paris & Île-de-France, Toute la France sur devis

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

Pour toute demande de réservation, devis ou information :

- **Téléphone** : 06 12 94 05 40 (24h/24 - 7j/7)
- **Email** : contact@transvoyage.fr (réponse sous 2 heures)
- **Site web** : www.frlimousine.com

Nous intervenons principalement à Paris et en Île-de-France, avec possibilité d'intervention dans toute la France sur devis personnalisé.