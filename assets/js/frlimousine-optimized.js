const VEHICULE_PRICES = {
    'mustang-rouge': 90,
    'mustang-bleu': 95,
    'excalibur': 110,
    'lincoln-limousine': 120,
    'hummer-limousine': 150,
    'mercedes-viano': 85,
};

const OPTIONS_PRICES = {
    'decoration-florale-sur-devis': 0,
    'photographie-video': 100,
};

const VEHICULE_NAMES = {
    'mustang-rouge': 'Mustang Rouge',
    'mustang-bleu': 'Mustang Bleu',
    'excalibur': 'Excalibur',
    'hummer-limousine': 'Hummer Limousine',
    'mercedes-viano': 'Mercedes Viano',
};

const MAX_PASSAGERS = {
    'mustang-rouge': 3,
    'mustang-bleu': 3,
    'excalibur': 2,
    'mercedes-viano': 7,
    'lincoln-limousine': 8,
    'hummer-limousine': 8,
};

function initBurgerMenu() {
    const burgerMenu = document.querySelector('.burger-menu');
    const navLinks = document.querySelector('.nav-links');
    const navLinkItems = document.querySelectorAll('.nav-link');

    if (burgerMenu && navLinks) {
        burgerMenu.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            burgerMenu.classList.toggle('active');
            burgerMenu.setAttribute('aria-expanded', navLinks.classList.contains('active'));
            // Empêche le scroll de la page quand le menu est ouvert
            document.body.classList.toggle('menu-open');
        });

        // Ferme le menu quand on clique sur un lien (pour la navigation sur une seule page)
        navLinkItems.forEach(link => {
            link.addEventListener('click', () => {
                if (navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    burgerMenu.classList.remove('active');
                    burgerMenu.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('menu-open');
                }
            });
        });
    }
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
        'decoration-florale-sur-devis': 'Décoration florale (Sur devis)',
        'photographie-video': 'Service photographie/vidéo professionnel (+100€/h)',
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


function calculatePrice() {
    const vehicule = document.getElementById('vehicule-select')?.value;
    const duree = parseInt(document.getElementById('duree-select')?.value);
    const options = document.querySelectorAll('input[name="options[]"]:checked');

    if (!vehicule || !duree) {
        document.getElementById('price-calculation')?.style.setProperty('display', 'none');
        return;
    }

    const prixVehicule = VEHICULE_PRICES[vehicule] * duree;
    const prixOptions = Array.from(options).reduce((total, option) => total + OPTIONS_PRICES[option.value], 0);
    const prixTotal = prixVehicule + prixOptions;

    // Mise à jour optimisée du DOM
    const calculationDiv = document.getElementById('price-calculation');
    if (calculationDiv) {
        calculationDiv.style.setProperty('display', 'block');
        // Affiche le nom du véhicule et la durée pour plus de clarté
        calculationDiv.querySelector('#selected-vehicule').textContent = `${VEHICULE_NAMES[vehicule]} (${duree}h)`;
        calculationDiv.querySelector('#vehicule-price').textContent = prixVehicule + '€';

        // Masquer la ligne de durée qui est redondante
        calculationDiv.querySelector('#duree-price-row').style.setProperty('display', 'none');
        const optionsRow = calculationDiv.querySelector('#options-price-row');
        const optionsPrice = calculationDiv.querySelector('#options-price');
        if (prixOptions > 0) {
            optionsRow.style.setProperty('display', 'flex');
            optionsPrice.textContent = prixOptions + '€';
        } else {
            optionsRow.style.setProperty('display', 'none');
        }

        calculationDiv.querySelector('#total-price').innerHTML = '<strong>' + prixTotal + '€</strong>';
    }
}



const RATE_LIMIT_STORAGE_KEY = 'frlimousine_form_submissions';
const MAX_SUBMISSIONS_PER_HOUR = 5;
const MAX_SUBMISSIONS_PER_DAY = 20;

