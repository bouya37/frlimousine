/**
 * FRLimousine - JavaScript Élégant & Professionnel
 * ================================================
 * Interactions essentielles pour un site haut de gamme
 */

// ============================================
//    FONCTIONS GLOBALES (ACCESSIBLES DEPUIS HTML)
// ============================================

// ============================================
//    VALIDATION DU FORMULAIRE DE RÉSERVATION
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
//    ENVOI D'EMAIL AVEC EMAILJS
// ============================================

function sendReservationEmail(data) {
    // Préparation des données pour le template EmailJS
    const templateParams = {
        // Destinataire
        to_email: 'proayoubfarkh@gmail.com',

        // Informations du client
        from_name: data.nom,
        client_name: data.nom,
        client_email: data.email,
        client_phone: data.telephone,
        client_service: getServiceName(data.service),

        // Détails de réservation
        vehicule_name: getVehiculeName(data.vehicule),
        vehicule_passagers: data.passagers,
        reservation_date: formatDate(data.date),
        start_time: data.heureDebut,
        end_time: data.heureFin,
        duration: data.duree + ' heures',
        departure_location: data.lieuDepart,
        arrival_location: data.lieuArrivee,

        // Prix et options
        base_price: (VEHICULE_PRICES[data.vehicule] * parseInt(data.duree)) + '€',
        options_price: data.options.length > 0 ?
            data.options.reduce((total, option) => total + OPTIONS_PRICES[option], 0) + '€' : '0€',
        total_price: calculatePriceForEmail(data) + '€',
        options_list: data.options.length > 0 ?
            data.options.map(opt => '• ' + getOptionName(opt)).join('\n') : 'Aucune option',

        // Message complémentaire
        client_message: data.message || 'Aucun message complémentaire',

        // Métadonnées
        submission_date: new Date().toLocaleString('fr-FR')
    };

    // Affichage du loader
    const submitBtn = document.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    submitBtn.disabled = true;

    // Générer le PDF et l'envoyer automatiquement
    const pdfContent = generatePDF(data);

    // Créer un nom de fichier unique
    const filename = `Devis_FRLimousine_${data.nom.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.html`;

    // Préparer les données pour l'envoi automatique
    const pdfData = {
        filename: filename,
        content: pdfContent,
        client: {
            nom: data.nom,
            email: data.email,
            telephone: data.telephone,
            service: getServiceName(data.service),
            vehicule: getVehiculeName(data.vehicule),
            passagers: data.passagers,
            date: formatDate(data.date),
            duree: data.duree + ' heures',
            prix: calculatePriceForEmail(data) + '€',
            message: data.message || 'Aucun message'
        },
        timestamp: new Date().toISOString()
    };

    // Solution automatique : OVH (remplacez par votre URL OVH)
    const webhookUrl = 'https://frlimousine.ovh/receive-pdf.php'; // Remplacez par votre URL OVH

    // Envoyer automatiquement via fetch
    fetch(webhookUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(pdfData)
    })
    .then(response => {
        console.log('✅ PDF envoyé automatiquement avec succès!');
        alert('✅ Devis envoyé automatiquement !\n\nLe PDF a été envoyé directement à votre serveur.\nVous le recevrez dans quelques secondes.');
    })
    .catch(error => {
        console.error('❌ Erreur envoi automatique:', error);

        // Solution de secours : téléchargement local
        const blob = new Blob([JSON.stringify(pdfData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename.replace('.html', '.json');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        alert('⚠️ Envoi automatique échoué\n\nLe fichier a été téléchargé en local.\nVeuillez nous l\'envoyer manuellement à proayoubfarkh@gmail.com');
    });
    // Succès - Ouvrir le client email
    console.log('✅ PDF généré avec succès !');

    // Remettre le bouton à l'état normal
    submitBtn.innerHTML = '<i class="fas fa-check"></i> Devis généré !';
    submitBtn.style.background = '#28a745';

    // Afficher le message de confirmation
    showConfirmationMessage();

    // Reset après 3 secondes
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        submitBtn.style.background = '';
    }, 3000);
}

// ============================================
//    FONCTIONS UTILITAIRES GLOBALES
// ============================================

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

