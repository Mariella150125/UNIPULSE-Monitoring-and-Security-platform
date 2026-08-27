<x-modal 
    id="connector-modal" 
    title="Ajouter un connecteur" >

    <form
        id="connector-form"
        method="POST"
        action="{{ route('connectors.store') }}"
    >
        @csrf
        <input type="hidden" name="_method" id="_method" value="POST">
        <input type="hidden" name="connector_id" id="modal-connector-id" value="">


        {{-- TYPE --}}
        <div class="modal-grid-2">
            <label for="type">Type de connecteur</label>
            <select name="type" id="type" class="input-group" required onchange="onTypeChange()">
                <option value="">-- Choisir --</option>
                <option value="prometheus">Prometheus</option>
                <option value="wazuh">Wazuh</option>
            </select>
            @error('type')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>


        {{-- NOM --}}
        <div class="input-group">
            <label for="name">Nom</label>
            <input
                type="text"
                name="name"
                id="name"
                placeholder="Ex : Wazuh — Production"
                required
                value="{{ old('name') }}"
            >
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>


        {{-- URL DE BASE --}}
        <div class="input-group">
            <label for="base_url">URL de base</label>
            <input
                type="url"
                name="base_url"
                id="base_url"
                placeholder="Ex : https://wazuh.example.com"
                required
                value="{{ old('base_url') }}"
            >
            @error('base_url')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>


        {{-- PORT API --}}
        <div class="input-group" id="port-group" style="display:none;">
            <label for="api_port">Port API</label>
            <input
                type="number"
                name="api_port"
                id="api_port"
                placeholder="Ex : 55000"
                min="1"
                max="65535"
                value="{{ old('api_port') }}"
            >
            <span class="form-hint">Laisser vide pour utiliser le port de l'URL.</span>
            @error('api_port')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>


        {{-- SÉPARATEUR --}}
        <div class="form-separator">
            <span>Authentification</span>
        </div>


        {{-- IDENTIFIANT --}}
        <div class="input-group">
            <label for="auth_username">Identifiant</label>
            <input
                type="text"
                name="auth_username"
                id="auth_username"
                placeholder="Optionnel"
                value="{{ old('auth_username') }}"
            >
            @error('auth_username')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>


        {{-- MOT DE PASSE --}}
        <div class="input-group">
            <label for="auth_password">Mot de passe</label>
            <div class="password-field">
                <input
                    type="password"
                    name="auth_password"
                    id="auth_password"
                    placeholder="Optionnel"
                    value="{{ old('auth_password') }}"
                >
                <button
                    type="button"
                    class="icon-btn"
                    onclick="togglePasswordVisibility('auth_password', this)"
                    title="Afficher/Masquer"
                >
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @error('auth_password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>


        {{-- CONFIG AVANCÉE --}}
        <details class="input-group input-full">
            <summary>Configuration avancée (JSON)</summary>
            <div class="input-group" style="margin-top:10px;">
                <label for="extra_config_raw">extra_config</label>
                <textarea
                    name="extra_config_raw"
                    id="extra_config_raw"
                    rows="3"
                    placeholder='{"insecure_ssl": true}'
                >{{ old('extra_config_raw') }}</textarea>
                <span class="form-hint">JSON valide. Sera parsé automatiquement.</span>
            </div>
        </details>


        {{-- TEST AVANT D'ENREGISTRER --}}
        <div id="modal-test-area" style="display:none; margin: 15px 0;">
            <button
                type="button"
                class="usr-btn secondary"
                id="modal-test-btn"
                onclick="testFromModal()"
            >
                <i class="fa-solid fa-plug"></i>
                Tester la connexion
            </button>
            <span id="modal-test-result" style="margin-left:10px;font-size:13px;"></span>
        </div>

    </form>


    {{-- FOOTER (les boutons sont en dehors du form pour éviter le submit) --}}
    @slot('footer')
        <div class="modal-actions">
            <button type="button" class="btn btn-cancel" data-modal-close="connector-modal">
                Annuler
            </button>
            <button type="submit" class="btn btn-primary" id="modal-submit-btn" form="connector-form">
                <i class="fa-solid fa-check"></i>
                Enregistrer
            </button>
        </div>
    @endslot

</x-modal>