function checkRateLimit() {
    const now = Date.now();
    const oneHour = 60 * 60 * 1000;
    const oneDay = 24 * oneHour;

    let submissions = JSON.parse(localStorage.getItem(RATE_LIMIT_STORAGE_KEY) || '[]');

    // Nettoyer les anciennes soumissions
    submissions = submissions.filter(sub => now - sub.timestamp < oneDay);

    // Compter les soumissions récentes
    const recentSubmissions = submissions.filter(sub => now - sub.timestamp < oneHour).length;
    const dailySubmissions = submissions.length;

    if (recentSubmissions >= MAX_SUBMISSIONS_PER_HOUR) {
        alert(`Trop de soumissions récentes. Veuillez attendre ${Math.ceil((oneHour - (now - submissions[submissions.length - 1].timestamp)) / 60000)} minutes.`);
        return false;
    }

    if (dailySubmissions >= MAX_SUBMISSIONS_PER_DAY) {
        alert('Limite de soumissions journalières atteinte. Veuillez réessayer demain.');
        return false;
    }

    // Ajouter la nouvelle soumission
    submissions.push({ timestamp: now });
    localStorage.setItem(RATE_LIMIT_STORAGE_KEY, JSON.stringify(submissions));

    return true;
}

function validateReservation(event) {
    event.preventDefault();

    // Vérifier le rate limiting
    if (!checkRateLimit()) {
        return false;
    }

    const form = event.target;
    const formData = new FormData(form);
    const data = {
        nom: formData.get('nom'),
        telephone: formData.get('telephone'),
        email: formData.get('email'),
        service: formData.get('service'),
        vehicule: formData.get('vehicule'),
        passagers: formData.get('passagers'),
        date: formData.get('date'),
        duree: formData.get('duree'),
        heureDebut: formData.get('heure-debut'), // Ajout de l'heure de début
        lieuDepart: formData.get('lieu-depart'),
        lieuArrivee: formData.get('lieu-arrivee'),
        options: formData.getAll('options[]'),
        message: formData.get('message')
    };

    // Validation rapide
    if (!data.nom || !data.telephone || !data.email || !data.vehicule || !data.passagers || !data.date || !data.duree || !data.lieuDepart || !data.lieuArrivee) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return false;
    }

    // Validation passagers
    if (parseInt(data.passagers) > MAX_PASSAGERS[data.vehicule]) {
        alert(`Ce véhicule ne peut pas accueillir plus de ${MAX_PASSAGERS[data.vehicule]} passagers.`);
        return false;
    }

    sendReservationEmail(data, form); // Passer l'élément 'form' à la fonction suivante
    return false;
}

