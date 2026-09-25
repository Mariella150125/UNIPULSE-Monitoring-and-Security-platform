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

        passwordInput.addEventListener('focus', function () {
            rulesPanel.hidden = false;
        });

        passwordInput.addEventListener('input', function () {
            updateRulesPanel(passwordInput.value);
        });

        if (toggleIcon) {
            toggleIcon.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye', !isHidden);
                toggleIcon.classList.toggle('fa-eye-slash', isHidden);
            });
        }

        if (formId === 'login-form') {
            form.addEventListener('submit', function () {});
            return;
        }

        form.addEventListener('submit', function (event) {
            updateRulesPanel(passwordInput.value);
            rulesPanel.hidden = false;

            if (!isPasswordValid(passwordInput.value)) {
                event.preventDefault();
                passwordInput.focus();
                return;
            }
        });
    }

    initPasswordValidator('login-password', 'login-toggle-password', 'login-password-rules', 'login-form');
    initPasswordValidator('signup-password', 'signup-toggle-password', 'signup-password-rules', 'signup-form');
    initPasswordValidator('activation-password', 'activation-toggle-password', 'activation-password-rules', 'activation-form');

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

            document.querySelectorAll('.nav-group.open').forEach(function (openGroup) {
                if (openGroup !== group) {
                    openGroup.classList.remove('open');
                    const openItems = openGroup.querySelector('.nav-group-items');
                    if (openItems) openItems.style.maxHeight = null;
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

    const labels = ['22 Juil.', '23 Juil.', '24 Juil.', '25 Juil.', '26 Juil.', '27 Juil.', '28 Juil.'];

    var envDonutCanvas = document.getElementById('envDonutChart');

    if (envDonutCanvas && typeof Chart !== 'undefined') {
        fetch('/dashboard/environment-chart')
            .then(function (response) {
                if (!response.ok) throw new Error('Erreur');
                return response.json();
            })
            .then(function (result) {
                var labels = result.labels || [];
                var data = result.data || [];
                var legend = document.getElementById('donutLegend');

                if (labels.length === 0 || data.length === 0) {
                    legend.innerHTML = '<p class="donut-empty">Aucune donnée disponible</p>';
                    return;
                }

                var environmentColors = ['#56825E', '#1d4a40', '#8fae94', '#c9d8cb', '#6f8f77', '#b5c7b8'];

                new Chart(envDonutCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: labels.map(function (_, index) {
                                return environmentColors[index % environmentColors.length];
                            }),
                            borderWidth: 0,
                            hoverOffset: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        var label = context.label || '';
                                        var value = context.parsed || 0;
                                        return ' ' + label + ' : ' + value + ' serveur' + (value > 1 ? 's' : '');
                                    }
                                }
                            }
                        }
                    }
                });

                legend.innerHTML = '';
                labels.forEach(function (label, index) {
                    var item = document.createElement('div');
                    item.className = 'legend-item';
                    item.innerHTML =
                        '<div class="legend-left">' +
                            '<span class="legend-color" style="background-color:' + environmentColors[index % environmentColors.length] + '"></span>' +
                            '<span class="legend-label">' + label + '</span>' +
                        '</div>' +
                        '<span class="legend-value">' + data[index] + '</span>';
                    legend.appendChild(item);
                });
            })
            .catch(function (error) {
                console.error('Erreur donut environnement :', error);
                document.getElementById('donutLegend').innerHTML = '<p class="donut-empty">Impossible de charger.</p>';
            });
    }

    const ctxAlerts = document.getElementById('alertChart');
    if (ctxAlerts && typeof Chart !== 'undefined') {
        new Chart(ctxAlerts, {
            type: 'line',
            data: {
                labels: window.dashboardAlertLabels || [], // <-- VARIABLE LARAVEL
                datasets: [{
                    label: 'Alertes critiques',
                    data: window.dashboardAlertData || [], // <-- VARIABLE LARAVEL
                    borderColor: '#c0392b',
                    backgroundColor: 'rgba(192, 57, 43, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, grid: { color: '#eef1ef' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxSecurityScore = document.getElementById('securityChart');
    if (ctxSecurityScore && typeof Chart !== 'undefined') {
        new Chart(ctxSecurityScore, {
            type: 'line',
            data: {
                labels: window.dashboardSecurityLabels || [], // <-- VARIABLE LARAVEL
                datasets: [{
                    label: 'Score de sécurité (%)',
                    data: window.dashboardSecurityData || [], // <-- VARIABLE LARAVEL
                    borderColor: '#56825E',
                    backgroundColor: 'rgba(86, 130, 94, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: { min: 0, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxServerHealth = document.getElementById('serverChart');
    if (ctxServerHealth && typeof Chart !== 'undefined') {
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
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: { min: 0, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    var appEnvDonutCanvas = document.getElementById('appEnvDonutChart');
    if (appEnvDonutCanvas && typeof Chart !== 'undefined') {
        fetch('/dashboard/application-environment-chart')
            .then(function (response) {
                if (!response.ok) throw new Error('Erreur');
                return response.json();
            })
            .then(function (result) {
                var labels = Array.isArray(result.labels) ? result.labels : [];
                var data = Array.isArray(result.data) ? result.data.map(Number) : [];
                var legend = document.getElementById('appDonutLegend');

                if (labels.length === 0 || data.length === 0) {
                    if (legend) legend.innerHTML = '<p style="color: var(--text-muted);">Aucune donnée disponible</p>';
                    return;
                }

                new Chart(appEnvDonutCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: ['#56825E', '#1d4a40', '#8fae94', '#c9d8cb'],
                            borderWidth: 0,
                            hoverOffset: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ' ' + context.label + ' : ' + context.parsed + ' application(s)';
                                    }
                                }
                            }
                        }
                    }
                });

                if (!legend) return;
                legend.innerHTML = '';
                var colors = ['#56825E', '#1d4a40', '#8fae94', '#c9d8cb'];
                labels.forEach(function (label, index) {
                    var item = document.createElement('div');
                    item.className = 'donut-legend-item';
                    item.innerHTML =
                        '<span class="donut-legend-dot" style="background-color:' + colors[index % colors.length] + '"></span>' +
                        '<span class="donut-legend-label">' + label + '</span>' +
                        '<span class="donut-legend-value">' + data[index] + '</span>';
                    legend.appendChild(item);
                });
            })
            .catch(function (error) {
                console.error('Erreur donut applications :', error);
                var legend = document.getElementById('appDonutLegend');
                if (legend) legend.innerHTML = '<p style="color: var(--red);">Impossible de charger.</p>';
            });
    }

    const logoutLink = document.getElementById('logout-link');
    const logoutForm = document.getElementById('logout-form');
    if (logoutLink && logoutForm) {
        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();
            logoutForm.submit();
        });
    }

    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let timer;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                const form = searchInput.closest('form');
                if (form) form.submit();
            }, 500);
        });
    }

    document.querySelectorAll('[data-modal-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = this.dataset.modalOpen;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('open');
                document.body.classList.add('modal-open');
            }
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = this.dataset.modalClose;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                document.body.classList.remove('modal-open');
            }
        });
    });

    const hostingSelect = document.getElementById('is_hosted');
    const serverField = document.getElementById('server-field');
    const portField = document.getElementById('port-field');
    const deploymentPathField = document.getElementById('deployment-path-field');
    const serverSelect = document.getElementById('server_id');
    const portInput = document.getElementById('port');
    const deploymentPathInput = document.getElementById('deployment_path');

    if (hostingSelect && serverField && portField && deploymentPathField && serverSelect) {
        function updateHostingFields() {
            const isHosted = hostingSelect.value === '1';
            if (isHosted) {
                serverField.style.display = 'block';
                portField.style.display = 'block';
                deploymentPathField.style.display = 'block';
                serverSelect.required = true;
            } else {
                serverField.style.display = 'none';
                portField.style.display = 'none';
                deploymentPathField.style.display = 'none';
                serverSelect.required = false;
                serverSelect.value = '';
                if (portInput) portInput.value = '';
                if (deploymentPathInput) deploymentPathInput.value = '';
            }
        }
        hostingSelect.addEventListener('change', updateHostingFields);
        updateHostingFields();
    }

    const userMenuToggle = document.getElementById('user-menu-toggle');
    const userDropdown = document.getElementById('user-dropdown');
    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            userDropdown.classList.toggle('open');
        });
        document.addEventListener('click', function (event) {
            if (!userDropdown.contains(event.target) && !userMenuToggle.contains(event.target)) {
                userDropdown.classList.remove('open');
            }
        });
    }

    const dateRangeToggle = document.getElementById('date-range-toggle');
    const dateRangeMenu = document.getElementById('date-range-menu');
    if (dateRangeToggle && dateRangeMenu) {
        dateRangeToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            dateRangeMenu.classList.toggle('open');
        });
        dateRangeMenu.querySelectorAll('[data-range]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const range = this.dataset.range;
                if (range === 'custom') {
                    dateRangeMenu.classList.remove('open');
                    return;
                }
                const url = new URL(window.location.href);
                url.searchParams.set('range', range);
                window.location.href = url.toString();
            });
        });
        document.addEventListener('click', function (event) {
            if (!dateRangeMenu.contains(event.target) && !dateRangeToggle.contains(event.target)) {
                dateRangeMenu.classList.remove('open');
            }
        });
    }

    const language = document.querySelector('.language');
    if (language) {
        const languageButton = language.querySelector('.lang-active');
        const languageOptions = language.querySelectorAll('[data-lang]');
        if (languageButton) {
            languageButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                language.classList.toggle('open');
            });
        }
        languageOptions.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const lang = this.dataset.lang;
                language.classList.remove('open');
            });
        });
        document.addEventListener('click', function () {
            language.classList.remove('open');
        });
    }

    let connectorSearchTimeout;
    const connectorSearch = document.getElementById('connector-search');
    const filterType = document.getElementById('filter-type');
    const filterStatus = document.getElementById('filter-status');

    function applyConnectorFilters() {
        const params = new URLSearchParams();
        const search = connectorSearch ? connectorSearch.value.trim() : '';
        const type = filterType ? filterType.value : '';
        const status = filterStatus ? filterStatus.value : '';
        if (search) params.set('search', search);
        if (type) params.set('type', type);
        if (status) params.set('status', status);
        const qs = params.toString();
        window.location = '/connecteurs' + (qs ? '?' + qs : '');
    }

    if (connectorSearch) {
        connectorSearch.addEventListener('input', function () {
            clearTimeout(connectorSearchTimeout);
            connectorSearchTimeout = setTimeout(applyConnectorFilters, 400);
        });
    }
    if (filterType) filterType.addEventListener('change', applyConnectorFilters);
    if (filterStatus) filterStatus.addEventListener('change', applyConnectorFilters);

    function openCreateModal() {
        const modal = document.getElementById('connector-modal');
        const form = document.getElementById('connector-form');
        const method = document.getElementById('_method');
        const connectorId = document.getElementById('modal-connector-id');
        const testArea = document.getElementById('modal-test-area');
        const testResult = document.getElementById('modal-test-result');
        if (!modal || !form) return;
        const title = modal.querySelector('h3');
        if (title) title.textContent = 'Ajouter un connecteur';
        form.action = '/connecteurs';
        if (method) method.value = 'POST';
        if (connectorId) connectorId.value = '';
        form.reset();
        if (testArea) testArea.style.display = 'none';
        if (testResult) testResult.textContent = '';
        onConnectorTypeChange();
    }

    document.querySelectorAll('[data-modal-open="connector-modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () { openCreateModal(); });
    });

    function openEditModal(connectorId) {
        fetch('/connecteurs/' + connectorId + '/edit-data')
            .then(function (r) {
                if (!r.ok) throw new Error('Non autorisé');
                return r.json();
            })
            .then(function (data) {
                const modal = document.getElementById('connector-modal');
                const form = document.getElementById('connector-form');
                if (!modal || !form) return;
                const title = modal.querySelector('h3');
                if (title) title.textContent = 'Modifier le connecteur';
                form.action = '/connecteurs/' + connectorId;
                const method = document.getElementById('_method');
                if (method) method.value = 'PUT';
                const modalConnectorId = document.getElementById('modal-connector-id');
                if (modalConnectorId) modalConnectorId.value = connectorId;
                
                document.getElementById('type').value = data.type;
                document.getElementById('name').value = data.name;
                document.getElementById('base_url').value = data.base_url;
                document.getElementById('api_port').value = data.api_port || '';
                document.getElementById('auth_username').value = data.auth_username || '';
                document.getElementById('auth_password').value = '';
                document.getElementById('extra_config_raw').value = data.extra_config ? JSON.stringify(data.extra_config, null, 2) : '';
                
                onConnectorTypeChange();
            })
            .catch(function (err) { alert('Erreur : ' + err.message); });
    }

    function onConnectorTypeChange() {
        const typeElement = document.getElementById('type');
        const portGroup = document.getElementById('port-group');
        const testArea = document.getElementById('modal-test-area');
        const portInput = document.getElementById('api_port');
        if (!typeElement || !portGroup || !testArea || !portInput) return;
        const type = typeElement.value;
        if (type === 'wazuh') {
            portGroup.style.display = '';
            portInput.placeholder = '55000';
            if (!portInput.value) portInput.value = '55000';
            testArea.style.display = '';
        } else if (type === 'prometheus') {
            portGroup.style.display = '';
            portInput.placeholder = '9090';
            if (!portInput.value || portInput.value === '55000') portInput.value = '9090';
            testArea.style.display = '';
        } else {
            portGroup.style.display = 'none';
            testArea.style.display = 'none';
        }
    }

    const connectorType = document.getElementById('type');
    if (connectorType) connectorType.addEventListener('change', onConnectorTypeChange);

    async function testFromModal() {
        const btn = document.getElementById('modal-test-btn');
        const result = document.getElementById('modal-test-result');
        if (!btn || !result) return;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Test en cours...';
        btn.disabled = true;
        result.textContent = '';
        const rawConfig = document.getElementById('extra_config_raw').value.trim();
        let extraConfig = null;
        if (rawConfig) {
            try { extraConfig = JSON.parse(rawConfig); } catch (e) { extraConfig = null; }
        }
        try {
            const response = await fetch('/connecteurs/test-preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: document.getElementById('type').value,
                    name: document.getElementById('name').value,
                    base_url: document.getElementById('base_url').value,
                    api_port: document.getElementById('api_port').value || null,
                    auth_username: document.getElementById('auth_username').value || null,
                    auth_password: document.getElementById('auth_password').value || null,
                    extra_config: extraConfig
                })
            });
            const data = await response.json();
            if (data.success) {
                result.style.color = 'var(--sage-green)';
                result.textContent = '✓ ' + data.message + ' (' + data.response_time + ' ms)';
            } else {
                result.style.color = 'var(--red)';
                result.textContent = '✗ ' + data.message;
            }
        } catch (error) {
            result.style.color = 'var(--red)';
            result.textContent = 'Erreur réseau : ' + error.message;
        } finally {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    }

    async function runTest(id, btn) {
    if (!btn) return;
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Test en cours...';
    btn.disabled = true;
    const resultCard = document.getElementById('test-result-card');
    if (resultCard) resultCard.style.display = 'none';
    try {
        const r = await fetch('/connecteurs/' + id + '/test', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await r.json();
        const content = document.getElementById('test-result-content');
        if (!content) return;
        
        // Affichage du résultat principal
        if (data.success) {
            content.innerHTML = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;"><span class="status-dot online" style="width:14px;height:14px;"></span><strong style="font-size:16px;color:var(--sage-green);">Connexion réussie</strong></div><div><strong>Temps de réponse</strong><p>' + data.response_time + ' ms</p></div><div><strong>Nouveau statut</strong><p>' + (data.status === 'connected' ? 'Connecté' : data.status) + '</p></div><div><strong>Vérifié à</strong><p>' + (data.last_check_at || '—') + '</p></div>' + (data.metadata ? '<div><strong>Détails</strong><pre style="background:var(--input-bg);padding:10px;border-radius:6px;font-size:13px;overflow-x:auto;">' + JSON.stringify(data.metadata, null, 2) + '</pre></div>' : '');
        } else {
            content.innerHTML = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;"><span class="status-dot offline" style="width:14px;height:14px;"></span><strong style="font-size:16px;color:var(--red);">Échec de connexion</strong></div><div><strong>Erreur</strong><p style="color:var(--red);">' + data.message + '</p></div><div><strong>Nouveau statut</strong><p>' + (data.status === 'error' ? 'En erreur' : data.status) + '</p></div>';
        }
        if (resultCard) resultCard.style.display = '';

        // MISE À JOUR DE L'HISTORIQUE EN DIRECT
        if (data.last_log) {
            const historyList = document.getElementById('history-list');
            const noLogs = document.getElementById('no-logs');
            if (noLogs) noLogs.remove(); // Enlève le message "Aucun historique"

            const newLog = document.createElement('div');
            newLog.className = 'history-item';
            newLog.innerHTML = `
                <span class="status-dot ${data.last_log.success ? 'online' : 'offline'}"></span>
                <div>
                    <strong>
                        ${data.last_log.success ? 'Connexion réussie' : 'Échec de connexion'}
                        ${data.last_log.duration_ms ? `<span style="font-weight:400;color:var(--text-muted);font-size:11px;">(${data.last_log.duration_ms} ms)</span>` : ''}
                    </strong>
                    <p>
                        ${data.last_log.executed_at}
                        ${!data.last_log.success && data.last_log.error_message ? ` — ${data.last_log.error_message}` : ''}
                    </p>
                </div>
            `;
            historyList.prepend(newLog); // Ajoute en haut de la liste
        }

    } catch (e) {
        const content = document.getElementById('test-result-content');
        if (content) content.innerHTML = '<div style="display:flex;align-items:center;gap:12px;"><span class="status-dot offline" style="width:14px;height:14px;"></span><strong style="color:var(--red);">Erreur réseau</strong></div><p style="color:var(--text-muted);margin-top:8px;">Impossible de contacter le serveur Laravel.</p>';
        if (resultCard) resultCard.style.display = '';
    } finally {
        btn.innerHTML = original;
        btn.disabled = false;
    }
}

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input || !btn) return;
        const icon = btn.querySelector('i');
        if (!icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    }

    const connectorForm = document.getElementById('connector-form');
    if (connectorForm) {
        connectorForm.addEventListener('submit', function (e) {
            const rawField = document.getElementById('extra_config_raw');
            if (!rawField) return;
            const raw = rawField.value.trim();
            const old = this.querySelector('input[name="extra_config"]');
            if (old) old.remove();
            if (raw) {
                try {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'extra_config';
                    hidden.value = JSON.stringify(JSON.parse(raw));
                    this.appendChild(hidden);
                } catch (err) {
                    e.preventDefault();
                    alert('Le champ "Configuration avancée" doit contenir du JSON valide.');
                }
            }
        });
    }

    window.openCreateModal = openCreateModal;
    window.openEditModal = openEditModal;
    window.onConnectorTypeChange = onConnectorTypeChange;
    window.testFromModal = testFromModal;
    window.runTest = runTest;
    window.togglePasswordVisibility = togglePasswordVisibility;

    const successMessage = document.getElementById('success-message');
    if (successMessage) {
        setTimeout(function () {
            successMessage.style.transition = 'opacity 0.5s ease';
            successMessage.style.opacity = '0';
            setTimeout(function () { successMessage.remove(); }, 500);
        }, 10000);
    }

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
        sidebar.querySelectorAll('.nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('open');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                }
            });
        });
    }

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

    document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.openModal;
            var modal = document.getElementById(id);
            if (modal) modal.classList.add('open');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(function (m) { m.classList.remove('open'); });
        }
    });

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            toast('Copié dans le presse-papiers.', 'success');
        }).catch(function () { toast('Impossible de copier.', 'error'); });
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

    var eventContainer = document.getElementById('event-checks-container');
    if (eventContainer) {
        fetch('/webhooks/event-types?direction=inbound')
            .then(function (r) { return r.json(); })
            .then(function (response) {
                eventContainer.innerHTML = '';
                var types = response.event_types || response;
                if (!types || types.length === 0) {
                    eventContainer.innerHTML = '<p class="text-muted">Aucun événement disponible.</p>';
                    return;
                }
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

    function getCsrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

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
                var res = data.result || data;
                if (res.success) toast('Endpoint OK (' + res.status_code + ') — ' + res.response_time_ms + 'ms', 'success');
                else if (res.status === 'timeout') toast('Timeout', 'warning');
                else toast('Erreur HTTP ' + (res.status_code || 'undefined'), 'error');
                setTimeout(function () { location.reload(); }, 1000);
            })
            .catch(function () { toast('Erreur réseau.', 'error'); })
            .finally(function () { btn.innerHTML = original; btn.disabled = false; });
        });
    });

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

    // ── Webhook — Ouvrir Modale Création ──
    document.querySelectorAll('[data-open-modal="webhook-modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = document.getElementById('webhook-modal');
            var form = document.getElementById('webhook-form');
            if (modal && form) {
                document.querySelector('#webhook-modal h3').textContent = 'Ajouter un Webhook';
                form.reset();
                var hiddenId = document.getElementById('wh-id');
                if (hiddenId) hiddenId.value = '';
                document.querySelectorAll('input[name="event_types[]"]').forEach(function(cb) { cb.checked = false; });
                modal.classList.add('open');
            }
        });
    });

    // ── Webhook — Modifier ──
    document.querySelectorAll('[data-edit-webhook]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var whId = this.dataset.editWebhook;
            var modal = document.getElementById('webhook-modal');
            var form = document.getElementById('webhook-form');
            if (!modal || !form) return;

            document.querySelector('#webhook-modal h3').textContent = 'Modifier le webhook';

            fetch('/webhooks/' + whId + '/edit-data')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    form.querySelector('[name="name"]').value = data.name;
                    form.querySelector('[name="application_id"]').value = data.application_id;
                    form.querySelector('[name="auth_method"]').value = data.auth_method;
                    form.querySelector('[name="min_severity_level"]').value = data.min_severity_level;
                    form.querySelector('[name="api_key_id"]').value = data.api_key_id || '';

                    document.querySelectorAll('input[name="event_types[]"]').forEach(function(cb) {
                        cb.checked = data.event_types.includes(parseInt(cb.value));
                    });

                    var hiddenId = document.getElementById('wh-id');
                    if (!hiddenId) {
                        hiddenId = document.createElement('input');
                        hiddenId.type = 'hidden';
                        hiddenId.id = 'wh-id';
                        hiddenId.name = 'id';
                        form.appendChild(hiddenId);
                    }
                    hiddenId.value = whId;

                    modal.classList.add('open');
                })
                .catch(function() { toast('Impossible de charger le webhook.', 'error'); });
        });
    });

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

    var webhookForm = document.getElementById('webhook-form');
    if (webhookForm) {
        webhookForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(this);
            var eventTypes = fd.getAll('event_types[]');
            
            var whId = document.getElementById('wh-id') ? document.getElementById('wh-id').value : '';
            var isEdit = whId && whId !== '';
            var url = isEdit ? '/webhooks/' + whId : '/webhooks';
            var method = isEdit ? 'PUT' : 'POST';

            var payload = {
                name: fd.get('name'),
                direction: fd.get('direction'), 
                scope: fd.get('scope'),         
                application_id: fd.get('application_id'),
                auth_method: fd.get('auth_method'),
                api_key_id: fd.get('api_key_id') || null,
                min_severity_level: fd.get('min_severity_level'),
                event_types: eventTypes
            };
            
            fetch(url, {
                method: method,
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

    const form = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveSettingsBtn');
    
    if (form && saveBtn) {
        const noChangesText = document.getElementById('noChangesText');
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        const activeTabInput = document.getElementById('active_tab_input');
        
        let hasChanged = false;

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

        form.addEventListener('input', function() {
            if (!hasChanged) {
                hasChanged = true;
                saveBtn.disabled = false;
                saveBtn.classList.add('btn-active-state');
                if (noChangesText) noChangesText.style.display = 'none';
            }
        });

        form.addEventListener('submit', function() {
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';
            saveBtn.disabled = true;
        });
    }

    function initGrafanaMonitoring() {
        const panels = document.querySelectorAll('.grafana-panel');
        if (panels.length === 0) return;

        async function loadAllMetrics() {
            panels.forEach(async (panel) => {
                const serverId = panel.dataset.serverId;
                try {
                    const response = await fetch(`/monitoring/servers/${serverId}/metrics`);
                    if (!response.ok) return;
                    const data = await response.json();
                    if (!data.success) return;

                    const statusEl = document.getElementById(`status-${serverId}`);
                    if (data.status === 'online') {
                        statusEl.innerHTML = '<span class="status-dot online"></span> En ligne';
                    } else {
                        statusEl.innerHTML = '<span class="status-dot offline"></span> Hors ligne';
                    }

                    const cpuVal = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                    const cpuPct = data.cpu !== null ? data.cpu : 0;
                    document.getElementById(`cpu-val-${serverId}`).textContent = cpuVal;
                    const cpuBar = document.getElementById(`cpu-bar-${serverId}`);
                    cpuBar.style.width = `${cpuPct}%`;
                    cpuBar.className = 'metric-bar';
                    if (cpuPct > 80) cpuBar.classList.add('critical');
                    else if (cpuPct > 60) cpuBar.classList.add('warning');

                    const ramVal = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';
                    const ramPct = data.memory !== null ? data.memory : 0;
                    document.getElementById(`ram-val-${serverId}`).textContent = ramVal;
                    const ramBar = document.getElementById(`ram-bar-${serverId}`);
                    ramBar.style.width = `${ramPct}%`;
                    ramBar.className = 'metric-bar';
                    if (ramPct > 75) ramBar.classList.add('critical');
                    else if (ramPct > 60) ramBar.classList.add('warning');
                    
                                // Gestion du DISK
                    const diskVal = data.disk !== null ? `${data.disk.toFixed(1)} %` : '-- %';
                    const diskPct = data.disk !== null ? data.disk : 0;
                    document.getElementById(`disk-val-${serverId}`).textContent = diskVal;
                    const diskBar = document.getElementById(`disk-bar-${serverId}`);
                    diskBar.style.width = `${diskPct}%`;
                    diskBar.className = 'metric-bar';
                    if (diskPct > 80) diskBar.classList.add('critical');
                    else if (diskPct > 60) diskBar.classList.add('warning');

                } catch (error) {
                    console.error('Erreur monitoring pour serveur ' + serverId, error);
                }
            });
        }
        loadAllMetrics();
        setInterval(loadAllMetrics, 15000);
    }
    initGrafanaMonitoring();

       function initServerMonitoringDetail() {
        const monitorCard = document.querySelector('.monitor-card');
        if (!monitorCard) return;

        const serverId = monitorCard.dataset.serverId;
        const statusEl = document.getElementById('server-status');
        
        const cpuEl = document.getElementById('cpu-value');
        const memEl = document.getElementById('memory-value');
        const cpuBar = document.getElementById('cpu-bar');
        const ramBar = document.getElementById('ram-bar');
        
        const diskEl = document.getElementById('disk-value');
        const diskBar = document.getElementById('disk-bar');

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

                // --- CPU ---
                cpuEl.textContent = data.cpu !== null ? `${data.cpu.toFixed(1)} %` : '-- %';
                if (cpuBar) {
                    cpuBar.style.width = `${data.cpu ?? 0}%`;
                    cpuBar.className = 'metric-bar'; // Reset
                    if (data.cpu > 80) cpuBar.classList.add('critical');
                    else if (data.cpu > 60) cpuBar.classList.add('warning');
                }

                // --- RAM ---
                memEl.textContent = data.memory !== null ? `${data.memory.toFixed(1)} %` : '-- %';
                if (ramBar) {
                    ramBar.style.width = `${data.memory ?? 0}%`;
                    ramBar.className = 'metric-bar'; // Reset
                    if (data.memory > 80) ramBar.classList.add('critical');
                    else if (data.memory > 60) ramBar.classList.add('warning');
                }

                // --- DISK ---
                if (diskEl) {
                    diskEl.textContent = data.disk !== null ? `${data.disk.toFixed(1)} %` : '-- %';
                }
                if (diskBar) {
                    diskBar.style.width = `${data.disk ?? 0}%`;
                    diskBar.className = 'metric-bar'; // Reset
                    if (data.disk > 80) diskBar.classList.add('critical');
                    else if (data.disk > 60) diskBar.classList.add('warning');
                }
                //--NETWORK
                const netEl = document.getElementById('network-value');
                if (netEl) {
                    netEl.textContent = data.network !== null ? `${data.network.toFixed(2)} MB/s` : '-- MB/s';
                }

            } catch (error) {
                console.error('Erreur monitoring:', error);
                statusEl.textContent = 'Erreur réseau';
                statusEl.style.color = 'var(--red)';
            }
        }

        loadMetrics();
        setInterval(loadMetrics, 15000);
    }
    initServerMonitoringDetail();

    document.querySelectorAll('.action-dropdown-toggle').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll('.action-dropdown-menu.open').forEach(function(openMenu) {
                if (openMenu !== menu) openMenu.classList.remove('open');
            });
            menu.classList.toggle('open');
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.action-dropdown-menu.open').forEach(function(menu) {
            menu.classList.remove('open');
        });
    });
        /* ==========================================================
       MODALE APPLICATION - GESTION DES ONGLETS
       ========================================================== */
    const appModal = document.getElementById('application-modal');
    if (appModal) {
        const appTabs = appModal.querySelectorAll('.tab-btn');
        const appTabContents = appModal.querySelectorAll('.tab-content');

        appTabs.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.dataset.tab;
                appTabs.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                appTabContents.forEach(c => c.classList.remove('active'));
                const tabContent = appModal.querySelector('#tab-' + tab);
                if (tabContent) tabContent.classList.add('active');
            });
        });
    }
        // monitoring applicatif 
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

       // 1. Au clic sur un onglet, on mémorise son nom dans l'URL
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            tabContents.forEach(c => c.classList.remove('active'));
            
            // SÉCURITÉ : On vérifie que l'onglet existe avant d'ajouter la classe
            const tabElement = document.getElementById('tab-' + tab);
            if (tabElement) {
                tabElement.classList.add('active');
            }
            
            history.replaceState(null, null, '#tab-' + tab); // Mémorise l'onglet
        });
    });
    // 2. Au chargement de la page, on lit l'URL pour réouvrir le bon onglet
    const initialTab = window.location.hash ? window.location.hash.replace('#tab-', '') : 'overview';
    const activeTabBtn = document.querySelector(`.tab-btn[data-tab="${initialTab}"]`);
    const activeTabContent = document.getElementById('tab-' + initialTab);
    
    if (activeTabBtn && activeTabContent) {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        activeTabBtn.classList.add('active');
        activeTabContent.classList.add('active');
    }
    

    // Tri des vulnérabilités par CVSS décroissant (MF-29)
    const sortBtn = document.getElementById('sort-cvss-btn');
    if (sortBtn) {
        sortBtn.addEventListener('click', function() {
            const tbody = document.querySelector('#vulns-table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => parseFloat(b.dataset.cvss) - parseFloat(a.dataset.cvss));
            rows.forEach(row => tbody.appendChild(row));
        });
    }

    // Filtre des logs par niveau (MF-32)
    const logFilter = document.getElementById('log-filter');
    if (logFilter) {
        logFilter.addEventListener('change', function() {
            const level = this.value;
            document.querySelectorAll('#logs-table tbody tr').forEach(row => {
                if (!level || row.dataset.level === level) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    /* ==========================================================
       GRAPHIQUE DE LATENCE APPLICATION (Façon Grafana)
       ========================================================== */
    const appResponseChart = document.getElementById('appResponseTimeChart');
    if (appResponseChart && typeof Chart !== 'undefined') {
        
        // Récupère les vraies données envoyées par Laravel depuis la variable globale
        const rawData = window.latencyHistory || [];
        
        // Sépare les labels (heures) et les valeurs (ms) pour Chart.js
        const labels = rawData.map(item => item.x);
        const dataValues = rawData.map(item => item.y);

        // Si pas de données, on met un tableau vide pour ne pas faire planter Chart.js
        if (labels.length > 0) {
            new Chart(appResponseChart, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Temps de réponse (ms)',
                        data: dataValues,
                        borderColor: '#1d4a40',
                        backgroundColor: 'rgba(29, 74, 64, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#eef1ef' },
                            ticks: { callback: (value) => value + ' ms' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    }
     // Gestion du filtre de période du graphique de latence
         
    const latencyRangeSelect = document.getElementById('latency-range-select');
    if (latencyRangeSelect) {
        latencyRangeSelect.addEventListener('change', function() {
            // On garde le hashtag de l'onglet pour ne pas perdre l'onglet Performance
            const currentHash = window.location.hash || '';
            window.location.href = window.location.pathname + '?range=' + this.value + currentHash;
        });
    }  
    const availabilityCanvas = document.getElementById('availabilityChart');
    if (availabilityCanvas && typeof Chart !== 'undefined') {
        
        // On lit les vraies données envoyées par Laravel
        const labels = window.availabilityLabels || [];
        const data = window.availabilityData || [];

        new Chart(availabilityCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Disponibilité globale (%)',
                    data: data,
                    borderColor: '#1d4a40',
                    backgroundColor: 'rgba(29, 74, 64, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: '#eef1ef' },
                        ticks: { callback: (value) => value + '%' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
        // Graphique Tendance Disponibilité
    const availTrendCtx = document.getElementById('availTrendChart');
    if (availTrendCtx && typeof Chart !== 'undefined') {
        new Chart(availTrendCtx, {
            type: 'line',
            data: {
                labels: window.availTrendLabels || [],
                datasets: [{
                    label: 'Disponibilité (%)', // C'est la "nomination" qui s'affichera
                    data: window.availTrendData || [],
                    borderColor: '#56825E',
                    backgroundColor: 'rgba(86, 130, 94, 0.1)',
                    fill: true, tension: 0.4, pointRadius: 3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: true, position: 'top' } // Affiche la nomination
                },
                scales: { y: { min: 0, max: 100, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } }
            }
        });
    }

    // Graphique Tendance Temps de réponse
    const respTrendCtx = document.getElementById('respTrendChart');
    if (respTrendCtx && typeof Chart !== 'undefined') {
        new Chart(respTrendCtx, {
            type: 'line',
            data: {
                labels: window.respTrendLabels || [],
                datasets: [{
                    label: 'Temps de réponse (ms)', // C'est la "nomination"
                    data: window.respTrendData || [],
                    borderColor: '#1d4a40',
                    backgroundColor: 'rgba(29, 74, 64, 0.1)',
                    fill: true, tension: 0.4, pointRadius: 3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: true, position: 'top' } // Affiche la nomination
                },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' ms' } }, x: { grid: { display: false } } }
            }
        });
    }
    /* ==========================================================
       CARTE DE DÉPENDANCES (MF-38)
       ========================================================== */
    window.addEventListener('load', function() {
        const dependencyNetwork = document.getElementById('dependency-network');
        if (dependencyNetwork && typeof vis !== 'undefined') {
            
            const nodes = new vis.DataSet(window.dependencyNodes || []);
            const edges = new vis.DataSet(window.dependencyEdges || []);

            const data = { nodes: nodes, edges: edges };
            
            const options = {
                layout: { improvedLayout: true },
                physics: { 
                    stabilization: true,
                    barnesHut: { gravitationalConstant: -8000, springConstant: 0.04 }
                },
                interaction: { 
                    hover: true, 
                    zoomView: true
                },
                edges: {
                    arrows: { to: { enabled: true, scaleFactor: 0.5 } },
                    smooth: true,
                    color: { color: '#8a9490', highlight: '#1d4a40' }
                },
                nodes: {
                    font: { color: 'white', size: 14 }
                }
            };

            new vis.Network(dependencyNetwork, data, options);
        }
    });
    /* ==========================================================
       GRAPHIQUE DE COMPARAISON (MF-36)
       ========================================================== */
    const comparisonCtx = document.getElementById('comparisonChart');
    if (comparisonCtx && typeof Chart !== 'undefined') {
        
        const labels = window.compareLabels || [];
        const data1 = window.compareData1 || [];
        const data2 = window.compareData2 || [];

        // On détermine l'unité en cherchant les parenthèses dans le label envoyé par Laravel
        const metricText = window.compareMetric || '';
        let unit = '';
        if (metricText.includes('%')) unit = '%';
        else if (metricText.includes('ms')) unit = ' ms';
        else if (metricText.includes('Req/s')) unit = ' Req/s';
        else if (metricText.includes('MB/s')) unit = ' MB/s';

        new Chart(comparisonCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { 
                        label: window.compareName1, 
                        data: data1, 
                        borderColor: '#1d4a40', 
                        backgroundColor: 'rgba(29, 74, 64, 0.1)', 
                        fill: false, tension: 0.4, borderWidth: 2 
                    },
                    { 
                        label: window.compareName2, 
                        data: data2, 
                        borderColor: '#e08e3e', 
                        backgroundColor: 'rgba(224, 142, 62, 0.1)', 
                        fill: false, tension: 0.4, borderWidth: 2 
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { 
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + unit;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: unit === '%', 
                        grid: { color: '#eef1ef' }, 
                        ticks: { callback: v => v + unit } 
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
    /* ==========================================================
       GRAPHIQUES HISTORIQUES SERVEUR (MF-12) & FILTRES
       ========================================================== */
    
    // Options de base pour les graphiques
    const chartOptions = {
        responsive: true, 
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { 
            y: { beginAtZero: true, grid: { color: '#eef1ef' } }, 
            x: { grid: { display: false } } 
        }
    };

    // 1. Initialisation des 4 graphiques
    const serverCpuCtx = document.getElementById('serverCpuChart');
    if (serverCpuCtx && typeof Chart !== 'undefined') {
        Chart.getChart(serverCpuCtx)?.destroy();
        new Chart(serverCpuCtx, {
            type: 'line',
            data: { labels: window.serverTimeLabels || [], datasets: [{ label: 'CPU (%)', data: window.serverCpuHistory || [], borderColor: '#1d4a40', backgroundColor: 'rgba(29, 74, 64, 0.1)', fill: true, tension: 0.4, pointRadius: 2 }] },
            options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, max: 100, ticks: { callback: v => v + '%' } } } }
        });
    }

    const serverRamCtx = document.getElementById('serverRamChart');
    if (serverRamCtx && typeof Chart !== 'undefined') {
        Chart.getChart(serverRamCtx)?.destroy();
        new Chart(serverRamCtx, {
            type: 'line',
            data: { labels: window.serverTimeLabels || [], datasets: [{ label: 'RAM (%)', data: window.serverRamHistory || [], borderColor: '#e08e3e', backgroundColor: 'rgba(224, 142, 62, 0.1)', fill: true, tension: 0.4, pointRadius: 2 }] },
            options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, max: 100, ticks: { callback: v => v + '%' } } } }
        });
    }

    const serverDiskCtx = document.getElementById('serverDiskChart');
    if (serverDiskCtx && typeof Chart !== 'undefined') {
        Chart.getChart(serverDiskCtx)?.destroy();
        new Chart(serverDiskCtx, {
            type: 'line',
            data: { labels: window.serverTimeLabels || [], datasets: [{ label: 'Disque (%)', data: window.serverDiskHistory || [], borderColor: '#c0392b', backgroundColor: 'rgba(192, 57, 43, 0.1)', fill: true, tension: 0.4, pointRadius: 2 }] },
            options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, max: 100, ticks: { callback: v => v + '%' } } } }
        });
    }

    const serverNetworkCtx = document.getElementById('serverNetworkChart');
    if (serverNetworkCtx && typeof Chart !== 'undefined') {
        Chart.getChart(serverNetworkCtx)?.destroy();
        new Chart(serverNetworkCtx, {
            type: 'line',
            data: { labels: window.serverTimeLabels || [], datasets: [{ label: 'Réseau (MB/s)', data: window.serverNetworkHistory || [], borderColor: '#56825E', backgroundColor: 'rgba(86, 130, 94, 0.1)', fill: true, tension: 0.4, pointRadius: 2 }] },
            options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, ticks: { callback: v => v + ' MB/s' } } } }
        });
    }

    // 2. Gestion des filtres de période (30min, 24h, 48h, 7j, 30j)
    const serverRangeSelects = document.querySelectorAll('[data-chart-range]');
    serverRangeSelects.forEach(select => {
        select.addEventListener('change', function() {
            const range = this.value;
            
            // On met à jour le texte de TOUS les menus déroulants pour qu'ils restent synchronisés
            serverRangeSelects.forEach(s => { s.value = range; });

            let nbPoints = 24;
            if (range === '30min') nbPoints = 30;
            else if (range === '48h') nbPoints = 48;
            else if (range === '7d') nbPoints = 168;
            else if (range === '30d') nbPoints = 120;

            // On régénère de fausses données pour la période choisie
            const newLabels = [];
            const newCpuData = [];
            const newRamData = [];
            const newDiskData = [];
            const newNetData = [];
            
            for (let i = nbPoints; i > 0; i--) {
                newLabels.push('T-' + i);
                newCpuData.push(Math.floor(Math.random() * 60) + 20);
                newRamData.push(Math.floor(Math.random() * 40) + 40);
                newDiskData.push(Math.floor(Math.random() * 30) + 50);
                newNetData.push(Math.floor(Math.random() * 50) + 1);
            }

            // On met à jour les 4 graphiques
            const cpuChart = Chart.getChart('serverCpuChart');
            if (cpuChart) { cpuChart.data.labels = newLabels; cpuChart.data.datasets[0].data = newCpuData; cpuChart.update(); }
            
            const ramChart = Chart.getChart('serverRamChart');
            if (ramChart) { ramChart.data.labels = newLabels; ramChart.data.datasets[0].data = newRamData; ramChart.update(); }

            const diskChart = Chart.getChart('serverDiskChart');
            if (diskChart) { diskChart.data.labels = newLabels; diskChart.data.datasets[0].data = newDiskData; diskChart.update(); }

            const netChart = Chart.getChart('serverNetworkChart');
            if (netChart) { netChart.data.labels = newLabels; netChart.data.datasets[0].data = newNetData; netChart.update(); }
        });
    });    
   /* ==========================================================
   GRAPHIQUE POSTURE SÉCURITÉ (DEMI-CERCLE)
   ========================================================== */
    const securityCtx = document.getElementById('securityChart');
    if (securityCtx && typeof Chart !== 'undefined') {
        
        // 1. On détruit l'ancien graphique s'il existe déjà sur ce canvas
        let existingChart = Chart.getChart(securityCtx);
        if (existingChart) {
            existingChart.destroy();
        }

        // 2. On crée le nouveau
        const hexColors = (window.securityColors || []).map(c => {
            if (c.includes('sage')) return '#56825E';
            if (c.includes('orange')) return '#e08e3e';
            return '#c0392b';
        });

        new Chart(securityCtx, {
            type: 'doughnut',
            data: {
                labels: window.securityLabels || [],
                datasets: [{
                    label: 'Score',
                    data: window.securityData || [],
                    backgroundColor: hexColors,
                    borderColor: 'transparent',
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '70%',
                circumference: 180,
                rotation: 270,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ' : ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    // Fonction qui interroge le serveur
    function checkCriticalAlerts() {
        fetch('/alerts/check-critical')
            .then(response => response.json())
            .then(data => {
                if (data.has_critical && !isUrgentAlerting) {
                    triggerUrgentAlert(data.alerts[0]); // Déclenche le son et le clignotement
                }
            });
    }

    // Lancer la vérification toutes les 30 secondes
    setInterval(checkCriticalAlerts, 30000);
    checkCriticalAlerts(); // Lancer une fois au chargement
    const notifToggle = document.getElementById('notif-toggle');
    const notifDropdown = document.getElementById('notif-dropdown');

    if (notifToggle) {
        notifToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('open');
        });

        document.addEventListener('click', function(e) {
            if (!notifDropdown.contains(e.target) && !notifToggle.contains(e.target)) {
                notifDropdown.classList.remove('open');
            }
        });
    }

        // Fonction pour charger les alertes dans le menu
        function loadAlertsInTopbar() {
            fetch('/alerts/check-critical') // On réutilise cette route pour la démo
                .then(response => response.json())
                .then(data => {
                    const notifCount = document.getElementById('notif-count');
                    const notifList = document.getElementById('notif-list');
                    
                    let activeAlerts = data.alerts || [];
                    let count = activeAlerts.length;

                    if (count > 0) {
                        notifCount.textContent = count;
                        notifCount.style.display = 'flex';
                    } else {
                        notifCount.style.display = 'none';
                    }

                    // Construire la liste
                    if (count > 0) {
                        notifList.innerHTML = activeAlerts.map(alert => `
                            <a href="/alerts/${alert.id}" style="display: flex; gap: 12px; padding: 12px; border-bottom: 1px solid var(--border-color); text-decoration: none; color: inherit;">
                                <div style="color: ${alert.priority === 'critical' ? 'var(--red)' : 'var(--orange)'};">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div style="flex: 1;">
                                    <strong style="display: block; font-size: 14px;">${alert.title}</strong>
                                    <small style="color: var(--text-muted);">${alert.source || 'Système'} - ${alert.code || 'ALR-'+alert.id}</small>
                                </div>
                            </a>
                        `).join('');
                    } else {
                        notifList.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-muted);"><i class="fa-solid fa-check-circle" style="color: var(--sage-green); font-size: 24px; margin-bottom: 10px; display: block;"></i>Aucune alerte active.</div>';
                    }
                })
                .catch(error => console.error('Erreur:', error));
        }

        // Charger au démarrage
        loadAlertsInTopbar();
        // Recharger toutes les 30 secondes
        setInterval(loadAlertsInTopbar, 30000);
//popup
    function triggerUrgentAlert(alertData) {
        isUrgentAlerting = true;
        document.body.classList.add('critical-alert-flash');
        
        // A. Le pop-up HTML classique (utile si l'utilisateur est sur l'onglet)
        const popup = document.getElementById('critical-popup');
        const popupTitle = document.getElementById('popup-title');
        const popupDesc = document.getElementById('popup-description');
        
        if (popupTitle && alertData.title) popupTitle.innerText = alertData.title;
        if (popupDesc && alertData.description) popupDesc.innerText = alertData.description;
        if (popup) popup.style.display = 'block';

        // B. LA NOTIFICATION SYSTÈME (Même si l'utilisateur est sur un autre logiciel !)
        if ('Notification' in window && Notification.permission === 'granted') {
            const systemNotif = new Notification('🚨 ALERTE CRITIQUE UNIPULSE', {
                body: alertData.title + '\n' + (alertData.description || 'Intervention requise immédiatement !'),
                icon: '{{ asset("images/logo-unipulse.png") }}', // Mets une icône de ton site ici (optionnel)
                tag: 'critical-alert', // Empêche d'avoir 50 pop-up si l'alerte résonne
                requireInteraction: true // La notification reste affichée jusqu'à ce qu'on clique dessus
            });

            // Quand on clique sur la notification système, ça ouvre l'onglet du navigateur
            systemNotif.onclick = function() {
                window.focus();
                this.close();
            };
        }

        // C. Le son
        const sound = document.getElementById('alert-sound');
        const unlockBtn = document.getElementById('unlock-sound-btn');
        
        if (sound) {
            sound.loop = true;
            sound.play().then(() => {
                if (unlockBtn) unlockBtn.style.display = 'none';
            }).catch(() => {
                if (unlockBtn) unlockBtn.style.display = 'block';
            });
        }
    }
   // alertes 


        function triggerUrgentAlert(alertData) {
            isUrgentAlerting = true;
            document.body.classList.add('critical-alert-flash');
            
            const popup = document.getElementById('critical-popup');
            const popupTitle = document.getElementById('popup-title');
            const popupDesc = document.getElementById('popup-description');
            
            if (popupTitle && alertData.title) popupTitle.innerText = alertData.title;
            if (popupDesc && alertData.description) popupDesc.innerText = alertData.description;
            if (popup) popup.style.display = 'block';

            const sound = document.getElementById('alert-sound');
            const unlockBtn = document.getElementById('unlock-sound-btn');
            
            if (sound) {
                sound.loop = true;
                sound.play().then(() => {
                    if (unlockBtn) unlockBtn.style.display = 'none';
                }).catch(() => {
                    if (unlockBtn) unlockBtn.style.display = 'block';
                });
            }
        }

        // ON SÉCURISE L'ÉCOUTEUR DU BOUTON DE DÉBLOCAGE
        const unlockBtn = document.getElementById('unlock-sound-btn');
        if (unlockBtn) {
            unlockBtn.addEventListener('click', function() {
                document.getElementById('alert-sound').play().then(() => {
                    this.style.display = 'none';
                });
            });
        }

       window.stopUrgentAlert = function() {
            isUrgentAlerting = false;
            document.body.classList.remove('critical-alert-flash');
            const popup = document.getElementById('critical-popup');
            if (popup) popup.style.display = 'none';

            const sound = document.getElementById('alert-sound');
            if (sound) {
                sound.pause();
                sound.currentTime = 0;
            }

            fetch('/alerts/check-critical')
                .then(r => r.json())
                .then(data => {
                    if (data.has_critical && data.alerts.length > 0) {
                        const alertId = data.alerts[0].id;
                        
                        // On utilise POST et on ajoute _method: 'PUT' dans le corps
                        fetch('/alerts/' + alertId + '/acknowledge', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'PUT'
                            })
                        });
                    }
                });
        };
        setInterval(checkCriticalAlerts, 30000);
        checkCriticalAlerts();
        let isUrgentAlerting = false;

        // 1. Demander la permission d'envoyer des notifications système
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                // On demande la permission (le navigateur affichera une popup "Autoriser / Bloquer")
                Notification.requestPermission();
            }
        }
    // Fonction pour charger les alertes dans le menu
    function loadAlertsInTopbar() {
        const notifCount = document.getElementById('notif-count');
        const notifList = document.getElementById('notif-list');
        
        // SÉCURITÉ : Si les éléments n'existent pas, on arrête la fonction proprement
        if (!notifCount || !notifList) return;

        fetch('/alerts/check-critical')
            .then(response => response.json())
            .then(data => {
                let activeAlerts = data.alerts || [];
                let count = activeAlerts.length;

                if (count > 0) {
                    notifCount.textContent = count;
                    notifCount.style.display = 'flex'; // Affiche le badge
                } else {
                    notifCount.style.display = 'none'; // Cache le badge si 0 alerte
                }

                // Construire la liste
                if (count > 0) {
                    notifList.innerHTML = activeAlerts.map(alert => `
                        <a href="/alerts/${alert.id}" style="display: flex; gap: 12px; padding: 12px; border-bottom: 1px solid var(--border-color); text-decoration: none; color: inherit;">
                            <div style="color: ${alert.priority === 'critical' ? 'var(--red)' : 'var(--orange)'};">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div style="flex: 1;">
                                <strong style="display: block; font-size: 14px;">${alert.title}</strong>
                                <small style="color: var(--text-muted);">${alert.source || 'Système'} - ${alert.code || 'ALR-'+alert.id}</small>
                            </div>
                        </a>
                    `).join('');
                } else {
                    notifList.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-muted);"><i class="fa-solid fa-check-circle" style="color: var(--sage-green); font-size: 24px; margin-bottom: 10px; display: block;"></i>Aucune alerte active.</div>';
                }
            })
            .catch(error => console.error('Erreur:', error));
    }
    /* ==========================================================
       GRAPHIQUE ÉVOLUTION SÉCURITÉ (DASHBOARD PRINCIPAL)
       ========================================================== */
    const ctxDashboardSecurity = document.getElementById('dashboardSecurityChart');
    if (ctxDashboardSecurity && typeof Chart !== 'undefined') {
        
        // On détruit l'ancien s'il existe
        let existingChart = Chart.getChart(ctxDashboardSecurity);
        if (existingChart) {
            existingChart.destroy();
        }

        new Chart(ctxDashboardSecurity, {
            type: 'line',
            data: {
                labels: window.dashboardSecurityLabels || [],
                datasets: [{
                    label: 'Score de sécurité (%)',
                    data: window.dashboardSecurityData || [],
                    borderColor: '#56825E',
                    backgroundColor: 'rgba(86, 130, 94, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'top' } },
                scales: {
                    y: { min: 0, max: 100, grid: { display: false }, ticks: { callback: v => v + '%' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
}); // Fin du DOMContentLoaded (et fin du fichier)
