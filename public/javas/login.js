document.addEventListener('DOMContentLoaded', function () {

    /* ------------------------------------------------------
       Fonction réutilisable pour la validation du mot de passe
       ------------------------------------------------------ */
    function initPasswordValidator(passwordInputId, toggleIconId, rulesPanelId, formId) {
        const passwordInput = document.getElementById(passwordInputId);
        const toggleIcon    = document.getElementById(toggleIconId);
        const rulesPanel    = document.getElementById(rulesPanelId);
        const form          = document.getElementById(formId);

        // Sécurité : si un des éléments n'existe pas, on arrête
        if (!passwordInput || !rulesPanel || !form) return;

        const rules = {
            length:    value => value.length >= 8,
            uppercase: value => /[A-Z]/.test(value),
            lowercase: value => /[a-z]/.test(value),
            number:    value => /[0-9]/.test(value),
            special:   value => /[^A-Za-z0-9]/.test(value),
        };

        function isPasswordValid(value) {
            return Object.values(rules).every(test => test(value));
        }

        function updateRulesPanel(value) {
            Object.keys(rules).forEach(function (key) {
                const li = rulesPanel.querySelector('[data-rule="' + key + '"]');
                if (!li) return;
                li.classList.toggle('valid', rules[key](value));
            });
        }

        // Affichage des règles au focus
        passwordInput.addEventListener('focus', function () {
            rulesPanel.hidden = false;
        });

        // Mise à jour en temps réel
        passwordInput.addEventListener('input', function () {
            updateRulesPanel(passwordInput.value);
        });

        // Icône œil (si elle existe)
        if (toggleIcon) {
            toggleIcon.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye', !isHidden);
                toggleIcon.classList.toggle('fa-eye-slash', isHidden);
            });
        }

        // Blocage de l'envoi du formulaire si invalide
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            updateRulesPanel(passwordInput.value);
            rulesPanel.hidden = false;

            if (!isPasswordValid(passwordInput.value)) {
                passwordInput.focus();
                return;
            }

            console.log('Formulaire valide (' + formId + ') :', {
                password: passwordInput.value,
            });

            // Quand le backend Laravel sera prêt, remplace ce bloc par :
            // if (!isPasswordValid(passwordInput.value)) {
            //     event.preventDefault();
            // }
            // Sinon le formulaire s'envoie normalement
        });
    }

    /* ------------------------------------------------------
       Initialisation pour le LOGIN
       ------------------------------------------------------ */
    initPasswordValidator('login-password', 'login-toggle-password', 'login-password-rules', 'login-form');

    /* ------------------------------------------------------
       Initialisation pour l'INSCRIPTION (SIGNUP)
       ------------------------------------------------------ */
    initPasswordValidator('signup-password', 'signup-toggle-password', 'signup-password-rules', 'signup-form');

    /* ------------------------------------------------------
       Initialisation pour l'ACTIVATION (Nouveau mot de passe)
       ------------------------------------------------------ */
    initPasswordValidator('activation-password', 'activation-toggle-password', 'activation-password-rules', 'activation-form');

    // Gestion de l'icône œil UNIQUEMENT pour le champ de confirmation
    const confirmToggle = document.getElementById('confirmation-toggle-password');
    const confirmInput  = document.getElementById('password-confirmation');

    if (confirmToggle && confirmInput) {
        confirmToggle.addEventListener('click', function () {
            const isHidden = confirmInput.type === 'password';
            confirmInput.type = isHidden ? 'text' : 'password';
            confirmToggle.classList.toggle('fa-eye', !isHidden);
            confirmToggle.classList.toggle('fa-eye-slash', isHidden);
        });
    }

    /* ------------------------------------------------------
       Gestion des étapes du formulaire d'inscription
       ------------------------------------------------------ */
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        const steps = Array.from(signupForm.querySelectorAll('.form-step'));
        let currentStep = 1;

        function showStep(stepNumber) {
            steps.forEach(function (step) {
                step.hidden = Number(step.dataset.step) !== stepNumber;
            });
            currentStep = stepNumber;
        }

        function isCurrentStepValid() {
            const currentStepEl = steps.find(step => Number(step.dataset.step) === currentStep);
            if (!currentStepEl) return false;

            const requiredFields = currentStepEl.querySelectorAll('[required]');
            for (const field of requiredFields) {
                if (!field.value.trim()) {
                    field.focus();
                    return false;
                }
            }
            return true;
        }

        signupForm.querySelectorAll('.next-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (isCurrentStepValid()) showStep(currentStep + 1);
            });
        });

        signupForm.querySelectorAll('.prev-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showStep(currentStep - 1);
            });
        });

        showStep(1);
    }

    /* ------------------------------------------------------
       Sidebar & Menu
       ------------------------------------------------------ */
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    if (sidebar && menuToggle) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
        });
    }

    document.querySelectorAll('.nav-group-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const group = toggle.closest('.nav-group');
            if (!group) return;
            const items = group.querySelector('.nav-group-items');
            if (!items) return;
            const isOpen = group.classList.contains('open');

            // Ferme les autres groupes ouverts
            document.querySelectorAll('.nav-group.open').forEach(function (openGroup) {
                if (openGroup !== group) {
                    openGroup.classList.remove('open');
                    openGroup.querySelector('.nav-group-items').style.maxHeight = null;
                }
            });

            if (isOpen) {
                group.classList.remove('open');
                items.style.maxHeight = null;
            } else {
                group.classList.add('open');
                items.style.maxHeight = items.scrollHeight + 'px';
            }
        });
    });

    /* ------------------------------------------------------
       Graphiques — Dashboard principal
       ------------------------------------------------------ */
    var labels = ['22 Juil.', '23 Juil.', '24 Juil.', '25 Juil.', '26 Juil.', '27 Juil.', '28 Juil.'];

    // Alertes critiques
    var ctxAlerts = document.getElementById('alertChart');
    if (ctxAlerts) {
        new Chart(ctxAlerts, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Alertes critiques',
                    data: [3, 5, 2, 6, 4, 7, 4],
                    borderColor: '#c0392b',
                    backgroundColor: 'rgba(192, 57, 43, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, grid: { color: '#eef1ef' } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // Score de sécurité
    var ctxSecurityScore = document.getElementById('securityChart');
    if (ctxSecurityScore) {
        new Chart(ctxSecurityScore, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Score de sécurité (%)',
                    data: [78, 80, 82, 81, 85, 88, 92],
                    borderColor: '#56825E',
                    backgroundColor: 'rgba(86, 130, 94, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: { min: 0, grid: { display: false } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // Santé des serveurs
    var ctxServerHealth = document.getElementById('serverChart');
    if (ctxServerHealth) {
        new Chart(ctxServerHealth, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Santé des serveurs (%)',
                    data: [78, 80, 82, 81, 85, 88, 92],
                    borderColor: '#56825E',
                    backgroundColor: 'rgba(86, 130, 94, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: { min: 0, grid: { display: false } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    /* ------------------------------------------------------
       Graphiques — Page Applications
       ------------------------------------------------------ */

    // Donut : Répartition par environnement
    var ctxDonut = document.getElementById('envDonutChart');
    if (ctxDonut) {
        var envData = [
            { label: 'Production',    value: 6, color: '#1d4a40' },
            { label: 'Staging',       value: 3, color: '#e08e3e' },
            { label: 'Développement', value: 2, color: '#8a9490' },
            { label: 'QA',            value: 1, color: '#56825E' }
        ];
        var envTotal = envData.reduce(function (s, d) { return s + d.value; }, 0);

        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: envData.map(function (d) { return d.label; }),
                datasets: [{
                    data: envData.map(function (d) { return d.value; }),
                    backgroundColor: envData.map(function (d) { return d.color; }),
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#163d34',
                        titleFont: { family: 'Arial Rounded MT', size: 13 },
                        bodyFont: { family: 'Arial Rounded MT', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                var pct = ((context.parsed / envTotal) * 100).toFixed(0);
                                return ' ' + context.label + ' : ' + context.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Légende personnalisée à côté du donut
        var legendContainer = document.getElementById('donutLegend');
        if (legendContainer) {
            envData.forEach(function (item) {
                var pct = ((item.value / envTotal) * 100).toFixed(0);
                var row = document.createElement('div');
                row.className = 'donut-legend-item';
                row.innerHTML =
                    '<span class="donut-legend-dot" style="background:' + item.color + '"></span>' +
                    '<span class="donut-legend-label">' + item.label + '</span>' +
                    '<span class="donut-legend-value">' + item.value + ' (' + pct + '%)</span>';
                legendContainer.appendChild(row);
            });
        }
    }

    // Ligne : Disponibilité des applications
    var ctxAvailability = document.getElementById('availabilityChart');
    if (ctxAvailability) {
        var weekDays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

        new Chart(ctxAvailability, {
            type: 'line',
            data: {
                labels: weekDays,
                datasets: [
                    {
                        label: 'Company Website',
                        data: [99.9, 99.8, 99.9, 100, 99.7, 99.9, 99.9],
                        borderColor: '#1d4a40',
                        backgroundColor: 'rgba(29,74,64,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                    },
                    {
                        label: 'REST API',
                        data: [99.7, 99.6, 99.8, 99.5, 99.7, 99.8, 99.7],
                        borderColor: '#56825E',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                    },
                    {
                        label: 'Payment Gateway',
                        data: [99.8, 99.9, 99.7, 99.8, 99.9, 99.8, 99.8],
                        borderColor: '#e08e3e',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        borderDash: [5, 4],
                    }
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        min: 94,
                        max: 101,
                        ticks: {
                            font: { family: 'Arial Rounded MT', size: 11 },
                            color: '#8a9490',
                            callback: function (v) { return v + '%'; }
                        },
                        grid: { color: '#e3e7e4' },
                    },
                    x: {
                        ticks: {
                            font: { family: 'Arial Rounded MT', size: 11 },
                            color: '#8a9490',
                        },
                        grid: { display: false },
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            font: { family: 'Arial Rounded MT', size: 12 },
                            color: '#163d34',
                        }
                    },
                    tooltip: {
                        backgroundColor: '#163d34',
                        titleFont: { family: 'Arial Rounded MT', size: 13 },
                        bodyFont: { family: 'Arial Rounded MT', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.dataset.label + ' : ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }
        // Bouton Copier le code curl (Page API & Webhooks)
    var copyBtn = document.getElementById('copyCurl');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var codeBlock = copyBtn.closest('.panel').querySelector('.api-code-block');
            var text = codeBlock ? codeBlock.innerText : '';
            
            navigator.clipboard.writeText(text).then(function () {
                copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copié !';
                setTimeout(function () {
                    copyBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copier';
                }, 2000);
            });
        });
    }
        /* ------------------------------------------------------
       Topbar : Menu Langue (utilise tes variables .language et .lang-item)
       ------------------------------------------------------ */
    var langContainer = document.querySelector('.language');
    var langActiveBtn = document.querySelector('.lang-item.lang-active');

    if (langContainer && langActiveBtn) {
        // Ouverture/fermeture au clic sur le bouton principal
        langActiveBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            langContainer.classList.toggle('open');
        });

        // Gestion du clic sur les options du dropdown
        document.querySelectorAll('.lang-dropdown .lang-item').forEach(function(option) {
            option.addEventListener('click', function() {
                // Mettre à jour le texte du bouton principal
                langActiveBtn.innerHTML = this.textContent + ' <i class="fa-solid fa-chevron-down"></i>';
                
                // Fermer le menu
                langContainer.classList.remove('open');

                // --- PRÉPARATION POUR LARAVEL ---
                // window.location.href = '/change-lang/' + this.dataset.lang;
            });
        });
    }

    // Fermer le menu langue si on clique ailleurs
    document.addEventListener('click', function(e) {
        if (langContainer && !langContainer.contains(e.target)) {
            langContainer.classList.remove('open');
        }
    });

}); // <-- Unique fermeture du DOMContentLoaded — RIEN après cette ligne