function sendReservationEmail(data, form) { // Accepter 'form' comme argument
    // Calcul du prix
    const prixVehicule = VEHICULE_PRICES[data.vehicule] * parseInt(data.duree);
    const prixOptions = data.options.reduce((total, option) => total + OPTIONS_PRICES[option], 0);
    const prixTotal = prixVehicule + prixOptions;

    // Template email optimisé
    const templateParams = {
        to_email: 'proayoubfarkh@gmail.com',
        from_name: data.nom,
        client_name: data.nom,
        client_email: data.email,
        client_phone: data.telephone,
        client_service: getServiceName(data.service),
        vehicule_name: VEHICULE_NAMES[data.vehicule],
        vehicule_passagers: data.passagers,
        reservation_date: formatDate(data.date),
        start_time: data.heureDebut,
        duration: data.duree + ' heures',
        departure_location: data.lieuDepart,
        arrival_location: data.lieuArrivee,
        base_price: prixVehicule + '€',
        options_price: prixOptions + '€',
        total_price: prixTotal + '€',
        options_list: data.options.map(opt => '• ' + getOptionName(opt)).join('\n'),
        client_message: data.message || 'Aucun message complémentaire',
        submission_date: new Date().toLocaleString('fr-FR')
    };

    // Bouton de chargement
    const submitBtn = form.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    submitBtn.disabled = true;

    // Générer le contenu HTML du devis
    const pdfContent = generatePDF(data);

    // Envoi via fetch (plus rapide qu'EmailJS)
    fetch('https://frlimousine.ovh/receive-pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            filename: `devis-frlimousine-${data.nom.replace(/\s+/g, '-')}-${Date.now()}.html`,
            content: pdfContent,
            client: {
                nom: data.nom,
                email: data.email,
                telephone: data.telephone,
                service: getServiceName(data.service),
                vehicule: VEHICULE_NAMES[data.vehicule],
                passagers: data.passagers,
                date: formatDate(data.date) + ' à ' + data.heureDebut,
                duree: data.duree + ' heures',
                lieuDepart: data.lieuDepart,
                lieuArrivee: data.lieuArrivee,
                options: data.options.length > 0 ? data.options.map(opt => getOptionName(opt)).join(', ') : 'Aucune',
                prix: prixTotal + '€',
                message: data.message || 'Aucun message'
            },
            timestamp: new Date().toISOString()
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erreur serveur: ${response.status} ${response.statusText}`);
        }
        console.log('✅ Devis PDF généré avec succès!');
        alert('✅ Devis envoyé automatiquement !\n\nLe devis PDF a été généré sur votre serveur.');
    })
    .catch(error => {
        console.error('❌ Erreur envoi:', error);
        alert('⚠️ Envoi échoué\n\nVeuillez nous contacter directement à proayoubfarkh@gmail.com');
    })
    .finally(() => {
        // Restaurer le bouton
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Devis généré !';
        submitBtn.style.background = '#28a745';

        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            submitBtn.style.background = '';
        }, 3000);
    });

    showConfirmationMessage();
}


function generatePDF(data) {
    const prixVehicule = VEHICULE_PRICES[data.vehicule] * parseInt(data.duree);
    const prixOptions = data.options.reduce((total, option) => total + OPTIONS_PRICES[option], 0);
    const prixTotal = prixVehicule + prixOptions;

    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis FRLimousine</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; color: #d42121; }
        .logo { font-size: 24px; font-weight: bold; }
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
            <tr><td class="label">Véhicule:</td><td>${VEHICULE_NAMES[data.vehicule]}</td></tr>
            <tr><td class="label">Passagers:</td><td>${data.passagers}</td></tr>
            <tr><td class="label">Date:</td><td>${formatDate(data.date)}</td></tr>
            <tr><td class="label">Durée:</td><td>${data.duree} heures</td></tr>
            <tr><td class="label">Départ:</td><td>${data.lieuDepart}</td></tr>
            <tr><td class="label">Arrivée:</td><td>${data.lieuArrivee}</td></tr>
            ${data.options.length > 0 ? `<tr><td class="label">Options:</td><td>${data.options.map(opt => getOptionName(opt)).join(', ')}</td></tr>` : ''}
        </table>
    </div>

    <div class="total">
        <p><strong>Total: ${prixTotal}€</strong></p>
        <p style="font-size: 12px; color: #666;">* Tarifs indicatifs. Devis personnalisé sur demande.</p>
    </div>
</body>
</html>`;
}


function calculateEndTime() {
    const startTimeInput = document.getElementById('heure-debut-input');
    const dureeSelect = document.getElementById('duree-select');
    const endTimeInput = document.getElementById('heure-fin-input');

    if (!startTimeInput?.value || !dureeSelect?.value) {
        endTimeInput.value = '';
        return;
    }

    const startTime = new Date('2000-01-01T' + startTimeInput.value);
    const duree = parseInt(dureeSelect.value);
    const endDate = new Date(startTime.getTime()); // Crée une copie
    endDate.setHours(startTime.getHours() + duree);

    startTime.setHours(startTime.getHours() + duree);
    // Gère le changement de jour
    endTimeInput.value = endDate.toTimeString().slice(0, 5);
}

function validatePassagers() {
    const vehicule = document.getElementById('vehicule-select')?.value;
    const passagersInput = document.getElementById('passagers-input');

    if (vehicule && passagersInput?.value) {
        const maxPassagers = MAX_PASSAGERS[vehicule];
        if (parseInt(passagersInput.value) > maxPassagers) {
            alert(`Ce véhicule ne peut pas accueillir plus de ${maxPassagers} passagers.`);
            passagersInput.value = maxPassagers;
        }
    }
}

function showConfirmationMessage() {
    const confirmationDiv = document.getElementById('confirmation-message');
    if (confirmationDiv) {
        confirmationDiv.style.display = 'block';
        confirmationDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => { confirmationDiv.style.display = 'none'; }, 10000);
    }
}

