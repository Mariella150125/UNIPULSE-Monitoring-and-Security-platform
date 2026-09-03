document.addEventListener('DOMContentLoaded', function () {
    const loadingText = document.getElementById('loading-text');
    const progressBar = document.getElementById('progress-bar');
    const progressValue = document.getElementById('progress-value');
    
    // On cible l'image du logo pour vérifier son vrai chargement
    const logoImg = document.querySelector('.splash-logo img');

    const messages = [
        "Initialisation de la plateforme...", // 0-20%
        "Chargement des services...",         // 20-40%
        "Vérification des connexions...",     // 40-60%
        "Initialisation du monitoring...",    // 60-80%
        "Chargement des modules de sécurité...", // 80-95%
        "Plateforme prête..."                 // 100%
    ];

    let progress = 0;
    let targetProgress = 15; // On commence à viser 15% pendant l'init de base

    // --- ÉVÉNEMENT RÉEL 1 : L'image du logo est chargée ---
    if (logoImg.complete) {
        // Si l'image est déjà dans le cache du navigateur
        targetProgress = 50;
    } else {
        logoImg.addEventListener('load', () => {
            targetProgress = 50; // L'image vient de finir de télécharger
        });
    }

    // --- ÉVÉNEMENT RÉEL 2 : Toute la page (CSS, JS, polices) est chargée ---
    window.addEventListener('load', () => {
        targetProgress = 90; // On monte à 90%
        
        // On simule les dernières "vérifications de sécurité" avant de dire 100%
        setTimeout(() => {
            targetProgress = 100;
        }, 800); 
    });

    // --- BOUCLE D'ANIMATION FLUIDE ---
    const animInterval = setInterval(() => {
        // Si on n'a pas atteint l'objectif réel, on avance
        if (progress < targetProgress) {
            
            // Plus on est proche de l'objectif, plus on ralentit (effet naturel de chargement)
            if (targetProgress - progress < 10) {
                progress += 1;
            } else {
                progress += 2;
            }
            
            progress = Math.min(progress, 100);
            
            // Mise à jour visuelle
            progressBar.style.width = `${progress}%`;
            progressValue.textContent = `${progress}%`;

            // Mise à jour du texte selon le vrai pourcentage
            const msgIndex = Math.min(Math.floor(progress / 20), messages.length - 1);
            if (messages[msgIndex] !== loadingText.textContent) {
                loadingText.textContent = messages[msgIndex];
            }
        }
        
        // --- REDIRECTION : Seulement quand on atteint VRAIMENT 100% ---
        if (progress >= 100 && targetProgress === 100) {
            clearInterval(animInterval);
            
            setTimeout(() => {
                window.location.href = "/login";
            }, 500);
        }
    }, 40); // Rafraîchissement de la barre toutes les 40ms pour un effet fluide

    // --- SÉCURITÉ : Si quelque chose bloque le chargement ---
    // Au bout de 5 secondes, si la page n'est toujours pas à 100%, on force l'avancée
    setTimeout(() => {
        if (targetProgress < 100) {
            console.warn("Chargement long détecté. Forçage de la progression...");
            targetProgress = 100;
        }
    }, 5000);
});