

{{-- ═══════════════════════════════════════════════════════════
     MODALES
     ═══════════════════════════════════════════════════════════ --}}

<x-modal id="api-key-modal" title="Générer une clé API">
    <form id="api-key-form">
        @csrf
        <div class="input-group">
            <label>Nom de la clé *</label>
            <input type="text" name="name" required placeholder="ex. Intégration Zapier">
        </div>
        <div class="input-group">
            <label>Permissions *</label>
            <div class="scope-checks">
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="servers:read" checked><span class="scope-check-label">servers:read</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="servers:write"><span class="scope-check-label">servers:write</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="applications:read"><span class="scope-check-label">applications:read</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="applications:write"><span class="scope-check-label">applications:write</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="alerts:read" checked><span class="scope-check-label">alerts:read</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="alerts:write"><span class="scope-check-label">alerts:write</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="reports:read"><span class="scope-check-label">reports:read</span></label>
                <label class="scope-check"><input type="checkbox" name="scopes[]" value="reports:write"><span class="scope-check-label">reports:write</span></label>
            </div>
        </div>
        <div class="input-group">
            <label>Date d'expiration</label>
            <input type="date" name="expires_at">
        </div>
    </form>
    @slot('footer')
        <button type="button" class="btn-secondary" data-modal-close="api-key-modal">Annuler</button>
        <button type="submit" form="api-key-form" class="login-btn login-btn-sm">Générer</button>
    @endslot
</x-modal>

<x-modal id="api-key-reveal" title="Clé API générée">
    <div class="key-reveal-warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Copiez cette clé maintenant — elle ne sera plus jamais affichée.
    </div>
    <div class="key-reveal-block" id="reveal-key-text"></div>
    @slot('footer')
        <button type="button" class="btn-secondary" data-modal-close="api-key-reveal">Fermer</button>
        <button type="button" class="login-btn login-btn-sm" data-copy-reveal-key>
            <i class="fa-regular fa-copy"></i> Copier
        </button>
    @endslot
</x-modal>

<x-modal id="endpoint-modal" title="Ajouter un endpoint">
    <form id="endpoint-form">
        @csrf
        <input type="hidden" name="id" id="endpoint-edit-id" value="">
        <div class="modal-grid-2">
            <div class="input-group">
                <label>Application *</label>
                <select name="application_id" required>
                    <option value="">Sélectionner...</option>
                    @foreach(\App\Models\Application::orderBy('name')->get() as $app)
                        <option value="{{ $app->id }}">{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label>Méthode HTTP *</label>
                <select name="http_method" required>
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                    <option value="HEAD">HEAD</option>
                </select>
            </div>
        </div>
        <div class="input-group">
            <label>URL *</label>
            <input type="url" name="url" required placeholder="https://mon-app.com/health">
        </div>
        <div class="input-group">
            <label>Fréquence de vérification (secondes)</label>
            <input type="number" name="frequency_seconds" value="60" min="10" max="86400" required>
        </div>
        <p class="modal-section-title">En-têtes d'authentification</p>
        <div class="header-rows" id="header-rows">
            <div class="header-row">
                <input type="text" name="headers[0][key]" placeholder="Clé (ex. Authorization)">
                <input type="text" name="headers[0][value]" placeholder="Valeur (ex. Bearer xyz)">
                <button type="button" class="icon-btn" data-remove-header><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <button type="button" class="add-header-btn" id="add-header-btn">
            <i class="fa-solid fa-plus"></i> Ajouter un en-tête
        </button>
    </form>
    @slot('footer')
        <button type="button" class="btn-secondary" data-modal-close="endpoint-modal">Annuler</button>
        <button type="submit" form="endpoint-form" class="login-btn login-btn-sm">Enregistrer</button>
    @endslot
</x-modal>

<x-modal id="webhook-modal" title="Ajouter un webhook">
    <form id="webhook-form">
        @csrf
        <div class="input-group">
            <label>Nom *</label>
            <input type="text" name="name" required placeholder="ex. Slack Alert">
        </div>
        <div class="input-group">
            <label>URL Cible *</label>
            <input type="url" name="target_url" required placeholder="https://hooks.slack.com/services/...">
        </div>
        <div class="modal-grid-2">
            <div class="input-group">
                <label>Méthode d'authentification</label>
                <select name="auth_method" id="wh-auth-method">
                    <option value="none">Aucune</option>
                    <option value="hmac_signature">HMAC Signature</option>
                    <option value="api_key">Clé API</option>
                </select>
            </div>
            <div class="input-group">
                <label>Sévérité minimale</label>
                <select name="min_severity_level">
                    <option value="0">Toutes (0)</option>
                    <option value="1">Mineure (1)</option>
                    <option value="2">Majeure (2)</option>
                    <option value="3">Critique (3)</option>
                    <option value="4">Bloquante (4)</option>
                </select>
            </div>
        </div>
        <div class="input-group" id="wh-apikey-group" style="display:none;">
            <label>Clé API</label>
            <select name="api_key_id">
                <option value="">Sélectionner...</option>
                @foreach($apiKeys->where('status', 'active') as $ak)
                    <option value="{{ $ak->id }}">{{ $ak->name }} ({{ $ak->key_prefix }}…)</option>
                @endforeach
            </select>
        </div>
        <p class="modal-section-title">Événements à écouter *</p>
        <div class="event-checks" id="event-checks-container"></div>
    </form>
    @slot('footer')
        <button type="button" class="btn-secondary" data-modal-close="webhook-modal">Annuler</button>
        <button type="submit" form="webhook-form" class="login-btn login-btn-sm">Créer</button>
    @endslot
</x-modal>

<x-modal id="delete-modal" title="Confirmer la suppression">
    <div class="delete-modal-content">
        <div class="delete-icon"><i class="fa-solid fa-trash"></i></div>
        <p id="delete-modal-text" class="text-muted"></p>
        <form id="delete-form" method="POST" class="delete-actions">
            @method('DELETE')
            @csrf
            <button type="button" class="btn btn-cancel" data-modal-close="delete-modal">Annuler</button>
            <button type="submit" class="btn-delete">Supprimer</button>
        </form>
    </div>
</x-modal>