function initCarousel(selector, options = {}) {
    const carouselElement = document.querySelector(selector);
    if (!carouselElement) return;

    const wrapper = carouselElement.querySelector('.carousel-wrapper');
    const slides = carouselElement.querySelectorAll('.carousel-slide');
    const prevBtn = carouselElement.querySelector('.carousel-prev');
    const nextBtn = carouselElement.querySelector('.carousel-next');
    const pagination = carouselElement.querySelector('.carousel-pagination');
    if (!wrapper || slides.length === 0 || !pagination) return;
    
    let currentIndex = 0;
    let slidesPerView = getSlidesPerView();
    let autoPlayInterval;
    let touchStartX = 0;
    let touchEndX = 0;

    // Créer la pagination
    slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.className = 'carousel-pagination-dot';
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel(true); // true pour indiquer une navigation manuelle
        });
        pagination.appendChild(dot);
    });

    function getSlidesPerView() {
        // Toujours afficher un seul élément à la fois
        return 1;
    }

    function updateCarousel(manualNav = false) {
        // Si navigation manuelle, on réinitialise l'autoplay
        if (manualNav && options.autoplay) {
            resetAutoPlay();
        }

        // Animation professionnelle avec transitions élégantes
        slides.forEach((slide, index) => {
            if (index === currentIndex) {
                // Slide entrant - animation d'entrée
                slide.style.display = 'flex';
                slide.classList.add('is-active', 'entering');
                slide.classList.remove('leaving');
            } else {
                // Slide sortant - animation de sortie
                slide.classList.remove('is-active', 'entering');
                slide.classList.add('leaving');
                // Masquer après l'animation
                setTimeout(() => {
                    if (!slide.classList.contains('is-active')) {
                        slide.style.display = 'none';
                        slide.classList.remove('leaving');
                    }
                }, 600); // Durée de l'animation de sortie
            }
        });

        // Mettre à jour la pagination
        const dots = pagination.querySelectorAll('.carousel-pagination-dot');
        dots.forEach((dot, index) => {
            if (index === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });

        // Gérer les boutons
        if (prevBtn && nextBtn) {
            const loop = options.loop !== false; // loop par défaut
            prevBtn.style.opacity = !loop && currentIndex === 0 ? '0.5' : '1';
            prevBtn.style.pointerEvents = !loop && currentIndex === 0 ? 'none' : 'auto';

            const maxIndex = slides.length - slidesPerView;
            nextBtn.style.opacity = !loop && currentIndex >= maxIndex ? '0.5' : '1';
            nextBtn.style.pointerEvents = !loop && currentIndex >= maxIndex ? 'none' : 'auto';
        }
    }

    function nextSlide() {
        const maxIndex = slides.length - slidesPerView;
        currentIndex = (currentIndex < maxIndex) ? currentIndex + 1 : (options.loop !== false ? 0 : currentIndex);
        updateCarousel();
    }

    function prevSlide() {
        const maxIndex = slides.length - slidesPerView;
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = maxIndex; // boucle vers la fin
        }
        updateCarousel();
    }

    // Navigation au clavier
    function handleKeyNavigation(event) {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : slides.length - 1;
            updateCarousel(true);
            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate(30);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            currentIndex = (currentIndex < slides.length - 1) ? currentIndex + 1 : 0;
            updateCarousel(true);
            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate(30);
        }
    }

    // Ajouter la navigation au clavier
    carouselElement.addEventListener('keydown', handleKeyNavigation);

    // Événements des boutons
    if (nextBtn && prevBtn) {
        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex < slides.length - 1) ? currentIndex + 1 : 0;
            updateCarousel(true);
            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate(30);
        });
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : slides.length - 1;
            updateCarousel(true);
            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate(30);
        });
    }

    // Auto-play 10s
    function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, 10000);
    }
    function stopAutoPlay() {
        if (autoPlayInterval) clearInterval(autoPlayInterval);
    }
    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }

    if (options.autoplay) {
        // Pause au survol
        carouselElement.addEventListener('mouseenter', stopAutoPlay);
        carouselElement.addEventListener('mouseleave', startAutoPlay);
    }
    
    // Swipe tactile
    wrapper.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
        stopAutoPlay();
    });
    wrapper.addEventListener('touchmove', (e) => {
        touchEndX = e.touches[0].clientX;
    });
    wrapper.addEventListener('touchend', () => {
        const diff = touchStartX - touchEndX;
        const swipeThreshold = 50;
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) nextSlide(); else prevSlide();
        }
        if (options.autoplay) {
            resetAutoPlay();
        }
    });

    // Zones de tap (mobile): cliquer moitié gauche/droite pour naviguer
    function attachTapZones() {
        if (window.innerWidth <= 768) {
            carouselElement.addEventListener('click', (e) => {
                // ignorer clics sur liens/boutons internes
                if (e.target.closest('a, button')) return;
                const rect = carouselElement.getBoundingClientRect();
                const x = e.clientX - rect.left;
                if (x < rect.width / 2) { prevSlide(); } else { nextSlide(); }
                if (options.autoplay) {
                    resetAutoPlay();
                }
            });
        }
    }
    attachTapZones();

    // Réinitialiser au redimensionnement
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            slidesPerView = getSlidesPerView();
            if (currentIndex > slides.length - slidesPerView) {
                currentIndex = Math.max(0, slides.length - slidesPerView);
            }
            updateCarousel();
            if (options.autoplay) {
                resetAutoPlay();
            }
            attachTapZones();
        }, 250);
    });

    // Initialisation - Afficher immédiatement le premier slide
    slides.forEach((slide, index) => {
        if (index === 0) {
            slide.style.display = 'flex';
            slide.classList.add('is-active');
        } else {
            slide.style.display = 'none';
        }
    });

    // Mettre à jour la pagination initiale
    const dots = pagination.querySelectorAll('.carousel-pagination-dot');
    dots.forEach((dot, index) => {
        if (index === 0) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });

    if (options.autoplay) {
        startAutoPlay();
    }
}


