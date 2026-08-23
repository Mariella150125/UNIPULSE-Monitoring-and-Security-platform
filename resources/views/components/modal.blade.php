@props(['id' => 'mainModal', 'title' => 'Modal'])

<div id="{{ $id }}" class="modal-overlay" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true">
        
        <div class="modal-header">
            <h3>{{ $title }}</h3>
            <button type="button" class="modal-close" data-modal-close="{{ $id }}" aria-label="Fermer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="modal-footer">
                {{ $footer }}
            </div>
        @endisset

    </div>
</div>