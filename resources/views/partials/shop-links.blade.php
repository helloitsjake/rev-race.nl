{{--
    Aanbod zoeken op drie plekken, met logo. Alle links lopen via /naar/{motor}/{bestemming}
    (klikteller). $motor, $source (de ?van= waarde). simulator.js bouwt dezelfde markup na.
--}}
<div class="shops">
    <p class="shops__label">Zoek aanbod op</p>
    <div class="shops__row">
        @foreach(\App\Http\Controllers\OutboundController::shopLinks($motor, $source) as $shop)
            <a class="shop shop--{{ $shop['key'] }}" href="{{ $shop['url'] }}" target="_blank" rel="nofollow noopener" aria-label="Zoek {{ $motor->shortLabel() }} op {{ $shop['label'] }}">
                <img src="{{ $shop['logo'] }}" alt="" width="22" height="22" loading="lazy">
                <span>{{ $shop['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