function initSmoothScrolling() {
    document.addEventListener('click', function(e) {
        if (e.target.matches('a[href^="#"]')) {
            const href = e.target.getAttribute('href');
            if (href === '#') return;

            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const headerHeight = document.querySelector('#header')?.offsetHeight || 0;
                const targetPosition = target.offsetTop - headerHeight - 20;

                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        }
    });
}


function initBackToTop() {
    const backToTopBtn = document.getElementById('back-to-top');

    if (!backToTopBtn) return;

    // Afficher/cacher le bouton selon le scroll
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;

        // Afficher quand on a scrollé plus de 50% de la hauteur de la fenêtre
        if (scrollTop > windowHeight / 2) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // Scroll vers le haut au clic
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}


function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px', // Charger 50px avant que l'image soit visible
        threshold: 0.01
    });

    images.forEach(img => imageObserver.observe(img));
}


function initHapticFeedback() {
    // Feedback tactile pour les interactions carrousel
    const carousels = document.querySelectorAll('.fleet-carousel, .testimonials-carousel, .pricing-carousel');

    carousels.forEach(carousel => {
        // Feedback au swipe
        carousel.addEventListener('touchend', () => {
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        });

        // Feedback aux clics boutons
        const buttons = carousel.querySelectorAll('.carousel-btn');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                if (navigator.vibrate) {
                    navigator.vibrate(30);
                }
            });
        });
    });

    // Feedback pour les CTA principaux
    const ctaButtons = document.querySelectorAll('.contact-button, .pricing-btn');
    ctaButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (navigator.vibrate) {
                navigator.vibrate(40);
            }
        });
    });
}