function generatePDF(data) {
    const pdfContent = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis FRLimousine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }

        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #d42121;
        }

        .logo-section {
            flex: 1;
        }

        .company-logo {
            font-size: 36px;
            font-weight: 900;
            color: #d42121;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .company-info {
            flex: 1;
            text-align: right;
            font-size: 14px;
            line-height: 1.8;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #d42121;
            margin-bottom: 10px;
        }

        .company-details {
            color: #333;
        }

        .content {
            margin: 30px 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #d42121;
            margin: 20px 0 15px 0;
            border-bottom: 2px solid #d42121;
            padding-bottom: 5px;
        }

        .info-grid {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }

        .info-column {
            flex: 1;
        }

        .info-item {
            margin: 8px 0;
        }

        .info-label {
            font-weight: bold;
            color: #333;
        }

        .info-value {
            color: #666;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #d42121;
        }

        .total-section {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
        }

        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #d42121;
        }

        .footer {
            margin-top: 50px;
            padding: 20px;
            border-top: 2px solid #d42121;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .attention {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .notes {
            font-size: 12px;
            color: #666;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- En-tête avec logo et coordonnées -->
    <div class="pdf-header">
        <div class="logo-section">
            <h1 class="company-logo">FRLimousine</h1>
        </div>
        <div class="company-info">
            <div class="company-name">FRLimousine</div>
            <div class="company-details">
                <strong>Tél :</strong> 06 12 94 05 40<br>
                <strong>Email :</strong> contact@transvoyage.fr<br>
                <strong>Site :</strong> www.frlimousine.com<br>
                <strong>Service :</strong> 24h/24 - 7j/7
            </div>
        </div>
    </div>

    <!-- Titre du document -->
    <div style="text-align: center; margin: 30px 0;">
        <h2 style="color: #d42121; font-size: 28px; margin: 0;">DEVIS DE RÉSERVATION</h2>
        <p style="font-size: 16px; color: #666; margin: 10px 0 0 0;">Date d'émission : ${new Date().toLocaleDateString('fr-FR')}</p>
    </div>

    <!-- Section d'attention -->
    <div class="attention">
        <strong>À l'attention de ${data.nom}</strong><br>
        ${data.email} | ${data.telephone}
    </div>

    <!-- Informations client -->
    <div class="content">
        <div class="section-title">Informations Client</div>
        <div class="info-grid">
            <div class="info-column">
                <div class="info-item">
                    <span class="info-label">Nom complet :</span>
                    <span class="info-value">${data.nom}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone :</span>
                    <span class="info-value">${data.telephone}</span>
                </div>
            </div>
            <div class="info-column">
                <div class="info-item">
                    <span class="info-label">Email :</span>
                    <span class="info-value">${data.email}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Service souhaité :</span>
                    <span class="info-value">${getServiceName(data.service)}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails de réservation -->
    <div class="content">
        <div class="section-title">Détails de Réservation</div>
        <table>
            <tr>
                <td><strong>Véhicule sélectionné</strong></td>
                <td>${getVehiculeName(data.vehicule)}</td>
            </tr>
            <tr>
                <td><strong>Nombre de passagers</strong></td>
                <td>${data.passagers} personnes</td>
            </tr>
            <tr>
                <td><strong>Date de réservation</strong></td>
                <td>${formatDate(data.date)}</td>
            </tr>
            <tr>
                <td><strong>Heure de début</strong></td>
                <td>${data.heureDebut}</td>
            </tr>
            <tr>
                <td><strong>Heure de fin</strong></td>
                <td>${data.heureFin}</td>
            </tr>
            <tr>
                <td><strong>Durée totale</strong></td>
                <td>${data.duree} heures</td>
            </tr>
            <tr>
                <td><strong>Lieu de départ</strong></td>
                <td>${data.lieuDepart}</td>
            </tr>
            <tr>
                <td><strong>Lieu d'arrivée</strong></td>
                <td>${data.lieuArrivee}</td>
            </tr>
            ${data.options.length > 0 ? `
            <tr>
                <td><strong>Options supplémentaires</strong></td>
                <td>${data.options.map(opt => getOptionName(opt)).join('<br>')}</td>
            </tr>` : ''}
            ${data.message ? `
            <tr>
                <td><strong>Message complémentaire</strong></td>
                <td>${data.message}</td>
            </tr>` : ''}
        </table>
    </div>

    <!-- Calcul du prix -->
    <div class="content">
        <div class="section-title">Récapitulatif Financier</div>
        <table>
            <tr>
                <td>Véhicule (${getVehiculeName(data.vehicule)}) - ${data.duree}h × ${VEHICULE_PRICES[data.vehicule]}€</td>
                <td style="text-align: right;">${VEHICULE_PRICES[data.vehicule] * parseInt(data.duree)}€</td>
            </tr>
            ${data.options.length > 0 ? `
            <tr>
                <td>Options supplémentaires</td>
                <td style="text-align: right;">${data.options.reduce((total, option) => total + OPTIONS_PRICES[option], 0)}€</td>
            </tr>` : ''}
            <tr style="background-color: #ffeaa7;">
                <td><strong>TOTAL TTC</strong></td>
                <td style="text-align: right; font-size: 18px; font-weight: bold; color: #d42121;">${calculatePriceForEmail(data)}€</td>
            </tr>
        </table>
    </div>

    <!-- Notes importantes -->
    <div class="notes">
        * Tarifs indicatifs. Devis personnalisé sur demande.<br>
        * Minimum de facturation : 2 heures.<br>
        * Suppléments possibles pour prestations spéciales (décorations, attente, etc.)<br>
        * Toute réservation implique l'acceptation de nos conditions générales de vente.
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>FRLimousine</strong> - Service de location de limousines de luxe à Paris</p>
        <p>Tél : 06 12 94 05 40 | Email : contact@transvoyage.fr | Site : www.frlimousine.com</p>
        <p>Document généré le ${new Date().toLocaleString('fr-FR')} - Devis valable 30 jours</p>
    </div>
</body>
</html>
    `;

    // Ouverture du PDF dans une nouvelle fenêtre (pour impression)
    const pdfWindow = window.open('', '_blank');
    pdfWindow.document.write(pdfContent);
    pdfWindow.document.close();
    pdfWindow.print();

    // Retourner le contenu PDF pour EmailJS
    return pdfContent;
}

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
//    CONFIGURATION EMAILJS SUPPRIMÉE
// ============================================
// Remplacée par la solution mailto automatique
// Plus besoin de service tiers !

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
    // CONFIGURATION EMAILJS SUPPRIMÉE
    // ============================================
    // Remplacée par la solution mailto automatique

    // ============================================
    // ENVOI D'EMAIL AVEC EMAILJS
    // ============================================

    function sendReservationEmail(data) {
        // Préparation des données pour le template EmailJS FRLimousine
        // ⚠️ Variables doivent correspondre EXACTEMENT à votre template
        const templateParams = {
            // Destinataire
            to_email: 'proayoubfarkh@gmail.com',
    
            // Informations du client (variables qui fonctionnent)
            client_email: data.email,
            client_service: getServiceName(data.service),
            vehicule_passagers: data.passagers,
            reservation_date: formatDate(data.date),
    
            // Nouvelles variables avec les bons noms pour votre template
            user_os: 'Windows 10.0',
            user_platform: 'Microsoft Windows',
            user_browser: 'Chrome',
            user_country: 'France',
            user_ip: '2b0f737346da3cd1b6585f25f213a087',
            user_referrer: 'https://ayoub-informatique.netlify.app',
            from_name: data.nom,
            client_name: data.nom,
            client_phone: data.telephone,
    
            // Détails de réservation
            vehicule_name: getVehiculeName(data.vehicule),
            start_time: data.heureDebut,
            end_time: data.heureFin,
            duration: data.duree + ' heures',
            departure_location: data.lieuDepart,
            arrival_location: data.lieuArrivee,
    
            // Prix et options
            base_price: (VEHICULE_PRICES[data.vehicule] * parseInt(data.duree)) + '€',
            options_price: data.options.length > 0 ?
                data.options.reduce((total, option) => total + OPTIONS_PRICES[option], 0) + '€' : '0€',
            total_price: calculatePriceForEmail(data) + '€',
            options_list: data.options.length > 0 ?
                data.options.map(opt => '• ' + getOptionName(opt)).join('\n') : 'Aucune option',
    
            // Message complémentaire
            client_message: data.message || 'Aucun message complémentaire',
            message: data.message || 'Aucun message complémentaire',
    
            // Métadonnées
            submission_date: new Date().toLocaleString('fr-FR')
        };

        // Affichage du loader
        const submitBtn = document.querySelector('.submit-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        submitBtn.disabled = true;

        // Générer le PDF et l'envoyer automatiquement
        const pdfContent = generatePDF(data);
    
        // Créer un nom de fichier unique
        const filename = `Devis_FRLimousine_${data.nom.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.html`;
    
        // Préparer les données pour l'envoi automatique
        const pdfData = {
            filename: filename,
            content: pdfContent,
            client: {
                nom: data.nom,
                email: data.email,
                telephone: data.telephone,
                service: getServiceName(data.service),
                vehicule: getVehiculeName(data.vehicule),
                passagers: data.passagers,
                date: formatDate(data.date),
                duree: data.duree + ' heures',
                prix: calculatePriceForEmail(data) + '€',
                message: data.message || 'Aucun message'
            },
            timestamp: new Date().toISOString()
        };
    
        // Solution 1: Utiliser un webhook gratuit (remplacez par votre URL)
        const webhookUrl = 'https://webhook.site/YOUR_WEBHOOK_URL'; // Remplacez par votre URL
    
        // Envoyer automatiquement via fetch
        fetch(webhookUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(pdfData)
        })
        .then(response => {
            console.log('✅ PDF envoyé automatiquement avec succès!');
            alert('✅ Devis envoyé automatiquement !\n\nLe PDF a été envoyé directement à votre serveur.\nVous le recevrez dans quelques secondes.');
        })
        .catch(error => {
            console.error('❌ Erreur envoi automatique:', error);
    
            // Solution de secours : téléchargement local
            const blob = new Blob([JSON.stringify(pdfData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename.replace('.html', '.json');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
    
            alert('⚠️ Envoi automatique échoué\n\nLe fichier a été téléchargé en local.\nVeuillez nous l\'envoyer manuellement à proayoubfarkh@gmail.com');
        });
        // Succès - Ouvrir le client email
        console.log('✅ PDF généré avec succès !');
    
        // Remettre le bouton à l'état normal
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Devis généré !';
        submitBtn.style.background = '#28a745';
    
        // Afficher le message de confirmation
        showConfirmationMessage();
    
        // Reset après 3 secondes
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            submitBtn.style.background = '';
        }, 3000);
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

    // ============================================
    // FONCTION POUR LES BOUTONS "EN DÉCOUVRIR PLUS"
    // ============================================

    window.discoverVehicle = function(vehicleType) {
        // Animation de scroll vers la section contact
        document.getElementById('contact').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Pré-remplir le formulaire selon le véhicule
        setTimeout(() => {
            const vehiculeSelect = document.getElementById('vehicule-select');
            const passagersInput = document.getElementById('passagers-input');

            if (vehiculeSelect && passagersInput) {
                // Sélectionner le véhicule selon le type
                switch(vehicleType) {
                    case 'hummer':
                        vehiculeSelect.value = 'hummer';
                        passagersInput.value = '15'; // Valeur par défaut pour Hummer
                        break;
                    case 'lincoln':
                        vehiculeSelect.value = 'lincoln';
                        passagersInput.value = '8'; // Valeur par défaut pour Lincoln
                        break;
                    case 'mercedes':
                        vehiculeSelect.value = 'mercedes-s';
                        passagersInput.value = '3'; // Valeur par défaut pour Mercedes
                        break;
                }

                // Recalculer le prix
                calculatePrice();

                // Mettre le focus sur le formulaire
                vehiculeSelect.focus();
            }
        }, 800); // Délai pour laisser le temps du scroll
    };

    console.log('🚀 FRLimousine website loaded - Élégant & Professionnel');
});