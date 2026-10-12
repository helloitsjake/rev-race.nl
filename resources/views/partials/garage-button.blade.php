{{--
    Garageknop voor publiek gecachte pagina's (vergelijk, wizard). Er staat geen gebruiker in
    de HTML: site.js post met de XSRF-cookie van een ingelogde bezoeker en valt voor gasten
    terug op registreren met ?bewaar=, waarna AuthController de motor alsnog opslaat.
    $motorIds: één of twee motor-id's. $label: optionele tekst voor de ruststand.
--}}
<a class="gbtn" href="{{ route('register', ['bewaar' => $motorIds[0]]) }}" data-garage="{{ implode(',', $motorIds) }}" data-state="idle" rel="nofollow">
    <svg class="gbtn__icon" viewBox="0 0 20 20" aria-hidden="true">
        <circle class="gbtn__ring" cx="10" cy="10" r="8" pathLength="1"/>
        <path class="gbtn__plus" d="M10 6v8M6 10h8"/>
        <path class="gbtn__check" d="M6 10.5l2.6 2.6L14 7.6" pathLength="1"/>
    </svg>
    <span class="gbtn__lab" data-lab="idle">{{ $label ?? 'Bewaar in garage' }}</span>
    <span class="gbtn__lab" data-lab="busy">Bewaren</span>
    <span class="gbtn__lab" data-lab="done">In je garage</span>
    <span class="gbtn__lab" data-lab="full">Garage vol, bekijk</span>
</a>