function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('✅ Service Worker registered successfully:', registration.scope);

                    // Gestion des mises à jour
                    registration.addEventListener('updatefound', function() {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Nouvelle version disponible
                                    showUpdateNotification();
                                }
                            });
                        }
                    });
                })
                .catch(function(error) {
                    console.log('❌ Service Worker registration failed:', error);
                });
        });
    }
}

function showUpdateNotification() {
    // Créer une notification de mise à jour
    const updateDiv = document.createElement('div');
    updateDiv.className = 'pwa-update-notification';
    updateDiv.innerHTML = `
        <div class="update-content">
            <p>🚀 Nouvelle version disponible !</p>
            <button onclick="location.reload()">Mettre à jour</button>
            <button onclick="this.parentElement.parentElement.remove()">Plus tard</button>
        </div>
    `;
    updateDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #2c2c2c;
        color: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        font-family: Arial, sans-serif;
    `;
    document.body.appendChild(updateDiv);
}


document.addEventListener('DOMContentLoaded', function() {
    try {
        // Initialiser les fonctionnalités essentielles uniquement
        initSmoothScrolling();
        initBurgerMenu();
        initBackToTop();
        initLazyLoading();
        initHapticFeedback();
        registerServiceWorker();

        // Initialisation des carrousels avec la fonction générique (sans autoplay)
        initCarousel('.fleet-carousel', { autoplay: false, loop: true });
        initCarousel('.testimonials-carousel', { autoplay: false, loop: true });
        initCarousel('.pricing-carousel', { autoplay: false, loop: true });

        // Écouteurs d'événements pour le formulaire
        const vehiculeSelect = document.getElementById('vehicule-select');
        const dureeSelect = document.getElementById('duree-select');
        const heureDebutInput = document.getElementById('heure-debut-input');
        const passagersInput = document.getElementById('passagers-input');

        if (vehiculeSelect) vehiculeSelect.addEventListener('change', calculatePrice);
        if (dureeSelect) dureeSelect.addEventListener('change', calculatePrice);
        if (dureeSelect) dureeSelect.addEventListener('change', calculateEndTime);
        if (heureDebutInput) heureDebutInput.addEventListener('change', calculateEndTime);
        if (passagersInput) passagersInput.addEventListener('change', validatePassagers);

        // Écouteurs pour les options
        document.querySelectorAll('input[name="options[]"]').forEach(option => {
            option.addEventListener('change', calculatePrice);
        });

        // Écouteurs pour les boutons de réservation (auto-remplissage)
        document.querySelectorAll('.pricing-btn[data-vehicule]').forEach(btn => {
            btn.addEventListener('click', function(event) {
                const vehiculeValue = this.getAttribute('data-vehicule');
                if (vehiculeValue) {
                    const vehiculeSelect = document.getElementById('vehicule-select');
                    if (vehiculeSelect) {
                        vehiculeSelect.value = vehiculeValue;
                        // Déclencher l'événement change pour mettre à jour le calcul du prix
                        vehiculeSelect.dispatchEvent(new Event('change'));
                        // Faire défiler vers le formulaire de contact sur toutes les résolutions
                        document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // Retirer la classe preload après chargement
        window.addEventListener('load', function() {
            document.body.classList.remove('is-preload');
        });

        console.log('🚀 FRLimousine website loaded - Optimisé & Performant');
    } catch (error) {
        console.error('Erreur lors de l\'initialisation du site:', error);
        // Fallback : retirer la classe preload même en cas d'erreur
        document.body.classList.remove('is-preload');
    }
});