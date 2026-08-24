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

                const li = rulesPanel.querySelector(
                    '[data-rule="' + key + '"]'
                );

                if (!li) return;

                li.classList.toggle(
                    'valid',
                    rules[key](value)
                );
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

                const isHidden =
                    passwordInput.type === 'password';

                passwordInput.type =
                    isHidden ? 'text' : 'password';

                toggleIcon.classList.toggle(
                    'fa-eye',
                    !isHidden
                );

                toggleIcon.classList.toggle(
                    'fa-eye-slash',
                    isHidden
                );
            });
        }

        /*
         * LOGIN
         *
         * IMPORTANT :
         * On ne bloque PAS l'envoi du formulaire.
         */
        if (formId === 'login-form') {

            form.addEventListener('submit', function () {
                // Le formulaire est envoyé normalement à Laravel.
            });

            return;
        }

        /*
         * SIGNUP / ACTIVATION
         */
        form.addEventListener('submit', function (event) {

            updateRulesPanel(passwordInput.value);
            rulesPanel.hidden = false;

            if (!isPasswordValid(passwordInput.value)) {

                event.preventDefault();
                passwordInput.focus();

                return;
            }

            console.log(
                'Formulaire valide (' + formId + ')'
            );
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
    const confirmToggle =
        document.getElementById(
            'confirmation-toggle-password'
        );

    const confirmInput =
        document.getElementById(
            'password-confirmation'
        );

    if (confirmToggle && confirmInput) {

        confirmToggle.addEventListener('click', function () {

            const isHidden =
                confirmInput.type === 'password';

            confirmInput.type =
                isHidden ? 'text' : 'password';

            confirmToggle.classList.toggle(
                'fa-eye',
                !isHidden
            );

            confirmToggle.classList.toggle(
                'fa-eye-slash',
                isHidden
            );
        });
    }


    /* ------------------------------------------------------
       Etapes du formulaire SIGNUP
       ------------------------------------------------------ */
    const signupForm =
        document.getElementById('signup-form');

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
                step =>
                    Number(step.dataset.step) === currentStep
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
    const sidebar =
        document.querySelector('.sidebar');

    const menuToggle =
        document.querySelector('.menu-toggle');

    if (sidebar && menuToggle) {

        menuToggle.addEventListener('click', function () {

            sidebar.classList.toggle('collapsed');
        });
    }

    document
        .querySelectorAll('.nav-group-toggle')
        .forEach(function (toggle) {

            toggle.addEventListener('click', function () {

                const group =
                    toggle.closest('.nav-group');

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
                                openGroup.querySelector(
                                    '.nav-group-items'
                                );

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

    var ctxAlerts =
        document.getElementById('alertChart');

    if (ctxAlerts) {

        new Chart(ctxAlerts, {

            type: 'line',

            data: {
                labels: labels,

                datasets: [{
                    label: 'Alertes critiques',

                    data: [
                        3, 5, 2, 6, 4, 7, 4
                    ],

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


    var ctxSecurityScore =
        document.getElementById('securityChart');

    if (ctxSecurityScore) {

        new Chart(ctxSecurityScore, {

            type: 'line',

            data: {

                labels: labels,

                datasets: [{

                    label: 'Score de sécurité (%)',

                    data: [
                        78, 80, 82, 81, 85, 88, 92
                    ],

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


    var ctxServerHealth =
        document.getElementById('serverChart');

    if (ctxServerHealth) {

        new Chart(ctxServerHealth, {

            type: 'line',

            data: {

                labels: labels,

                datasets: [{

                    label: 'Santé des serveurs (%)',

                    data: [
                        78, 80, 82, 81, 85, 88, 92
                    ],

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


    /* ------------------------------------------------------
       Recherche en temps réel (Utilisateurs)
       ------------------------------------------------------ */
    const searchInput =
        document.querySelector(
            'input[name="search"]'
        );

    if (searchInput) {

        let timer;

        searchInput.addEventListener(
            'input',
            function () {

                clearTimeout(timer);

                timer = setTimeout(function () {

                    const form =
                        searchInput.closest('form');

                    if (form) {
                        form.submit();
                    }

                }, 500);
            }
        );
    }


    /* ------------------------------------------------------
       Modals (Ouverture / Fermeture)
       ------------------------------------------------------ */

    document
        .querySelectorAll('[data-modal-open]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const modalId =
                    this.dataset.modalOpen;

                const modal =
                    document.getElementById(modalId);

                if (modal) {

                    modal.classList.add('open');

                    document.body.classList.add(
                        'modal-open'
                    );
                }
            });
        });


    document
        .querySelectorAll('[data-modal-close]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const modalId =
                    this.dataset.modalClose;

                const modal =
                    document.getElementById(modalId);

                if (modal) {

                    modal.classList.remove('open');

                    document.body.classList.remove(
                        'modal-open'
                    );
                }
            });
        });


    /* ------------------------------------------------------
       APPLICATIONS
       Gestion hébergement
       ------------------------------------------------------ */

    const hostingSelect =
        document.getElementById('is_hosted');

    const serverField =
        document.getElementById('server-field');

    const portField =
        document.getElementById('port-field');

    const deploymentPathField =
        document.getElementById(
            'deployment-path-field'
        );

    const serverSelect =
        document.getElementById('server_id');

    const portInput =
        document.getElementById('port');

    const deploymentPathInput =
        document.getElementById('deployment_path');


    /*
     * On vérifie que les éléments existent.
     *
     * C'est important parce que ce fichier JS
     * est utilisé sur plusieurs pages.
     */
    if (
        hostingSelect &&
        serverField &&
        portField &&
        deploymentPathField &&
        serverSelect
    ) {

        function updateHostingFields() {

            const isHosted =
                hostingSelect.value === '1';


            if (isHosted) {

                /*
                 * APPLICATION HÉBERGÉE
                 */

                serverField.style.display = 'block';

                portField.style.display = 'block';

                deploymentPathField.style.display =
                    'block';

                // Le serveur devient obligatoire
                serverSelect.required = true;


            } else {

                /*
                 * APPLICATION NON HÉBERGÉE
                 */

                serverField.style.display = 'none';

                portField.style.display = 'none';

                deploymentPathField.style.display =
                    'none';

                // Le serveur n'est plus obligatoire
                serverSelect.required = false;

                // Nettoyage des valeurs
                serverSelect.value = '';

                if (portInput) {
                    portInput.value = '';
                }

                if (deploymentPathInput) {
                    deploymentPathInput.value = '';
                }
            }
        }


        /*
         * Lorsque l'utilisateur change
         * Oui / Non
         */
        hostingSelect.addEventListener(
            'change',
            updateHostingFields
        );


        /*
         * État initial
         */
        updateHostingFields();
    }
    /* ------------------------------------------------------
   Menu utilisateur Topbar
   ------------------------------------------------------ */

    const userMenuToggle = document.getElementById('user-menu-toggle');
    const userDropdown = document.getElementById('user-dropdown');

    if (userMenuToggle && userDropdown) {

        userMenuToggle.addEventListener('click', function (event) {

            event.stopPropagation();

            userDropdown.classList.toggle('open');

        });


        document.addEventListener('click', function (event) {

            if (
                !userDropdown.contains(event.target) &&
                !userMenuToggle.contains(event.target)
            ) {
                userDropdown.classList.remove('open');
            }

        });

    }
        document.addEventListener('DOMContentLoaded', function () {

        const toggle = document.getElementById('date-range-toggle');
        const menu = document.getElementById('date-range-menu');

        if (!toggle || !menu) {
            console.log('Calendrier : éléments introuvables');
            return;
        }

        console.log('Calendrier : OK');

        toggle.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            menu.classList.toggle('open');

            console.log('Menu calendrier:', menu.classList.contains('open'));

        });


        menu.querySelectorAll('[data-range]').forEach(function (button) {

            button.addEventListener('click', function (event) {

                event.preventDefault();
                event.stopPropagation();

                const range = this.dataset.range;

                console.log('Période sélectionnée:', range);

                const label = document.getElementById('date-range-label');

                if (range === 'today') {
                    label.textContent = "Aujourd'hui";
                }

                if (range === '7') {
                    label.textContent = "7 derniers jours";
                }

                if (range === '30') {
                    label.textContent = "30 derniers jours";
                }

                if (range === '90') {
                    label.textContent = "90 derniers jours";
                }

                if (range === 'custom') {
                    alert('Période personnalisée');
                }

                menu.classList.remove('open');

            });

        });


        document.addEventListener('click', function (event) {

            if (
                !menu.contains(event.target) &&
                !toggle.contains(event.target)
            ) {
                menu.classList.remove('open');
            }

        });

    });
        document.addEventListener('DOMContentLoaded', function () {

        const language = document.querySelector('.language');

        if (!language) {
            return;
        }

        const languageButton = language.querySelector('.lang-active');
        const languageOptions = language.querySelectorAll('[data-lang]');

        // Ouvrir / fermer le menu
        languageButton.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            language.classList.toggle('open');

        });

        // Choisir une langue
        languageOptions.forEach(function (button) {

            button.addEventListener('click', function (event) {

                event.preventDefault();
                event.stopPropagation();

                const lang = this.dataset.lang;

                console.log('Langue sélectionnée :', lang);

                language.classList.remove('open');

            });

        });

        // Fermer en cliquant ailleurs
        document.addEventListener('click', function () {

            language.classList.remove('open');

        });

    });
});