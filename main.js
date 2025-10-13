/**
 * FRLimousine - JavaScript Principal
 * ================================================
 * Version unifiée, optimisée et corrigée pour de meilleures performances et une maintenance simplifiée.
 * Ce fichier remplace frlimousine.js, frlimousine-optimized.js et l'usage de jQuery.
 */

// ============================================
// CONFIGURATION CENTRALISÉE
// ============================================

const VEHICULE_PRICES = {
    'mustang-rouge': 90,
    'mustang-bleu': 95,
    'excalibur': 110,
    'mercedes-viano': 85
};

const OPTIONS_PRICES = {
    'decoration-florale': 50,
    'boissons': 30,
    'musique': 25,
    'chauffeur-costume': 20,
    'photographie-video': 100 // Note: ce prix est par heure
};

const VEHICULE_NAMES = {
    'mustang-rouge': 'Mustang Rouge',
    'mustang-bleu': 'Mustang Bleu',
    'excalibur': 'Excalibur',
    'mercedes-viano': 'Mercedes Viano'
};

const MAX_PASSAGERS = {
    'mustang-rouge': 4,
    'mustang-bleu': 4,
    'excalibur': 4,
    'mercedes-viano': 8
};

// ============================================
// FONCTIONS UTILITAIRES
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
        'chauffeur-costume': 'Chauffeur en costume (+20€)',
        'photographie-video': 'Service photographie/vidéo professionnel (+100€/heure)'
    };
    return options[code] || code;
}

function formatDate(dateString) {
    if (!dateString) return 'Date non spécifiée';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getFormData(form) {
    const formData = new FormData(form);
    const duree = parseInt(formData.get('duree')) || 0;
    const isPhotoService = formData.getAll('options[]').includes('photographie-video');

    let prixOptions = Array.from(formData.getAll('options[]')).reduce((total, option) => {
        if (option !== 'photographie-video') {
            return total + (OPTIONS_PRICES[option] || 0);
        }
        return total;
    }, 0);

    if (isPhotoService) {
        prixOptions += (OPTIONS_PRICES['photographie-video'] || 0) * duree;
    }

    const prixVehicule = (VEHICULE_PRICES[formData.get('vehicule')] || 0) * duree;
    const prixTotal = prixVehicule + prixOptions;

    return {
        nom: formData.get('nom'),
        telephone: formData.get('telephone'),
        email: formData.get('email'),
        service: formData.get('service'),
        vehicule: formData.get('vehicule'),
        passagers: formData.get('passagers'),
        date: formData.get('date'),
        heureDebut: formData.get('heure-debut'),
        duree: duree,
        lieuDepart: formData.get('lieu-depart'),
        lieuArrivee: formData.get('lieu-arrivee'),
        options: formData.getAll('options[]'),
        message: formData.get('message'),
        prix: {
            vehicule: prixVehicule,
            options: prixOptions,
            total: prixTotal
        }
    };
}

// ============================================
// LOGIQUE DU FORMULAIRE DE RÉSERVATION
// ============================================

function calculatePrice() {
    const form = document.querySelector('.booking-form');
    if (!form) return;

    const data = getFormData(form);

    const calculationDiv = document.getElementById('price-calculation');
    if (!data.vehicule || !data.duree) {
        calculationDiv.style.display = 'none';
        return;
    }

    calculationDiv.style.display = 'block';
    calculationDiv.querySelector('#selected-vehicule').textContent = VEHICULE_NAMES[data.vehicule] || 'N/A';
    calculationDiv.querySelector('#vehicule-price').textContent = data.prix.vehicule + '€';
    calculationDiv.querySelector('#selected-duree').textContent = data.duree;
    // Prix de base par heure (corrigé)
    calculationDiv.querySelector('#duree-price').textContent = (VEHICULE_PRICES[data.vehicule] || 0) + '€';

    const optionsRow = calculationDiv.querySelector('#options-price-row');
    if (data.prix.options > 0) {
        optionsRow.style.display = 'flex';
        calculationDiv.querySelector('#options-price').textContent = data.prix.options + '€';
    } else {
        optionsRow.style.display = 'none';
    }

    calculationDiv.querySelector('#total-price').innerHTML = '<strong>' + data.prix.total + '€</strong>';
}

function calculateEndTime() {
    const startTimeInput = document.getElementById('heure-debut-input');
    const dureeSelect = document.getElementById('duree-select');
    const endTimeInput = document.getElementById('heure-fin-input');

    if (!startTimeInput.value || !dureeSelect.value) {
        endTimeInput.value = '';
        return;
    }

    const startTime = new Date(`1970-01-01T${startTimeInput.value}`);
    const duree = parseInt(dureeSelect.value);

    startTime.setHours(startTime.getHours() + duree);
    endTimeInput.value = startTime.toTimeString().slice(0, 5);
}

function validatePassagers() {
    const vehicule = document.getElementById('vehicule-select').value;
    const passagersInput = document.getElementById('passagers-input');

    if (vehicule && passagersInput.value) {
        const max = MAX_PASSAGERS[vehicule] || 20;
        if (parseInt(passagersInput.value) > max) {
            alert(`Ce véhicule ne peut pas accueillir plus de ${max} passagers.`);
            passagersInput.value = max;
        }
    }
}

function showConfirmationMessage() {
    const confirmationDiv = document.getElementById('confirmation-message');
    if (confirmationDiv) {
        confirmationDiv.style.display = 'block';
        confirmationDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            confirmationDiv.style.display = 'none';
        }, 10000);
    }
}

