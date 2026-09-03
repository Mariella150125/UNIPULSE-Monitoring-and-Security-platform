document.addEventListener('DOMContentLoaded', function () {

    /* ==========================================================
       VALIDATION DU MOT DE PASSE
       ========================================================== */

    function initPasswordValidator(
        passwordInputId,
        toggleIconId,
        rulesPanelId,
        formId
    ) {

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


    /* ==========================================================
       INITIALISATION LOGIN
       ========================================================== */

    initPasswordValidator(
        'login-password',
        'login-toggle-password',
        'login-password-rules',
        'login-form'
    );


    /* ==========================================================
       INITIALISATION SIGNUP
       ========================================================== */

    initPasswordValidator(
        'signup-password',
        'signup-toggle-password',
        'signup-password-rules',
        'signup-form'
    );


    /* ==========================================================
       INITIALISATION ACTIVATION
       ========================================================== */

    initPasswordValidator(
        'activation-password',
        'activation-toggle-password',
        'activation-password-rules',
        'activation-form'
    );


    /* ==========================================================
       ICÔNE ŒIL - CONFIRMATION MOT DE PASSE
       ========================================================== */

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


    /* ==========================================================
       ÉTAPES DU FORMULAIRE SIGNUP
       ========================================================== */

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


    /* ==========================================================
       SIDEBAR & MENU
       ========================================================== */

    const sidebar =
        document.querySelector('.sidebar');

    const menuToggle =
        document.querySelector('.menu-toggle');

    if (sidebar && menuToggle) {

        menuToggle.addEventListener('click', function () {

            sidebar.classList.toggle('collapsed');
        });
    }


    /* ==========================================================
       GROUPES DU MENU
       ========================================================== */

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


    /* ==========================================================
       GRAPHIQUES — DASHBOARD PRINCIPAL
       ========================================================== */

    const labels = [
        '22 Juil.',
        '23 Juil.',
        '24 Juil.',
        '25 Juil.',
        '26 Juil.',
        '27 Juil.',
        '28 Juil.'
    ];


    /* -------------------------
       Graphique Alertes
       ------------------------- */
    /* ------------------------------------------------------
   Donut — Répartition des serveurs par environnement
   ------------------------------------------------------ */

    var envDonutCanvas =
        document.getElementById('envDonutChart');

    if (envDonutCanvas) {

        fetch('/dashboard/environment-chart')
            .then(function (response) {

                if (!response.ok) {
                    throw new Error(
                        'Erreur lors du chargement des environnements'
                    );
                }

                return response.json();
            })
            .then(function (result) {

                var labels = result.labels || [];
                var data = result.data || [];

                /* ------------------------------------------
                Si aucune donnée
                ------------------------------------------ */

                if (labels.length === 0 || data.length === 0) {

                    document.getElementById('donutLegend').innerHTML =
                        '<p style="color: var(--text-muted);">' +
                        'Aucune donnée disponible' +
                        '</p>';

                    return;
                }


                /* ------------------------------------------
                Création du donut
                ------------------------------------------ */

                new Chart(envDonutCanvas, {

                    type: 'doughnut',

                    data: {

                        labels: labels,

                        datasets: [{

                            data: data,

                            backgroundColor: [
                                '#56825E',
                                '#1d4a40',
                                '#8fae94',
                                '#c9d8cb',
                                '#6f8f77',
                                '#b5c7b8'
                            ],

                            borderWidth: 0,

                            hoverOffset: 5
                        }]
                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        cutout: '68%',

                        plugins: {

                            legend: {
                                display: false
                            },

                            tooltip: {

                                callbacks: {

                                    label: function (context) {

                                        var label =
                                            context.label || '';

                                        var value =
                                            context.parsed || 0;

                                        return ' ' +
                                            label +
                                            ' : ' +
                                            value +
                                            ' serveur(s)';
                                    }
                                }
                            }
                        }
                    }
                });


                /* ------------------------------------------
                Création de la légende personnalisée
                ------------------------------------------ */

                var legend =
                    document.getElementById('donutLegend');

                legend.innerHTML = '';

                labels.forEach(function (label, index) {

                    var item =
                        document.createElement('div');

                    item.className = 'legend-item';

                    item.innerHTML =

                        '<span class="legend-color" ' +
                        'style="background-color:' +
                        getEnvironmentColor(index) +
                        '"></span>' +

                        '<span class="legend-label">' +
                        label +
                        '</span>' +

                        '<span class="legend-value">' +
                        data[index] +
                        '</span>';

                    legend.appendChild(item);
                });


                function getEnvironmentColor(index) {

                    var colors = [
                        '#56825E',
                        '#1d4a40',
                        '#8fae94',
                        '#c9d8cb',
                        '#6f8f77',
                        '#b5c7b8'
                    ];

                    return colors[
                        index % colors.length
                    ];
                }

            })

            .catch(function (error) {

                console.error(
                    'Erreur donut environnement :',
                    error
                );

                document.getElementById('donutLegend').innerHTML =
                    '<p style="color: var(--red);">' +
                    'Impossible de charger les données.' +
                    '</p>';
            });
    }
    const ctxAlerts =
        document.getElementById('alertChart');

    if (ctxAlerts && typeof Chart !== 'undefined') {

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


    /* -------------------------
       Graphique Score sécurité
       ------------------------- */

    const ctxSecurityScore =
        document.getElementById('securityChart');

    if (ctxSecurityScore && typeof Chart !== 'undefined') {

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


    /* -------------------------
       Graphique Santé serveurs
       ------------------------- */

    const ctxServerHealth =
        document.getElementById('serverChart');

    if (ctxServerHealth && typeof Chart !== 'undefined') {

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


    /* ==========================================================
       DÉCONNEXION
       ========================================================== */

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


    /* ==========================================================
       RECHERCHE EN TEMPS RÉEL — UTILISATEURS
       ========================================================== */

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


    /* ==========================================================
       MODALS — OUVERTURE / FERMETURE
       ========================================================== */

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


    /* ==========================================================
       APPLICATIONS
       Gestion hébergement
       ========================================================== */

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

                /* APPLICATION HÉBERGÉE */

                serverField.style.display = 'block';

                portField.style.display = 'block';

                deploymentPathField.style.display =
                    'block';

                // Le serveur devient obligatoire
                serverSelect.required = true;


            } else {

                /* APPLICATION NON HÉBERGÉE */

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


        hostingSelect.addEventListener(
            'change',
            updateHostingFields
        );


        // État initial
        updateHostingFields();
    }


    /* ==========================================================
       MENU UTILISATEUR TOPBAR
       ========================================================== */

    const userMenuToggle =
        document.getElementById('user-menu-toggle');

    const userDropdown =
        document.getElementById('user-dropdown');

    if (userMenuToggle && userDropdown) {

        userMenuToggle.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

                userDropdown.classList.toggle('open');
            }
        );


        document.addEventListener(
            'click',
            function (event) {

                if (
                    !userDropdown.contains(event.target) &&
                    !userMenuToggle.contains(event.target)
                ) {

                    userDropdown.classList.remove('open');
                }
            }
        );
    }

    /* ==========================================================
   CALENDRIER / PÉRIODE DU DASHBOARD
   ========================================================== */

    const dateRangeToggle = document.getElementById('date-range-toggle');
    const dateRangeMenu   = document.getElementById('date-range-menu');

    if (dateRangeToggle && dateRangeMenu) {

        // Ouvrir / fermer
        dateRangeToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            dateRangeMenu.classList.toggle('open');
        });

        // Cliquer sur une option → envoyer au backend
        dateRangeMenu.querySelectorAll('[data-range]').forEach(function (button) {

            button.addEventListener('click', function (event) {

                event.preventDefault();
                event.stopPropagation();

                const range = this.dataset.range;

                // Période personnalisée → ouvrir un datepicker (à toi d'implémenter)
                if (range === 'custom') {
                    dateRangeMenu.classList.remove('open');
                    // TODO: ouvrir un datepicker ici
                    return;
                }

                // Construire l'URL avec le paramètre range
                const url = new URL(window.location.href);
                url.searchParams.set('range', range);
                window.location.href = url.toString();
            });
        });

        // Fermer en cliquant ailleurs
        document.addEventListener('click', function (event) {
            if (
                !dateRangeMenu.contains(event.target) &&
                !dateRangeToggle.contains(event.target)
            ) {
                dateRangeMenu.classList.remove('open');
            }
        });
    }

    


    /* ==========================================================
       MENU LANGUE
       ========================================================== */

    const language =
        document.querySelector('.language');

    if (language) {

        const languageButton =
            language.querySelector('.lang-active');

        const languageOptions =
            language.querySelectorAll('[data-lang]');


        if (languageButton) {

            // Ouvrir / fermer le menu
            languageButton.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    language.classList.toggle('open');
                }
            );
        }


        // Choisir une langue
        languageOptions.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function (event) {

                        event.preventDefault();
                        event.stopPropagation();

                        const lang =
                            this.dataset.lang;

                        console.log(
                            'Langue sélectionnée :',
                            lang
                        );

                        language.classList.remove(
                            'open'
                        );
                    }
                );
            }
        );


        // Fermer en cliquant ailleurs
        document.addEventListener(
            'click',
            function () {

                language.classList.remove('open');
            }
        );
    }


    /* ==========================================================
       CONNECTEURS
       ========================================================== */


    /* ----------------------------------------------------------
       Recherche / filtres
       ---------------------------------------------------------- */

    let connectorSearchTimeout;

    const connectorSearch =
        document.getElementById('connector-search');

    const filterType =
        document.getElementById('filter-type');

    const filterStatus =
        document.getElementById('filter-status');


    function applyConnectorFilters() {

        const params =
            new URLSearchParams();

        const search =
            connectorSearch
                ? connectorSearch.value.trim()
                : '';

        const type =
            filterType
                ? filterType.value
                : '';

        const status =
            filterStatus
                ? filterStatus.value
                : '';


        if (search) {
            params.set('search', search);
        }

        if (type) {
            params.set('type', type);
        }

        if (status) {
            params.set('status', status);
        }


        const qs =
            params.toString();

        window.location =
            '/connecteurs' +
            (qs ? '?' + qs : '');
    }


    if (connectorSearch) {

        connectorSearch.addEventListener(
            'input',
            function () {

                clearTimeout(
                    connectorSearchTimeout
                );

                connectorSearchTimeout =
                    setTimeout(
                        applyConnectorFilters,
                        400
                    );
            }
        );
    }


    if (filterType) {

        filterType.addEventListener(
            'change',
            applyConnectorFilters
        );
    }


    if (filterStatus) {

        filterStatus.addEventListener(
            'change',
            applyConnectorFilters
        );
    }


    /* ----------------------------------------------------------
       MODALE CONNECTEUR
       ---------------------------------------------------------- */

    function openCreateModal() {

        const modal =
            document.getElementById(
                'connector-modal'
            );

        const form =
            document.getElementById(
                'connector-form'
            );

        const method =
            document.getElementById('_method');

        const connectorId =
            document.getElementById(
                'modal-connector-id'
            );

        const testArea =
            document.getElementById(
                'modal-test-area'
            );

        const testResult =
            document.getElementById(
                'modal-test-result'
            );


        if (!modal || !form) return;


        const title =
            modal.querySelector('h3');

        if (title) {
            title.textContent =
                'Ajouter un connecteur';
        }


        form.action = '/connecteurs';

        if (method) {
            method.value = 'POST';
        }

        if (connectorId) {
            connectorId.value = '';
        }


        form.reset();


        if (testArea) {
            testArea.style.display = 'none';
        }

        if (testResult) {
            testResult.textContent = '';
        }


        onConnectorTypeChange();
    }


    /* ----------------------------------------------------------
       OUVERTURE CRÉATION CONNECTEUR
       ---------------------------------------------------------- */

    document
        .querySelectorAll(
            '[data-modal-open="connector-modal"]'
        )
        .forEach(function (btn) {

            btn.addEventListener(
                'click',
                function () {

                    openCreateModal();
                }
            );
        });


    /* ----------------------------------------------------------
       MODIFICATION CONNECTEUR
       ---------------------------------------------------------- */

    function openEditModal(connectorId) {

        fetch(
            '/connecteurs/' +
            connectorId +
            '/edit-data'
        )

            .then(function (r) {

                if (!r.ok) {
                    throw new Error(
                        'Non autorisé'
                    );
                }

                return r.json();
            })

            .then(function (data) {

                const modal =
                    document.getElementById(
                        'connector-modal'
                    );

                const form =
                    document.getElementById(
                        'connector-form'
                    );


                if (!modal || !form) return;


                const title =
                    modal.querySelector('h3');

                if (title) {

                    title.textContent =
                        'Modifier le connecteur';
                }


                form.action =
                    '/connecteurs/' +
                    connectorId;


                const method =
                    document.getElementById(
                        '_method'
                    );

                if (method) {
                    method.value = 'PUT';
                }


                const modalConnectorId =
                    document.getElementById(
                        'modal-connector-id'
                    );

                if (modalConnectorId) {
                    modalConnectorId.value =
                        connectorId;
                }


                const type =
                    document.getElementById('type');

                const name =
                    document.getElementById('name');

                const baseUrl =
                    document.getElementById(
                        'base_url'
                    );

                const apiPort =
                    document.getElementById(
                        'api_port'
                    );

                const authUsername =
                    document.getElementById(
                        'auth_username'
                    );

                const authPassword =
                    document.getElementById(
                        'auth_password'
                    );

                const extraConfig =
                    document.getElementById(
                        'extra_config_raw'
                    );


                if (type) {
                    type.value = data.type;
                }

                if (name) {
                    name.value = data.name;
                }

                if (baseUrl) {
                    baseUrl.value = data.base_url;
                }

                if (apiPort) {
                    apiPort.value =
                        data.api_port || '';
                }

                if (authUsername) {
                    authUsername.value =
                        data.auth_username || '';
                }

                if (authPassword) {
                    authPassword.value = '';
                }

                if (extraConfig) {

                    extraConfig.value =
                        data.extra_config
                            ? JSON.stringify(
                                data.extra_config,
                                null,
                                2
                            )
                            : '';
                }


                onConnectorTypeChange();
            })

            .catch(function (err) {

                alert(
                    'Erreur : ' +
                    err.message
                );
            });
    }


    /* ----------------------------------------------------------
       CHAMPS CONDITIONNELS SELON LE TYPE
       ---------------------------------------------------------- */

    function onConnectorTypeChange() {

        const typeElement =
            document.getElementById('type');

        const portGroup =
            document.getElementById(
                'port-group'
            );

        const testArea =
            document.getElementById(
                'modal-test-area'
            );

        const portInput =
            document.getElementById(
                'api_port'
            );


        if (
            !typeElement ||
            !portGroup ||
            !testArea ||
            !portInput
        ) {
            return;
        }


        const type =
            typeElement.value;


        if (type === 'wazuh') {

            portGroup.style.display = '';

            portInput.placeholder = '55000';

            if (!portInput.value) {
                portInput.value = '55000';
            }

            testArea.style.display = '';


        } else if (type === 'prometheus') {

            portGroup.style.display = '';

            portInput.placeholder = '9090';

            if (
                !portInput.value ||
                portInput.value === '55000'
            ) {
                portInput.value = '9090';
            }

            testArea.style.display = '';


        } else {

            portGroup.style.display = 'none';

            testArea.style.display = 'none';
        }
    }


    /* ----------------------------------------------------------
       CHANGEMENT DU TYPE DE CONNECTEUR
       ---------------------------------------------------------- */

    const connectorType =
        document.getElementById('type');

    if (connectorType) {

        connectorType.addEventListener(
            'change',
            onConnectorTypeChange
        );
    }


    /* ----------------------------------------------------------
       TEST DEPUIS LA MODALE
       ---------------------------------------------------------- */

    async function testFromModal() {

        const btn =
            document.getElementById(
                'modal-test-btn'
            );

        const result =
            document.getElementById(
                'modal-test-result'
            );


        if (!btn || !result) return;


        const originalHTML =
            btn.innerHTML;


        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Test en cours...';

        btn.disabled = true;

        result.textContent = '';


        const rawConfig =
            document.getElementById(
                'extra_config_raw'
            ).value.trim();


        let extraConfig = null;


        if (rawConfig) {

            try {

                extraConfig =
                    JSON.parse(rawConfig);

            } catch (e) {
                extraConfig = null;
            }
        }


        try {

            const response =
                await fetch(
                    '/connecteurs/test-preview',
                    {
                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,

                            'Accept':
                                'application/json'
                        },

                        body:
                            JSON.stringify({

                                type:
                                    document.getElementById(
                                        'type'
                                    ).value,

                                name:
                                    document.getElementById(
                                        'name'
                                    ).value,

                                base_url:
                                    document.getElementById(
                                        'base_url'
                                    ).value,

                                api_port:
                                    document.getElementById(
                                        'api_port'
                                    ).value ||
                                    null,

                                auth_username:
                                    document.getElementById(
                                        'auth_username'
                                    ).value ||
                                    null,

                                auth_password:
                                    document.getElementById(
                                        'auth_password'
                                    ).value ||
                                    null,

                                extra_config:
                                    extraConfig
                            })
                    }
                );


            const data =
                await response.json();


            if (data.success) {

                result.style.color =
                    'var(--sage-green)';

                result.textContent =
                    '✓ ' +
                    data.message +
                    ' (' +
                    data.response_time +
                    ' ms)';

            } else {

                result.style.color =
                    'var(--red)';

                result.textContent =
                    '✗ ' +
                    data.message;
            }


        } catch (error) {

            result.style.color =
                'var(--red)';

            result.textContent =
                'Erreur réseau : ' +
                error.message;


        } finally {

            btn.innerHTML =
                originalHTML;

            btn.disabled = false;
        }
    }


    /* ----------------------------------------------------------
       TEST DE CONNEXION — PAGE CONNECTEUR
       ---------------------------------------------------------- */

    async function runTest(id, btn) {

        if (!btn) return;


        const original =
            btn.innerHTML;


        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Test en cours...';

        btn.disabled = true;


        const resultCard =
            document.getElementById(
                'test-result-card'
            );


        if (resultCard) {
            resultCard.style.display = 'none';
        }


        try {

            const r =
                await fetch(
                    '/connecteurs/' +
                    id +
                    '/test',
                    {
                        method: 'POST',

                        headers: {

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,

                            'Accept':
                                'application/json'
                        }
                    }
                );


            const data =
                await r.json();


            const content =
                document.getElementById(
                    'test-result-content'
                );


            if (!content) return;


            if (data.success) {

                content.innerHTML =

                    '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">' +

                        '<span class="status-dot online" style="width:14px;height:14px;"></span>' +

                        '<strong style="font-size:16px;color:var(--sage-green);">Connexion réussie</strong>' +

                    '</div>' +

                    '<div><strong>Temps de réponse</strong><p>' +

                        data.response_time +

                        ' ms</p></div>' +

                    '<div><strong>Nouveau statut</strong><p>' +

                        (
                            data.status === 'connected'
                                ? 'Connecté'
                                : data.status
                        ) +

                        '</p></div>' +

                    '<div><strong>Vérifié à</strong><p>' +

                        (
                            data.last_check_at ||
                            '—'
                        ) +

                        '</p></div>' +

                    (
                        data.metadata

                            ? '<div><strong>Détails</strong><pre style="background:var(--input-bg);padding:10px;border-radius:6px;font-size:13px;overflow-x:auto;">' +

                                JSON.stringify(
                                    data.metadata,
                                    null,
                                    2
                                ) +

                              '</pre></div>'

                            : ''
                    );


            } else {

                content.innerHTML =

                    '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">' +

                        '<span class="status-dot offline" style="width:14px;height:14px;"></span>' +

                        '<strong style="font-size:16px;color:var(--red);">Échec de connexion</strong>' +

                    '</div>' +

                    '<div><strong>Erreur</strong><p style="color:var(--red);">' +

                        data.message +

                        '</p></div>' +

                    '<div><strong>Nouveau statut</strong><p>' +

                        (
                            data.status === 'error'
                                ? 'En erreur'
                                : data.status
                        ) +

                        '</p></div>';
            }


            if (resultCard) {
                resultCard.style.display = '';
            }


        } catch (e) {

            const content =
                document.getElementById(
                    'test-result-content'
                );


            if (content) {

                content.innerHTML =

                    '<div style="display:flex;align-items:center;gap:12px;">' +

                        '<span class="status-dot offline" style="width:14px;height:14px;"></span>' +

                        '<strong style="color:var(--red);">Erreur réseau</strong>' +

                    '</div>' +

                    '<p style="color:var(--text-muted);margin-top:8px;">Impossible de contacter le serveur.</p>';
            }


            if (resultCard) {
                resultCard.style.display = '';
            }


        } finally {

            btn.innerHTML =
                original;

            btn.disabled = false;
        }
    }


    /* ----------------------------------------------------------
       AFFICHER / MASQUER MOT DE PASSE
       ---------------------------------------------------------- */

    function togglePasswordVisibility(
        inputId,
        btn
    ) {

        const input =
            document.getElementById(inputId);

        if (!input || !btn) return;


        const icon =
            btn.querySelector('i');

        if (!icon) return;


        if (input.type === 'password') {

            input.type = 'text';

            icon.className =
                'fa-solid fa-eye-slash';

        } else {

            input.type = 'password';

            icon.className =
                'fa-solid fa-eye';
        }
    }


    /* ----------------------------------------------------------
       FORMULAIRE CONNECTEUR
       Parser extra_config avant submit
       ---------------------------------------------------------- */

    const connectorForm =
        document.getElementById(
            'connector-form'
        );


    if (connectorForm) {

        connectorForm.addEventListener(
            'submit',
            function (e) {

                const rawField =
                    document.getElementById(
                        'extra_config_raw'
                    );

                if (!rawField) return;


                const raw =
                    rawField.value.trim();


                const old =
                    this.querySelector(
                        'input[name="extra_config"]'
                    );


                if (old) {
                    old.remove();
                }


                if (raw) {

                    try {

                        const hidden =
                            document.createElement(
                                'input'
                            );

                        hidden.type = 'hidden';

                        hidden.name =
                            'extra_config';

                        hidden.value =
                            JSON.stringify(
                                JSON.parse(raw)
                            );

                        this.appendChild(
                            hidden
                        );


                    } catch (err) {

                        e.preventDefault();

                        alert(
                            'Le champ "Configuration avancée" doit contenir du JSON valide.'
                        );
                    }
                }
            }
        );
    }


    /* ==========================================================
       EXPOSITION DES FONCTIONS POUR LES BOUTONS BLADE
       ========================================================== */

    /*
     * Ces fonctions sont utilisées si tes boutons Blade
     * contiennent par exemple :
     *
     * onclick="openEditModal(id)"
     * onclick="testFromModal()"
     * onclick="runTest(id, this)"
     * onclick="togglePasswordVisibility(...)"
     */

    window.openCreateModal =
        openCreateModal;

    window.openEditModal =
        openEditModal;

    window.onConnectorTypeChange =
        onConnectorTypeChange;

    window.testFromModal =
        testFromModal;

    window.runTest =
        runTest;

    window.togglePasswordVisibility =
        togglePasswordVisibility;


    const successMessage = document.getElementById('success-message');

    if (successMessage) {

        setTimeout(function () {

            successMessage.style.transition = 'opacity 0.5s ease';
            successMessage.style.opacity = '0';

            setTimeout(function () {
                successMessage.remove();
            }, 500);

        }, 3000);
    }
    /* ==========================================================
   SIDEBAR MOBILE
   ========================================================== */

    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
        });

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
            });
        }

        // Fermer la sidebar quand on clique sur un lien (mobile)
        sidebar.querySelectorAll('.nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('open');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                }
            });
        });
    }
        /* ==========================================================
       PAGE API & WEBHOOKS
       ========================================================== */

    function toast(message, type) {
        var container = document.getElementById('toastContainer');
        if (!container) return;
        var el = document.createElement('div');
        el.className = 'toast toast-' + (type || 'success');
        el.textContent = message;
        container.appendChild(el);
        setTimeout(function () {
            el.classList.add('toast-out');
            setTimeout(function () { el.remove(); }, 200);
        }, 3500);
    }

    // data-open-modal (vue Blade)
    document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.openModal;
            var modal = document.getElementById(id);
            if (modal) modal.classList.add('open');
        });
    });

    // Fermer en cliquant sur l'overlay
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    // Fermer avec Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
                m.classList.remove('open');
            });
        }
    });

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            toast('Copié dans le presse-papiers.', 'success');
        }).catch(function () {
            toast('Impossible de copier.', 'error');
        });
    }

    var copyCurlBtn = document.getElementById('copyCurl');
    if (copyCurlBtn) {
        copyCurlBtn.addEventListener('click', function () {
            var block = document.getElementById('curlBlock');
            if (block) copyToClipboard(block.textContent);
        });
    }

    document.querySelectorAll('[data-copy-reveal-key]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var keyText = document.getElementById('reveal-key-text');
            if (keyText) copyToClipboard(keyText.textContent);
        });
    });

    // ── Filtre texte ──
    document.querySelectorAll('[data-filter-table]').forEach(function (input) {
        input.addEventListener('input', function () {
            var tableId = this.dataset.filterTable;
            var table = document.getElementById(tableId);
            if (!table) return;
            var query = this.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    });

    // ── Filtre méthode ──
    document.querySelectorAll('[data-filter-method]').forEach(function (select) {
        select.addEventListener('change', function () {
            var table = document.getElementById(this.dataset.filterMethod);
            if (!table) return;
            var value = this.value;
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = !value || row.dataset.method === value ? '' : 'none';
            });
        });
    });

    // ── Filtre statut ──
    document.querySelectorAll('[data-filter-status]').forEach(function (select) {
        select.addEventListener('change', function () {
            var table = document.getElementById(this.dataset.filterStatus);
            if (!table) return;
            var value = this.value;
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = !value || row.dataset.status === value ? '' : 'none';
            });
        });
    });

    // ── Filtre événement ──
    document.querySelectorAll('[data-filter-event]').forEach(function (select) {
        select.addEventListener('change', function () {
            var table = document.getElementById(this.dataset.filterEvent);
            if (!table) return;
            var value = this.value;
            table.querySelectorAll('tbody tr').forEach(function (row) {
                if (!value) { row.style.display = ''; return; }
                var events = (row.dataset.events || '').split(',');
                row.style.display = events.includes(value) ? '' : 'none';
            });
        });
    });

    // ── Charger événements dans modal webhook ──
    var eventContainer = document.getElementById('event-checks-container');
    if (eventContainer) {
        fetch('/webhooks/event-types')
            .then(function (r) { return r.json(); })
            .then(function (types) {
                eventContainer.innerHTML = '';
                types.forEach(function (t) {
                    var label = document.createElement('label');
                    label.className = 'event-check';
                    label.innerHTML =
                        '<input type="checkbox" name="event_types[]" value="' + t.id + '">' +
                        '<span class="event-check-label">' + t.code + '</span>';
                    eventContainer.appendChild(label);
                });
            })
            .catch(function () {
                eventContainer.innerHTML = '<p class="text-muted">Impossible de charger les événements.</p>';
            });
    }

    // ── Afficher/masquer clé API dans modal webhook ──
    var whAuthMethod = document.getElementById('wh-auth-method');
    var whApikeyGroup = document.getElementById('wh-apikey-group');
    if (whAuthMethod && whApikeyGroup) {
        whAuthMethod.addEventListener('change', function () {
            whApikeyGroup.style.display = this.value === 'api_key' ? '' : 'none';
        });
    }

    // ── En-têtes dynamiques endpoint ──
    var headerIndex = 1;
    var addHeaderBtn = document.getElementById('add-header-btn');
    var headerRows = document.getElementById('header-rows');
    if (addHeaderBtn && headerRows) {
        addHeaderBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'header-row';
            row.innerHTML =
                '<input type="text" name="headers[' + headerIndex + '][key]" placeholder="Clé">' +
                '<input type="text" name="headers[' + headerIndex + '][value]" placeholder="Valeur">' +
                '<button type="button" class="icon-btn" data-remove-header><i class="fa-solid fa-xmark"></i></button>';
            headerRows.appendChild(row);
            headerIndex++;
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove-header]');
        if (btn) btn.closest('.header-row').remove();
    });

    // ── CSRF ──
    function getCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ── Clé API — Générer ──
    var apiKeyForm = document.getElementById('api-key-form');
    if (apiKeyForm) {
        apiKeyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(this);
            var scopes = fd.getAll('scopes[]');
            fetch('/api-keys', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: fd.get('name'), scopes: scopes, expires_at: fd.get('expires_at') || null })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.plain_key) {
                    document.getElementById('reveal-key-text').textContent = data.plain_key;
                    document.getElementById('api-key-modal').classList.remove('open');
                    document.getElementById('api-key-reveal').classList.add('open');
                    apiKeyForm.reset();
                    toast('Clé API générée avec succès.');
                } else if (data.errors) {
                    toast(data.errors.name ? data.errors.name[0] : 'Erreur de validation.', 'error');
                }
            })
            .catch(function () { toast('Erreur lors de la génération.', 'error'); });
        });
    }

    // ── Clé API — Toggle ──
    document.querySelectorAll('[data-toggle-key]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var keyId = this.dataset.toggleKey;
            fetch('/api-keys/' + keyId + '/suspend', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.message) { toast(data.message); setTimeout(function () { location.reload(); }, 600); }
                if (data.error) { toast(data.error, 'error'); }
            })
            .catch(function () { toast('Erreur.', 'error'); });
        });
    });

    // ── Clé API — Régénérer ──
    document.querySelectorAll('[data-regen-key]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Régénérer cette clé ? L\'ancienne sera immédiatement invalide.')) return;
            var keyId = this.dataset.regenKey;
            fetch('/api-keys/' + keyId + '/regenerate', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.plain_key) {
                    document.getElementById('reveal-key-text').textContent = data.plain_key;
                    document.getElementById('api-key-reveal').classList.add('open');
                    toast('Clé régénérée. Copiez la nouvelle clé.');
                }
                if (data.error) { toast(data.error, 'error'); }
            })
            .catch(function () { toast('Erreur.', 'error'); });
        });
    });

    // ── Clé API — Révoquer ──
    document.querySelectorAll('[data-revoke-key]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Révoquer définitivement cette clé ?')) return;
            var keyId = this.dataset.revokeKey;
            fetch('/api-keys/' + keyId + '/revoke', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.message) { toast(data.message); setTimeout(function () { location.reload(); }, 600); }
            })
            .catch(function () { toast('Erreur.', 'error'); });
        });
    });

    // ── Endpoint — Charger cURL ──
    document.querySelectorAll('[data-load-curl]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var epId = this.dataset.loadCurl;
            var curlBlock = document.getElementById('curlBlock');
            curlBlock.innerHTML = '<code><span class="text-muted">Chargement...</span></code>';
            fetch('/endpoints/' + epId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var html = '<span class="api-cmd">curl</span> <span class="api-flag">-X ' + (data.http_method || 'GET') + '</span> \\<br>';
                html += '&nbsp;&nbsp;<span class="api-url">"' + (data.url || '') + '"</span>';
                curlBlock.innerHTML = '<code>' + html + '</code>';
            })
            .catch(function () { curlBlock.innerHTML = '<code><span class="text-muted">Impossible de charger.</span></code>'; });
        });
    });

    // ── Endpoint — Tester ──
    document.querySelectorAll('[data-test-endpoint]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var epId = this.dataset.testEndpoint;
            var original = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            this.disabled = true;
            fetch('/endpoints/' + epId + '/test', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'success') toast('Endpoint OK — ' + data.duration_ms + 'ms', 'success');
                else if (data.status === 'timeout') toast('Timeout — ' + data.duration_ms + 'ms', 'warning');
                else toast('Erreur HTTP ' + data.http_code, 'error');
                setTimeout(function () { location.reload(); }, 800);
            })
            .catch(function () { toast('Erreur réseau.', 'error'); })
            .finally(function () { btn.innerHTML = original; btn.disabled = false; });
        });
    });

    // ── Endpoint — Modifier ──
    document.querySelectorAll('[data-edit-endpoint]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var epId = this.dataset.editEndpoint;
            fetch('/endpoints/' + epId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var form = document.getElementById('endpoint-form');
                if (!form) return;
                document.getElementById('endpoint-edit-id').value = data.id || '';
                form.querySelector('[name="application_id"]').value = data.application_id || '';
                form.querySelector('[name="http_method"]').value = data.http_method || 'GET';
                form.querySelector('[name="url"]').value = data.url || '';
                form.querySelector('[name="frequency_seconds"]').value = data.frequency_seconds || 60;
                document.getElementById('header-rows').innerHTML =
                    '<div class="header-row">' +
                    '<input type="text" name="headers[0][key]" placeholder="Clé">' +
                    '<input type="text" name="headers[0][value]" placeholder="Valeur">' +
                    '<button type="button" class="icon-btn" data-remove-header><i class="fa-solid fa-xmark"></i></button></div>';
                headerIndex = 1;
                document.querySelector('#endpoint-modal h3').textContent = 'Modifier l\'endpoint';
                document.getElementById('endpoint-modal').classList.add('open');
            })
            .catch(function () { toast('Impossible de charger.', 'error'); });
        });
    });

    // ── Endpoint — Supprimer ──
    document.querySelectorAll('[data-delete-endpoint]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var epId = this.dataset.deleteEndpoint;
            document.getElementById('delete-modal-text').textContent = 'Voulez-vous vraiment supprimer cet endpoint ?';
            var form = document.getElementById('delete-form');
            form.action = '/endpoints/' + epId;
            form.querySelector('input[name="_method"]').value = 'DELETE';
            document.getElementById('delete-modal').classList.add('open');
        });
    });

    // ── Webhook — Supprimer ──
    document.querySelectorAll('[data-delete-webhook]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var whId = this.dataset.deleteWebhook;
            document.getElementById('delete-modal-text').textContent = 'Voulez-vous vraiment supprimer ce webhook ?';
            var form = document.getElementById('delete-form');
            form.action = '/webhooks/' + whId;
            form.querySelector('input[name="_method"]').value = 'DELETE';
            document.getElementById('delete-modal').classList.add('open');
        });
    });

    // ── Endpoint form — Soumission ──
    var endpointForm = document.getElementById('endpoint-form');
    if (endpointForm) {
        endpointForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var editId = document.getElementById('endpoint-edit-id').value;
            var isEdit = editId && editId !== '';
            var url = isEdit ? '/endpoints/' + editId : '/endpoints';
            var headerRows = document.querySelectorAll('#header-rows .header-row');
            var headers = [];
            headerRows.forEach(function (row) {
                var inputs = row.querySelectorAll('input');
                if (inputs[0] && inputs[1] && inputs[0].value && inputs[1].value) {
                    headers.push({ key: inputs[0].value, value: inputs[1].value });
                }
            });
            var payload = {
                application_id: this.querySelector('[name="application_id"]').value,
                http_method: this.querySelector('[name="http_method"]').value,
                url: this.querySelector('[name="url"]').value,
                frequency_seconds: this.querySelector('[name="frequency_seconds"]').value,
                headers: headers
            };
            fetch(url, {
                method: isEdit ? 'PUT' : 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.message) {
                    toast(data.message);
                    document.getElementById('endpoint-modal').classList.remove('open');
                    setTimeout(function () { location.reload(); }, 600);
                }
                if (data.errors) {
                    var firstError = Object.values(data.errors)[0];
                    toast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                }
            })
            .catch(function () { toast('Erreur.', 'error'); });
        });
    }

    // ── Webhook form — Soumission ──
    var webhookForm = document.getElementById('webhook-form');
    if (webhookForm) {
        webhookForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(this);
            var eventTypes = fd.getAll('event_types[]');
            var payload = {
                name: fd.get('name'),
                target_url: fd.get('target_url'),
                auth_method: fd.get('auth_method'),
                api_key_id: fd.get('api_key_id') || null,
                min_severity_level: fd.get('min_severity_level'),
                event_types: eventTypes
            };
            fetch('/webhooks', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.message) {
                    toast(data.message);
                    document.getElementById('webhook-modal').classList.remove('open');
                    setTimeout(function () { location.reload(); }, 600);
                }
                if (data.errors) {
                    var firstError = Object.values(data.errors)[0];
                    toast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                }
            })
            .catch(function () { toast('Erreur.', 'error'); });
        });
    }
        /* ==========================================================
       PAGE SETTINGS (Onglets et sauvegarde)
       ========================================================== */
    const form = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveSettingsBtn');
    
    // SÉCURITÉ : Si on n'est pas sur la page des settings, on arrête tout
    if (form && saveBtn) {
        const noChangesText = document.getElementById('noChangesText');
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        const activeTabInput = document.getElementById('active_tab_input');
        
        let hasChanged = false;

        // Gestion des onglets
        if (tabBtns.length > 0) {
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tab = btn.dataset.tab;
                    tabBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    tabContents.forEach(c => c.classList.remove('active'));
                    const tabContent = document.getElementById('tab-' + tab);
                    if (tabContent) tabContent.classList.add('active');
                    if (activeTabInput) activeTabInput.value = tab;
                });
            });
        }

        // Détection de modification pour activer le bouton Enregistrer
        form.addEventListener('input', function() {
            if (!hasChanged) {
                hasChanged = true;
                saveBtn.disabled = false;
                saveBtn.classList.add('btn-active-state'); // Classe CSS pour changer le style
                if (noChangesText) noChangesText.style.display = 'none';
            }
        });

        // Effet de chargement lors du clic sur "Enregistrer"
        form.addEventListener('submit', function() {
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';
            saveBtn.disabled = true;
        });
    }
        /* ==========================================================
       MONITORING SERVEUR (Style Grafana)
       ========================================================== */
    function initGrafanaMonitoring() {
        const panels = document.querySelectorAll('.grafana-panel');
        if (panels.length === 0) return; // Si pas de panneaux sur la page, on arrête

        async function loadAllMetrics() {
            panels.forEach(async (panel) => {
                const serverId = panel.dataset.serverId;
                
                try {
                    const response = await fetch(`/monitoring/servers/${serverId}/metrics`);
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    if (!data.success) return;

                    // 1. Mise à jour du Statut
                    const statusEl = document.getElementById(`status-${serverId}`);
                    
                    if (data.status === 'online') {
                        statusEl.innerHTML = '<span class="status-dot online"></span> En ligne';
                    } else {
                        statusEl.innerHTML = '<span class="status-dot offline"></span> Hors ligne';
                    }

                    // 2. Mise à jour CPU
                    const cpuVal = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                    const cpuPct = data.cpu !== null ? data.cpu : 0;
                    document.getElementById(`cpu-val-${serverId}`).textContent = cpuVal;
                    
                    const cpuBar = document.getElementById(`cpu-bar-${serverId}`);
                    cpuBar.style.width = `${cpuPct}%`;
                    cpuBar.className = 'metric-bar'; // Reset class
                    if (cpuPct > 80) cpuBar.classList.add('critical');
                    else if (cpuPct > 60) cpuBar.classList.add('warning');

                    // 3. Mise à jour RAM
                    const ramVal = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';
                    const ramPct = data.memory !== null ? data.memory : 0;
                    document.getElementById(`ram-val-${serverId}`).textContent = ramVal;
                    
                    const ramBar = document.getElementById(`ram-bar-${serverId}`);
                    ramBar.style.width = `${ramPct}%`;
                    ramBar.className = 'metric-bar'; // Reset class
                    if (ramPct > 80) ramBar.classList.add('critical');
                    else if (ramPct > 60) ramBar.classList.add('warning');

                } catch (error) {
                    console.error('Erreur monitoring pour serveur ' + serverId, error);
                }
            });
        }

        // Première exécution
        loadAllMetrics();

        // Rafraîchissement toutes les 15 secondes
        setInterval(loadAllMetrics, 15000);
    }
    initGrafanaMonitoring();
    const monitorCard = document.querySelector('.monitor-card');
        if (!monitorCard) return; // Si on n'est pas sur cette page, on arrête

        const serverId = monitorCard.dataset.serverId;
        const statusEl = document.getElementById('server-status');
        const cpuEl = document.getElementById('cpu-value');
        const memEl = document.getElementById('memory-value');

        // Fonction pour mettre à jour les chiffres
        async function loadMetrics() {
            try {
                const response = await fetch(`/monitoring/servers/${serverId}/metrics`);
                if (!response.ok) return;
                
                const data = await response.json();
                if (!data.success) return;

                // Mise à jour du statut
                if (data.status === 'online') {
                    statusEl.textContent = '🟢 En ligne';
                    statusEl.style.color = 'var(--sage-green)';
                } else {
                    statusEl.textContent = '🔴 Hors ligne';
                    statusEl.style.color = 'var(--red)';
                }

                // Mise à jour du CPU et de la RAM
                cpuEl.textContent = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                memEl.textContent = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';

            } catch (error) {
                console.error('Erreur monitoring:', error);
                statusEl.textContent = 'Erreur réseau';
                statusEl.style.color = 'var(--red)';
            }
        }

        // Première exécution immédiate
        loadMetrics();

        // Rafraîchissement toutes les 15 secondes
        setInterval(loadMetrics, 15000);

        /* ==========================================================
       MONITORING SERVEUR - PAGE DE DÉTAIL
       ========================================================== */
    function initServerMonitoringDetail() {
        const monitorCard = document.querySelector('.monitor-card');
        if (!monitorCard) return; // Si on n'est pas sur la page de détail, on arrête

        const serverId = monitorCard.dataset.serverId;
        const statusEl = document.getElementById('server-status');
        const cpuEl = document.getElementById('cpu-value');
        const memEl = document.getElementById('memory-value');

        async function loadMetrics() {
            try {
                const response = await fetch(`/monitoring/servers/${serverId}/metrics`);
                if (!response.ok) return;
                
                const data = await response.json();
                if (!data.success) return;

                if (data.status === 'online') {
                    statusEl.textContent = '🟢 En ligne';
                    statusEl.style.color = 'var(--sage-green)';
                } else {
                    statusEl.textContent = '🔴 Hors ligne';
                    statusEl.style.color = 'var(--red)';
                }

                cpuEl.textContent = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                memEl.textContent = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';

            } catch (error) {
                console.error('Erreur monitoring:', error);
                statusEl.textContent = 'Erreur réseau';
                statusEl.style.color = 'var(--red)';
            }
        }

        // Première exécution
        loadMetrics();

        // Rafraîchissement toutes les 15 secondes
        setInterval(loadMetrics, 15000);
    }

    // On lance la fonction quand la page est prête
    initServerMonitoringDetail();
// ATTENTION : Ne mets pas de }); ici. Laisse la grande accolade de ton fichier JS se fermer normalement plus bas.
});