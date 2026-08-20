<div class="entity-details">

    <div class="entity-details-header">
        <div>
            <h2>{{ $title }}</h2>
            <p>{{ $subtitle }}</p>
        </div>

        <div>
            {{ $actions ?? '' }}
        </div>
    </div>

    <div class="entity-details-body">
        {{ $slot }}
    </div>

</div>