function validateReservation(event) {
    event.preventDefault();
    const form = event.target;
    const data = getFormData(form);

    // Validation
    if (!data.nom || !data.telephone || !data.email || !data.vehicule || !data.passagers || !data.date || !data.duree || !data.lieuDepart || !data.lieuArrivee) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return false;
    }
    if (parseInt(data.passagers) > (MAX_PASSAGERS[data.vehicule] || 20)) {
        alert(`Ce véhicule ne peut pas accueillir plus de ${MAX_PASSAGERS[data.vehicule]} passagers.`);
        return false;
    }

    sendReservationEmail(form, data);
    return false;
}

function sendReservationEmail(form, data) {
    const submitBtn = form.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    submitBtn.disabled = true;

    const pdfContent = generatePdfContent(data);

    fetch('receive-pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            filename: `Devis_FRLimousine_${data.nom.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.html`,
            content: pdfContent,
            client: {
                nom: data.nom,
                email: data.email,
                telephone: data.telephone,
                service: getServiceName(data.service),
                vehicule: VEHICULE_NAMES[data.vehicule],
                passagers: data.passagers,
                date: formatDate(data.date),
                duree: data.duree + ' heures',
                prix: data.prix.total + '€',
                message: data.message || 'Aucun message'
            },
            timestamp: new Date().toISOString()
        })
    })
    .then(response => {
        if (!response.ok) {
            // Gestion améliorée des erreurs HTTP
            if (response.status === 400) {
                throw new Error('Données de formulaire invalides. Vérifiez vos informations.');
            } else if (response.status === 500) {
                throw new Error('Erreur serveur interne. Veuillez réessayer plus tard.');
            } else if (response.status === 403) {
                throw new Error('Accès refusé. Veuillez contacter le support.');
            } else {
                throw new Error(`Erreur serveur (${response.status}): ${response.statusText}`);
            }
        }
        return response.json();
    })
    .then(result => {
        console.log('✅ Devis envoyé avec succès!', result);
        showConfirmationMessage();
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Envoyé !';
        submitBtn.style.background = '#28a745';
    })
    .catch(error => {
        console.error('❌ Erreur lors de l\'envoi:', error);

        // Message d'erreur plus spécifique selon le type d'erreur
        let errorMessage = '⚠️ L\'envoi a échoué. Veuillez nous contacter directement.';
        if (error.message.includes('Données de formulaire invalides')) {
            errorMessage = '⚠️ ' + error.message + ' Vérifiez que tous les champs sont correctement remplis.';
        } else if (error.message.includes('Erreur serveur')) {
            errorMessage = '⚠️ ' + error.message + ' Nos équipes ont été notifiées.';
        } else if (error.message.includes('réseau') || error.message.includes('fetch')) {
            errorMessage = '⚠️ Problème de connexion. Vérifiez votre connexion internet et réessayez.';
        }

        alert(errorMessage);
        submitBtn.innerHTML = '<i class="fas fa-times"></i> Échec';
        submitBtn.style.background = '#d32f2f';
    })
    .finally(() => {
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            submitBtn.style.background = '';
        }, 5000);
    });
}

