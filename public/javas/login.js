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
                labels: labels,
                datasets: [{
                    label: 'Alertes critiques',
                    data: [3, 5, 2, 6, 4, 7, 4],
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
                labels: labels,
                datasets: [{
                    label: 'Score de sécurité (%)',
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
            if (data.success) {
                content.innerHTML = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;"><span class="status-dot online" style="width:14px;height:14px;"></span><strong style="font-size:16px;color:var(--sage-green);">Connexion réussie</strong></div><div><strong>Temps de réponse</strong><p>' + data.response_time + ' ms</p></div><div><strong>Nouveau statut</strong><p>' + (data.status === 'connected' ? 'Connecté' : data.status) + '</p></div><div><strong>Vérifié à</strong><p>' + (data.last_check_at || '—') + '</p></div>' + (data.metadata ? '<div><strong>Détails</strong><pre style="background:var(--input-bg);padding:10px;border-radius:6px;font-size:13px;overflow-x:auto;">' + JSON.stringify(data.metadata, null, 2) + '</pre></div>' : '');
            } else {
                content.innerHTML = '<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;"><span class="status-dot offline" style="width:14px;height:14px;"></span><strong style="font-size:16px;color:var(--red);">Échec de connexion</strong></div><div><strong>Erreur</strong><p style="color:var(--red);">' + data.message + '</p></div><div><strong>Nouveau statut</strong><p>' + (data.status === 'error' ? 'En erreur' : data.status) + '</p></div>';
            }
            if (resultCard) resultCard.style.display = '';
        } catch (e) {
            const content = document.getElementById('test-result-content');
            if (content) content.innerHTML = '<div style="display:flex;align-items:center;gap:12px;"><span class="status-dot offline" style="width:14px;height:14px;"></span><strong style="color:var(--red);">Erreur réseau</strong></div><p style="color:var(--text-muted);margin-top:8px;">Impossible de contacter le serveur.</p>';
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
                    if (ramPct > 80) ramBar.classList.add('critical');
                    else if (ramPct > 60) ramBar.classList.add('warning');

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

}); // Fin du DOMContentLoaded (et fin du fichier)
