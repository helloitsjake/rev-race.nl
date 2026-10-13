{{--
    Partnerplek tussen ranglijsten (13-10-2026). Licht vlak op asfalt, zodat hij opvalt tussen
    de donkere rijen. De knop gaat direct naar de partner, met UTM-tags ($source als utm_content).
    Het eerste aanbod (offers[0]) is de haak; zonder aanbod valt hij terug op de beschrijving.
--}}
@php
    $offer = collect($partner->offers ?? [])->first();
    $site = $partner->outboundUrl($offer['url'] ?? null, $source ?? 'ranglijst');
    $image = config('partners.' . $partner->slug . '.spot_image') ?? $partner->hero_image;
@endphp
<aside class="pspot" aria-label="Partner: {{ $partner->name }}">
    @if($image)
        <div class="pspot__media">
            <img src="{{ $partner->assetUrl($image) }}" alt="" loading="lazy" width="640" height="420">
            @if($partner->logo_url)<span class="pspot__logo"><img src="{{ $partner->assetUrl($partner->logo_url) }}" alt="{{ $partner->name }}" width="120" height="48"></span>@endif
        </div>
    @endif
    <div class="pspot__body">
        <p class="pspot__tag">Partner &middot; {{ $partner->category }}</p>
        @if($ridden = config('partners.' . $partner->slug . '.ridden'))<p class="ridden__badge ridden__badge--small">{{ $ridden['short'] }}</p>@endif
        <h3 class="pspot__title">{{ $offer ? $offer['title'] : $partner->name }}</h3>
        <p class="pspot__text">{{ $offer ? $offer['text'] : $partner->description }}</p>
        @if($offer && ! empty($offer['points']))
            <ul class="pspot__points">
                @foreach($offer['points'] as $point)
                    <li>{{ $point }}</li>
                @endforeach
            </ul>
        @endif
        <div class="pspot__actions">
            @if($site)
                <a class="btn btn--primary" href="{{ $site }}" rel="sponsored noopener" target="_blank">Bekijk data en prijzen <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13L13 5M6 5h7v7"/></svg></a>
            @endif
            <a class="pspot__more" href="{{ route('partners.show', $partner) }}">Meer over {{ $partner->name }}</a>
        </div>
    </div>
</aside>
