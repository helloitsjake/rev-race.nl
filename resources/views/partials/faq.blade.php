{{--
    Herbruikbaar FAQ-blok voor de sjablonen die er duizenden pagina's mee vullen (vergelijking,
    modelpagina). De aanroepende view geeft $faq mee als collectie van ['q' => ..., 'a' => ...]
    en gebruikt diezelfde collectie voor het FAQPage-schema, zodat zichtbare tekst en
    gestructureerde data per definitie gelijk zijn. Google vereist dat: een FAQPage-schema met
    antwoorden die niet op de pagina staan, is een richtlijnovertreding.

    Markup en klassen zijn identiek aan het bestaande FAQ-blok op /hoe-het-werkt, zodat er geen
    nieuwe CSS bij hoeft.
--}}
@if($faq->isNotEmpty())
    <section class="chapter chapter--tight" id="faq">
        <div class="wrap">
            <span class="eyebrow">{{ $eyebrow ?? 'Veelgestelde vragen' }}</span>
            @isset($heading)
                <h2>{{ $heading }}</h2>
            @endisset
            <div class="faq">
                @foreach($faq as $item)
                    <details class="faq-item">
                        <summary><span>{{ $item['q'] }}</span></summary>
                        <p>{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
