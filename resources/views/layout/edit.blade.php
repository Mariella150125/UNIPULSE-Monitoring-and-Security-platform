<div class="panel">

    <div class="panel-header">
        <div>
            <h2>{{ $title }}</h2>

            @if(isset($description))
                <p>{{ $description }}</p>
            @endif
        </div>
    </div>

    <form action="{{ $action }}" method="{{ $method ?? 'POST' }}">

        @csrf

        @if(isset($httpMethod))
            @method($httpMethod)
        @endif

        <div class="form-grid">

            @foreach($fields as $field)

                <div class="form-group">

                    <label for="{{ $field['name'] }}">
                        {{ $field['label'] }}
                    </label>

                    <input
                        type="{{ $field['type'] ?? 'text' }}"
                        id="{{ $field['name'] }}"
                        name="{{ $field['name'] }}"
                        value="{{ old($field['name'], $field['value'] ?? '') }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                    >

                </div>

            @endforeach

        </div>

        <div class="form-actions">

            <a href="{{ $cancelUrl }}" class="btn btn-cancel">
                Annuler
            </a>

            <button type="submit" class="btn btn-primary">
                {{ $submitText ?? 'Enregistrer' }}
            </button>

        </div>

    </form>

</div>