// ============================================
// GÉNÉRATION DE PDF
// ============================================

function generatePdfContent(data) {
    const optionsListHtml = data.options.length > 0 ?
        `<tr>
            <td><strong>Options supplémentaires</strong></td>
            <td>${data.options.map(opt => getOptionName(opt)).join('<br>')}</td>
        </tr>` : '';

    const messageHtml = data.message ?
        `<tr>
            <td><strong>Message complémentaire</strong></td>
            <td>${data.message}</td>
        </tr>` : '';

    return `
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Devis FRLimousine</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; color: #333; }
            .pdf-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #d42121; }
            .company-logo { font-size: 36px; font-weight: 900; color: #d42121; margin: 0; text-transform: uppercase; }
            .company-info { text-align: right; font-size: 14px; }
            .company-name { font-size: 18px; font-weight: bold; color: #d42121; margin-bottom: 10px; }
            .section-title { font-size: 18px; font-weight: bold; color: #d42121; margin: 20px 0 15px 0; border-bottom: 2px solid #d42121; padding-bottom: 5px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f8f8f8; font-weight: bold; color: #d42121; }
            .total-row strong { font-size: 18px; color: #d42121; }
            .footer { margin-top: 50px; padding-top: 20px; border-top: 2px solid #d42121; text-align: center; font-size: 14px; color: #666; }
        </style>
    </head>
    <body>
        <div class="pdf-header">
            <div><h1 class="company-logo">FRLimousine</h1></div>
            <div class="company-info">
                <div class="company-name">FRLimousine</div>
                <strong>Tél :</strong> 06 12 94 05 40<br>
                <strong>Email :</strong> contact@transvoyage.fr<br>
                <strong>Site :</strong> www.frlimousine.com
            </div>
        </div>
        <div style="text-align: center; margin: 30px 0;">
            <h2 style="color: #d42121; font-size: 28px; margin: 0;">DEVIS DE RÉSERVATION</h2>
            <p style="font-size: 16px; color: #666;">Date d'émission : ${new Date().toLocaleDateString('fr-FR')}</p>
        </div>
        <div class="section-title">Informations Client</div>
        <p><strong>À l'attention de :</strong> ${data.nom}<br><strong>Email :</strong> ${data.email} | <strong>Téléphone :</strong> ${data.telephone}</p>

        <div class="section-title">Détails de Réservation</div>
        <table>
            <tr><td><strong>Service souhaité</strong></td><td>${getServiceName(data.service)}</td></tr>
            <tr><td><strong>Véhicule</strong></td><td>${VEHICULE_NAMES[data.vehicule]}</td></tr>
            <tr><td><strong>Nombre de passagers</strong></td><td>${data.passagers}</td></tr>
            <tr><td><strong>Date</strong></td><td>${formatDate(data.date)}</td></tr>
            <tr><td><strong>Heure de début</strong></td><td>${data.heureDebut}</td></tr>
            <tr><td><strong>Durée</strong></td><td>${data.duree} heures</td></tr>
            <tr><td><strong>Lieu de départ</strong></td><td>${data.lieuDepart}</td></tr>
            <tr><td><strong>Lieu d'arrivée</strong></td><td>${data.lieuArrivee}</td></tr>
            ${optionsListHtml}
            ${messageHtml}
        </table>

        <div class="section-title">Récapitulatif Financier</div>
        <table>
            <tr><td>Prestation véhicule (${data.duree}h)</td><td style="text-align: right;">${data.prix.vehicule}€</td></tr>
            ${data.prix.options > 0 ? `<tr><td>Options supplémentaires</td><td style="text-align: right;">${data.prix.options}€</td></tr>` : ''}
            <tr class="total-row" style="background-color: #f8f8f8;"><td><strong>TOTAL TTC</strong></td><td style="text-align: right;"><strong>${data.prix.total}€</strong></td></tr>
        </table>

        <div class="footer">
            <p><strong>FRLimousine</strong> - Service de location de limousines de luxe à Paris</p>
            <p>Devis valable 30 jours. Ce document ne constitue pas une confirmation de réservation.</p>
        </div>
    </body>
    </html>`;
}

