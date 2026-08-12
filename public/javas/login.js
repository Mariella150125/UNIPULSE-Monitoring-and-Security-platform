document.addEventListener('DOMContentLoaded', function () {

    /* ------------------------------------------------------
       Fonction réutilisable pour la validation du mot de passe
       ------------------------------------------------------ */
    function initPasswordValidator(passwordInputId, toggleIconId, rulesPanelId, formId) {
        const passwordInput = document.getElementById(passwordInputId);
        const toggleIcon = document.getElementById(toggleIconId);
        const rulesPanel = document.getElementById(rulesPanelId);
        const form = document.getElementById(formId);

        // Si les éléments n'existent pas sur cette page, on arrête
        if (!passwordInput || !rulesPanel || !form) return;

        const rules = {
            length: value => value.length >= 8,
            uppercase: value => /[A-Z]/.test(value),
            lowercase: value => /[a-z]/.test(value),
            number: value => /[0-9]/.test(value),
            special: value => /[^A-Za-z0-9]/.test(value),
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

        // Icône œil
        if (toggleIcon) {
            toggleIcon.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';

                passwordInput.type = isHidden ? 'text' : 'password';

                toggleIcon.classList.toggle('fa-eye', !isHidden);
                toggleIcon.classList.toggle('fa-eye-slash', isHidden);
            });
        }

        /*
         * LOGIN
         *
         * IMPORTANT :
         * On ne bloque PAS l'envoi du formulaire.
         * Laravel doit recevoir le POST pour faire Auth::attempt().
         */
        if (formId === 'login-form') {

            form.addEventListener('submit', function () {
                // Aucun event.preventDefault() ici.
                // Le formulaire est envoyé normalement à Laravel.
            });

            return;
        }

        /*
         * SIGNUP / ACTIVATION
         *
         * Ici on garde la validation frontend.
         */
        form.addEventListener('submit', function (event) {

            updateRulesPanel(passwordInput.value);
            rulesPanel.hidden = false;

            if (!isPasswordValid(passwordInput.value)) {
                event.preventDefault();
                passwordInput.focus();
                return;
            }

            console.log('Formulaire valide (' + formId + ')');
        });
    }


    /* ------------------------------------------------------
       Initialisation LOGIN
       ------------------------------------------------------ */
    initPasswordValidator(
        'login-password',
        'login-toggle-password',
        'login-password-rules',
        'login-form'
    );


    /* ------------------------------------------------------
       Initialisation SIGNUP
       ------------------------------------------------------ */
    initPasswordValidator(
        'signup-password',
        'signup-toggle-password',
        'signup-password-rules',
        'signup-form'
    );


    /* ------------------------------------------------------
       Initialisation ACTIVATION
       ------------------------------------------------------ */
    initPasswordValidator(
        'activation-password',
        'activation-toggle-password',
        'activation-password-rules',
        'activation-form'
    );


    /* ------------------------------------------------------
       Icône œil - confirmation mot de passe
       ------------------------------------------------------ */
    const confirmToggle = document.getElementById('confirmation-toggle-password');
    const confirmInput = document.getElementById('password-confirmation');

    if (confirmToggle && confirmInput) {

        confirmToggle.addEventListener('click', function () {

            const isHidden = confirmInput.type === 'password';

            confirmInput.type = isHidden ? 'text' : 'password';

            confirmToggle.classList.toggle('fa-eye', !isHidden);
            confirmToggle.classList.toggle('fa-eye-slash', isHidden);
        });
    }


    /* ------------------------------------------------------
       Etapes du formulaire SIGNUP
       ------------------------------------------------------ */
    const signupForm = document.getElementById('signup-form');

    if (signupForm) {

        const steps = Array.from(
            signupForm.querySelectorAll('.form-step')
        );

        let currentStep = 1;

        function showStep(stepNumber) {

            steps.forEach(function (step) {

                step.hidden =
                    Number(step.dataset.step) !== stepNumber;

            });

            currentStep = stepNumber;
        }

        function isCurrentStepValid() {

            const currentStepEl = steps.find(
                step => Number(step.dataset.step) === currentStep
            );

            if (!currentStepEl) return false;

            const requiredFields =
                currentStepEl.querySelectorAll('[required]');

            for (const field of requiredFields) {

                if (!field.value.trim()) {

                    field.focus();

                    return false;
                }
            }

            return true;
        }

        signupForm
            .querySelectorAll('.next-btn')
            .forEach(function (btn) {

                btn.addEventListener('click', function () {

                    if (isCurrentStepValid()) {

                        showStep(currentStep + 1);
                    }
                });
            });

        signupForm
            .querySelectorAll('.prev-btn')
            .forEach(function (btn) {

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


    document
        .querySelectorAll('.nav-group-toggle')
        .forEach(function (toggle) {

            toggle.addEventListener('click', function () {

                const group = toggle.closest('.nav-group');

                if (!group) return;

                const items =
                    group.querySelector('.nav-group-items');

                if (!items) return;

                const isOpen =
                    group.classList.contains('open');

                document
                    .querySelectorAll('.nav-group.open')
                    .forEach(function (openGroup) {

                        if (openGroup !== group) {

                            openGroup.classList.remove('open');

                            const openItems =
                                openGroup.querySelector('.nav-group-items');

                            if (openItems) {
                                openItems.style.maxHeight = null;
                            }
                        }
                    });

                if (isOpen) {

                    group.classList.remove('open');

                    items.style.maxHeight = null;

                } else {

                    group.classList.add('open');

                    items.style.maxHeight =
                        items.scrollHeight + 'px';
                }
            });
        });


    /* ------------------------------------------------------
       Graphiques — Dashboard principal
       ------------------------------------------------------ */

    var labels = [
        '22 Juil.',
        '23 Juil.',
        '24 Juil.',
        '25 Juil.',
        '26 Juil.',
        '27 Juil.',
        '28 Juil.'
    ];


    // Alertes critiques
    var ctxAlerts =
        document.getElementById('alertChart');

    if (ctxAlerts) {

        new Chart(ctxAlerts, {

            type: 'line',

            data: {

                labels: labels,

                datasets: [{

                    label: 'Alertes critiques',

                    data: [3, 5, 2, 6, 4, 7, 4],

                    borderColor: '#c0392b',

                    backgroundColor:
                        'rgba(192, 57, 43, 0.08)',

                    fill: true,

                    tension: 0.35,

                    pointRadius: 3
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false
                    }
                },

                scales: {

                    y: {
                        min: 0,
                        grid: {
                            color: '#eef1ef'
                        }
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }


    // Score de sécurité
    var ctxSecurityScore =
        document.getElementById('securityChart');

    if (ctxSecurityScore) {

        new Chart(ctxSecurityScore, {

            type: 'line',

            data: {

                labels: labels,

                datasets: [{

                    label: 'Score de sécurité (%)',

                    data: [78, 80, 82, 81, 85, 88, 92],

                    borderColor: '#56825E',

                    backgroundColor:
                        'rgba(86, 130, 94, 0.08)',

                    fill: true,

                    tension: 0.35,

                    pointRadius: 0
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        display: true,
                        position: 'top'
                    }
                },

                scales: {

                    y: {
                        min: 0,
                        grid: {
                            display: false
                        }
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }


    // Santé des serveurs
    var ctxServerHealth =
        document.getElementById('serverChart');

    if (ctxServerHealth) {

        new Chart(ctxServerHealth, {

            type: 'line',

            data: {

                labels: labels,

                datasets: [{

                    label: 'Santé des serveurs (%)',

                    data: [78, 80, 82, 81, 85, 88, 92],

                    borderColor: '#56825E',

                    backgroundColor:
                        'rgba(86, 130, 94, 0.08)',

                    fill: true,

                    tension: 0.35,

                    pointRadius: 0
                }]
            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        display: true,
                        position: 'top'
                    }
                },

                scales: {

                    y: {
                        min: 0,
                        grid: {
                            display: false
                        }
                    },

                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }


    /* ------------------------------------------------------
       Déconnexion
       ------------------------------------------------------ */

    const logoutLink =
        document.getElementById('logout-link');

    const logoutForm =
        document.getElementById('logout-form');

    if (logoutLink && logoutForm) {

        logoutLink.addEventListener('click', function (e) {

            e.preventDefault();

            logoutForm.submit();
        });
    }

});