/**
 * FRLimousine - JavaScript Élégant & Professionnel
 * ================================================
 * Interactions essentielles pour un site haut de gamme
 */

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // MENU MOBILE - Supprimé
    // ============================================
    // Le menu burger a été supprimé du site

    // ============================================
    // SMOOTH SCROLLING - Navigation Fluide
    // ============================================

    function initSmoothScrolling() {
        const anchors = document.querySelectorAll('a[href^="#"]');

        anchors.forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Ignorer les liens juste "#"
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();

                    // Calculer la position avec la hauteur du header
                    const headerHeight = document.querySelector('#header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                }
            });
        });
    }

    // ============================================
    // SLIDER D'AVIS CLIENTS - Automatique
    // ============================================

    function initTestimonialsSlider() {
        const testimonials = document.querySelectorAll('.testimonial-card');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        let currentIndex = 0;
        let autoSlideInterval;

        function showTestimonial(index) {
            // Masquer tous les témoignages
            testimonials.forEach(testimonial => {
                testimonial.classList.remove('active');
            });

            // Retirer la classe active de tous les dots
            dots.forEach(dot => {
                dot.classList.remove('active');
            });

            // Afficher le témoignage actuel
            if (testimonials[index]) {
                testimonials[index].classList.add('active');
            }

            // Activer le dot correspondant
            if (dots[index]) {
                dots[index].classList.add('active');
            }

            currentIndex = index;
        }

        function nextTestimonial() {
            const nextIndex = (currentIndex + 1) % testimonials.length;
            showTestimonial(nextIndex);
        }

        function previousTestimonial() {
            const prevIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
            showTestimonial(prevIndex);
        }

        function goToTestimonial(index) {
            showTestimonial(index);
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(nextTestimonial, 10000); // 10 secondes
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        // Navigation avec les boutons
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                stopAutoSlide();
                nextTestimonial();
                startAutoSlide();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                stopAutoSlide();
                previousTestimonial();
                startAutoSlide();
            });
        }

        // Navigation avec les dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                stopAutoSlide();
                goToTestimonial(index);
                startAutoSlide();
            });
        });

        // Pause auto slide au survol
        const sliderContainer = document.querySelector('.testimonials-slider');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoSlide);
            sliderContainer.addEventListener('mouseleave', startAutoSlide);
        }

        // Démarrer le slider automatique
        if (testimonials.length > 0) {
            showTestimonial(0);
            startAutoSlide();
        }
    }

    // ============================================
    // ANIMATIONS AU SCROLL - Élégantes
    // ============================================

    function initScrollAnimations() {
        // Éléments à animer
        const animateElements = document.querySelectorAll('.service-card, .vehicle-card, .contact-method, .testimonial-card');

        // Options de l'observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        // Créer l'observer
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-on-scroll', 'in-view');
                }
            });
        }, observerOptions);

        // Observer tous les éléments
        animateElements.forEach(element => {
            observer.observe(element);
        });
    }

    // ============================================
    // FORMULAIRE - Gestion Professionnelle
    // ============================================

    function initFormHandling() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('.submit-btn');
                if (submitBtn) {
                    // Animation de chargement
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                    submitBtn.disabled = true;

                    // Simulation d'envoi
                    setTimeout(() => {
                        submitBtn.innerHTML = '<i class="fas fa-check"></i> Demande envoyée !';
                        submitBtn.style.background = '#28a745';

                        // Reset après 3 secondes
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            submitBtn.style.background = '';
                            form.reset();
                        }, 3000);
                    }, 2000);
                }
            });
        });
    }

    // ============================================
    // HEADER SCROLL - Effet Subtil
    // ============================================

    function initHeaderScroll() {
        const header = document.querySelector('#header');
        let lastScrollTop = 0;

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScrollTop = scrollTop;
        });
    }

    // ============================================
    // IMAGES - Optimisation Élégante
    // ============================================

    function initImageOptimization() {
        const images = document.querySelectorAll('img');

        images.forEach(function(img) {
            // Lazy loading
            img.loading = 'lazy';

            // Gestion d'erreur simple
            img.addEventListener('error', function() {
                this.style.display = 'none';
                console.warn('Image failed to load:', this.src);
            });
        });
    }

    // ============================================
    // INITIALISATION - Démarrage Propre
    // ============================================

    // Initialiser toutes les fonctionnalités
    // initMobileMenu(); // Menu supprimé
    initSmoothScrolling();
    initTestimonialsSlider();
    initScrollAnimations();
    initFormHandling();
    initHeaderScroll();
    initImageOptimization();

    // Retirer la classe preload après chargement
    window.addEventListener('load', function() {
        document.body.classList.remove('is-preload');
    });

    // ============================================
    // SYSTÈME DE RÉSERVATION AVANCÉ
    // ============================================

    /* Tarifs des véhicules (basés sur le site existant) */
    const VEHICULE_PRICES = {
        'mercedes-s': 80,  // €/heure
        'lincoln': 120,    // €/heure
        'hummer': 150      // €/heure
    };

    const OPTIONS_PRICES = {
        'decoration-florale': 50,
        'boissons': 30,
        'musique': 25,
        'chauffeur-costume': 20
    };

    // ============================================
    // CALCUL AUTOMATIQUE DES PRIX
    // ============================================

    function calculatePrice() {
        const vehicule = document.getElementById('vehicule-select').value;
        const duree = parseInt(document.getElementById('duree-select').value);
        const options = document.querySelectorAll('input[name="options[]"]:checked');

        if (!vehicule || !duree) {
            document.getElementById('price-calculation').style.display = 'none';
            return;
        }

        // Calcul du prix du véhicule
        const prixVehicule = VEHICULE_PRICES[vehicule] * duree;

        // Calcul du prix des options
        let prixOptions = 0;
        options.forEach(option => {
            prixOptions += OPTIONS_PRICES[option.value];
        });

        const prixTotal = prixVehicule + prixOptions;

        // Mise à jour de l'affichage
        document.getElementById('selected-vehicule').textContent = getVehiculeName(vehicule);
        document.getElementById('vehicule-price').textContent = prixVehicule + '€';
        document.getElementById('selected-duree').textContent = duree;
        document.getElementById('duree-price').textContent = prixVehicule + '€';

        if (prixOptions > 0) {
            document.getElementById('options-price-row').style.display = 'flex';
            document.getElementById('options-price').textContent = prixOptions + '€';
        } else {
            document.getElementById('options-price-row').style.display = 'none';
        }

        document.getElementById('total-price').innerHTML = '<strong>' + prixTotal + '€</strong>';

        // Afficher le récapitulatif
        document.getElementById('price-calculation').style.display = 'block';
    }

    function getVehiculeName(code) {
        const names = {
            'mercedes-s': 'Mercedes Classe S',
            'lincoln': 'Limousine Lincoln',
            'hummer': 'Limousine Hummer'
        };
        return names[code] || 'Non sélectionné';
    }

    // ============================================
    // CALCUL DE L'HEURE DE FIN
    // ============================================

    function calculateEndTime() {
        const startTimeInput = document.getElementById('heure-debut-input');
        const dureeSelect = document.getElementById('duree-select');
        const endTimeInput = document.getElementById('heure-fin-input');

        if (!startTimeInput.value || !dureeSelect.value) {
            endTimeInput.value = '';
            return;
        }

        const startTime = new Date('2000-01-01T' + startTimeInput.value);
        const duree = parseInt(dureeSelect.value);

        startTime.setHours(startTime.getHours() + duree);
        const endTime = startTime.toTimeString().slice(0, 5);

        endTimeInput.value = endTime;
    }

    // ============================================
    // VALIDATION DU FORMULAIRE DE RÉSERVATION
    // ============================================

    function validateReservation(event) {
        event.preventDefault();

        // Récupération des données du formulaire
        const formData = new FormData(event.target);
        const reservationData = {
            nom: formData.get('nom'),
            telephone: formData.get('telephone'),
            email: formData.get('email'),
            service: formData.get('service'),
            vehicule: formData.get('vehicule'),
            passagers: formData.get('passagers'),
            date: formData.get('date'),
            heureDebut: formData.get('heure-debut'),
            heureFin: formData.get('heure-fin'),
            duree: formData.get('duree'),
            lieuDepart: formData.get('lieu-depart'),
            lieuArrivee: formData.get('lieu-arrivee'),
            options: formData.getAll('options[]'),
            message: formData.get('message')
        };

        // Validation des champs requis
        if (!reservationData.nom || !reservationData.telephone || !reservationData.email ||
            !reservationData.vehicule || !reservationData.passagers || !reservationData.date ||
            !reservationData.heureDebut || !reservationData.duree || !reservationData.lieuDepart ||
            !reservationData.lieuArrivee) {

            alert('Veuillez remplir tous les champs obligatoires.');
            return false;
        }

        // Validation du nombre de passagers selon le véhicule
        const maxPassagers = getMaxPassagers(reservationData.vehicule);
        if (parseInt(reservationData.passagers) > maxPassagers) {
            alert(`Ce véhicule ne peut pas accueillir plus de ${maxPassagers} passagers.`);
            return false;
        }

        // Envoi de l'email
        sendReservationEmail(reservationData);

        return false; // Empêche la soumission normale du formulaire
    }

    function getMaxPassagers(vehicule) {
        const maxPassagers = {
            'mercedes-s': 4,
            'lincoln': 10,
            'hummer': 20
        };
        return maxPassagers[vehicule] || 4;
    }

    // ============================================
    // ENVOI D'EMAIL AUTOMATIQUE
    // ============================================

    function sendReservationEmail(data) {
        // Préparation du contenu de l'email
        const subject = `Nouvelle réservation - ${data.vehicule.toUpperCase()} - ${data.date}`;

        const emailBody = `
NOUVELLE DEMANDE DE RÉSERVATION FRLIMOUSINE

Informations du client:
- Nom: ${data.nom}
- Téléphone: ${data.telephone}
- Email: ${data.email}
- Service: ${getServiceName(data.service)}

Détails de la réservation:
- Véhicule: ${getVehiculeName(data.vehicule)}
- Nombre de passagers: ${data.passagers}
- Date: ${formatDate(data.date)}
- Heure de début: ${data.heureDebut}
- Heure de fin: ${data.heureFin}
- Durée: ${data.duree} heures

Trajet:
- Lieu de départ: ${data.lieuDepart}
- Lieu d'arrivée: ${data.lieuArrivee}

${data.options.length > 0 ? 'Options supplémentaires:\n' + data.options.map(opt => '- ' + getOptionName(opt)).join('\n') : 'Aucune option supplémentaire'}

Prix total: ${calculatePriceForEmail(data)}€

${data.message ? 'Message complémentaire:\n' + data.message : 'Aucun message complémentaire'}

---
Email envoyé automatiquement depuis le site FRLimousine
Veuillez contacter le client pour confirmer la disponibilité.
        `.trim();

        // Envoi de l'email via un service tiers (ici simulation)
        console.log('Email envoyé à proayoubfarkh@gmail.com');
        console.log('Sujet:', subject);
        console.log('Contenu:', emailBody);

        // Affichage du message de confirmation
        showConfirmationMessage();

        // Génération du PDF
        generatePDF(data);
    }

    function getServiceName(code) {
        const services = {
            'mariage': 'Mariage',
            'evenement-pro': 'Événement d\'entreprise',
            'transfert-aeroport': 'Transfert aéroport',
            'soiree-privee': 'Soirée privée',
            'autre': 'Autre'
        };
        return services[code] || code;
    }

    function getOptionName(code) {
        const options = {
            'decoration-florale': 'Décoration florale (+50€)',
            'boissons': 'Pack boissons (+30€)',
            'musique': 'Système audio premium (+25€)',
            'chauffeur-costume': 'Chauffeur en costume (+20€)'
        };
        return options[code] || code;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function calculatePriceForEmail(data) {
        const prixVehicule = VEHICULE_PRICES[data.vehicule] * parseInt(data.duree);
        const prixOptions = data.options.reduce((total, option) => total + OPTIONS_PRICES[option], 0);
        return prixVehicule + prixOptions;
    }

    // ============================================
    // GÉNÉRATION DE PDF
    // ============================================

    function generatePDF(data) {
        const pdfContent = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis FRLimousine</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #d42121; }
        .details { margin: 20px 0; }
        .total { font-size: 18px; font-weight: bold; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">FRLimousine</div>
        <h2>Devis de Réservation</h2>
        <p>Date: ${formatDate(data.date)}</p>
    </div>

    <div class="details">
        <h3>Informations Client</h3>
        <p><strong>Nom:</strong> ${data.nom}</p>
        <p><strong>Téléphone:</strong> ${data.telephone}</p>
        <p><strong>Email:</strong> ${data.email}</p>
        <p><strong>Service:</strong> ${getServiceName(data.service)}</p>
    </div>

    <div class="details">
        <h3>Détails de Réservation</h3>
        <table>
            <tr><td class="label">Véhicule:</td><td>${getVehiculeName(data.vehicule)}</td></tr>
            <tr><td class="label">Passagers:</td><td>${data.passagers}</td></tr>
            <tr><td class="label">Date:</td><td>${formatDate(data.date)}</td></tr>
            <tr><td class="label">Heure début:</td><td>${data.heureDebut}</td></tr>
            <tr><td class="label">Heure fin:</td><td>${data.heureFin}</td></tr>
            <tr><td class="label">Durée:</td><td>${data.duree} heures</td></tr>
            <tr><td class="label">Départ:</td><td>${data.lieuDepart}</td></tr>
            <tr><td class="label">Arrivée:</td><td>${data.lieuArrivee}</td></tr>
            ${data.options.length > 0 ? `<tr><td class="label">Options:</td><td>${data.options.map(opt => getOptionName(opt)).join(', ')}</td></tr>` : ''}
        </table>
    </div>

    <div class="total">
        <p><strong>Total: ${calculatePriceForEmail(data)}€</strong></p>
        <p style="font-size: 12px; color: #666;">* Tarifs indicatifs. Devis personnalisé sur demande.</p>
        <p style="font-size: 12px; color: #666;">* Minimum de facturation : 2 heures.</p>
    </div>
</body>
</html>
        `;

        // Ouverture du PDF dans une nouvelle fenêtre (pour impression)
        const pdfWindow = window.open('', '_blank');
        pdfWindow.document.write(pdfContent);
        pdfWindow.document.close();
        pdfWindow.print();
    }

    // ============================================
    // AFFICHAGE DU MESSAGE DE CONFIRMATION
    // ============================================

    function showConfirmationMessage() {
        document.getElementById('confirmation-message').style.display = 'block';

        // Scroll vers le message de confirmation
        document.getElementById('confirmation-message').scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        // Masquer le message après 10 secondes
        setTimeout(() => {
            document.getElementById('confirmation-message').style.display = 'none';
        }, 10000);
    }

    // ============================================
    // VALIDATION EN TEMPS RÉEL
    // ============================================

    function validatePassagers() {
        const vehicule = document.getElementById('vehicule-select').value;
        const passagersInput = document.getElementById('passagers-input');

        if (vehicule && passagersInput.value) {
            const maxPassagers = getMaxPassagers(vehicule);
            if (parseInt(passagersInput.value) > maxPassagers) {
                alert(`Ce véhicule ne peut pas accueillir plus de ${maxPassagers} passagers.`);
                passagersInput.value = maxPassagers;
            }
        }
    }

    // ============================================
    // INITIALISATION DU SYSTÈME DE RÉSERVATION
    // ============================================

    // Écouteurs d'événements pour le calcul automatique
    document.getElementById('vehicule-select').addEventListener('change', calculatePrice);
    document.getElementById('duree-select').addEventListener('change', calculatePrice);
    document.getElementById('duree-select').addEventListener('change', calculateEndTime);
    document.getElementById('heure-debut-input').addEventListener('change', calculateEndTime);
    document.getElementById('passagers-input').addEventListener('change', validatePassagers);

    // Écouteurs pour les options
    document.querySelectorAll('input[name="options[]"]').forEach(option => {
        option.addEventListener('change', calculatePrice);
    });

    console.log('🚀 FRLimousine website loaded - Élégant & Professionnel');
});