function generatePDF() {
    const form = document.querySelector('.booking-form');
    if (!form) return;

    const data = getFormData(form);
    const pdfContent = generatePdfContent(data);

    const pdfWindow = window.open('', '_blank');
    pdfWindow.document.write(pdfContent);
    pdfWindow.document.close();
    pdfWindow.print();
}

// ============================================
// SLIDER D'AVIS CLIENTS
// ============================================

let currentTestimonialIndex = 0;
let testimonialInterval;

function showTestimonial(index) {
    const testimonials = document.querySelectorAll('.testimonial-card');
    const dots = document.querySelectorAll('.dot');
    if (testimonials.length === 0) return;

    currentTestimonialIndex = (index + testimonials.length) % testimonials.length;

    testimonials.forEach(t => t.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));

    testimonials[currentTestimonialIndex].classList.add('active');
    dots[currentTestimonialIndex].classList.add('active');
}

function nextTestimonial() {
    showTestimonial(currentTestimonialIndex + 1);
}

function previousTestimonial() {
    showTestimonial(currentTestimonialIndex - 1);
}

function goToTestimonial(index) {
    showTestimonial(index);
}

// ============================================
// GESTIONNAIRES D'ÉVÉNEMENTS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le slider de témoignages
    initTestimonialsSlider();

    // Gestionnaires d'événements pour le formulaire
    const vehiculeSelect = document.getElementById('vehicule-select');
    const dureeSelect = document.getElementById('duree-select');
    const heureDebutInput = document.getElementById('heure-debut-input');
    const passagersInput = document.getElementById('passagers-input');
    const bookingForm = document.querySelector('.booking-form');
    const pdfButton = document.querySelector('.btn-secondary'); // Cible le bouton "Générer PDF"

    if (vehiculeSelect) vehiculeSelect.addEventListener('change', calculatePrice);
    if (dureeSelect) dureeSelect.addEventListener('change', calculatePrice);
    if (dureeSelect) dureeSelect.addEventListener('change', calculateEndTime);
    if (heureDebutInput) heureDebutInput.addEventListener('change', calculateEndTime);
    if (passagersInput) passagersInput.addEventListener('change', validatePassagers);

    // Gestionnaire pour la soumission du formulaire
    if (bookingForm) bookingForm.addEventListener('submit', validateReservation);
    // Gestionnaire pour le bouton de génération de PDF
    if (pdfButton) pdfButton.addEventListener('click', generatePDF);
    // Gestionnaires pour les options
    document.querySelectorAll('input[name="options[]"]').forEach(option => {
        option.addEventListener('change', calculatePrice);
    });

    // Retirer la classe preload après chargement
    window.addEventListener('load', function() {
        document.body.classList.remove('is-preload');
    });

    console.log('🚀 FRLimousine website loaded - Optimisé & Performant');
});

function initTestimonialsSlider() {
    const testimonials = document.querySelectorAll('.testimonial-card');
    if (testimonials.length === 0) return;

    // Démarrer le slider automatique
    showTestimonial(0);
    testimonialInterval = setInterval(nextTestimonial, 10000);

    // Gestionnaires d'événements pour les contrôles du slider
    document.addEventListener('click', function(e) {
        if (e.target.matches('.slider-btn.prev')) {
            clearInterval(testimonialInterval);
            previousTestimonial();
            testimonialInterval = setInterval(nextTestimonial, 10000);
        } else if (e.target.matches('.slider-btn.next')) {
            clearInterval(testimonialInterval);
            nextTestimonial();
            testimonialInterval = setInterval(nextTestimonial, 10000);
        } else if (e.target.matches('.dot')) {
            clearInterval(testimonialInterval);
            const index = parseInt(e.target.dataset.index);
            goToTestimonial(index);
            testimonialInterval = setInterval(nextTestimonial, 10000);
        }
    });
}