{{--
    Eigen motoricoon voor de raceweergave (12-10-2026). Twee silhouetten: sport (rijder in de
    kuip) voor categorie sport, rechtop voor de rest. Kleur via currentColor.
--}}
@if(($category ?? null) === 'sport')
<svg class="bike-icon" viewBox="0 0 56 32" aria-hidden="true">
    <g fill="none" stroke="currentColor" stroke-width="2.6"><circle cx="11" cy="24" r="6.2"/><circle cx="45" cy="24" r="6.2"/></g>
    <g fill="currentColor">
        <path d="M11 24 L24 21 L25 23.4 L12.2 25.6Z"/>
        <path d="M45 24 L40.4 13.5 L42.6 12.6 L46.6 23.2Z"/>
        <path d="M18 20.5 L15 14.5 L22 13.2 L29 11 L36 10.4 L43 12 L46.5 15.5 L44 17.6 L38 18.4 L33 21.6 L24 22.2Z"/>
        <path d="M15 14.5 L8 12.4 L9 11 L19 12.6Z"/>
        <path d="M21 13 Q23 8 30 7.8 L34.6 9.2 L34 10.8 L29 10.8 Q25.4 11.2 24 13.6Z"/>
        <path d="M24.2 13.4 L28.6 18.8 L26.8 20 L21.6 14.4Z"/>
        <circle cx="35.2" cy="7.6" r="3.1"/>
    </g>
</svg>
@else
<svg class="bike-icon" viewBox="0 0 56 32" aria-hidden="true">
    <g fill="none" stroke="currentColor" stroke-width="2.6"><circle cx="11" cy="24" r="6.2"/><circle cx="45" cy="24" r="6.2"/></g>
    <g fill="currentColor">
        <path d="M11 24 L24 21 L25 23.4 L12.2 25.6Z"/>
        <path d="M45 24 L40 11 L42.2 10.2 L46.6 23.2Z"/>
        <path d="M17 16.6 L26 15.4 L29 12.6 L37 12.2 L40.6 14 L38 17.6 L34 22 L25 22.4 L21 19.6Z"/>
        <path d="M17 16.6 L9 15 L9.6 13.6 L19 14.6Z"/>
        <path d="M39.4 11.4 L36.6 8.2 L38.2 7.4 L41.4 10.6Z"/>
        <path d="M22.6 15 L24.6 6.6 Q25.6 4.6 28 5.4 L29 6 L27.2 15Z"/>
        <path d="M26.6 7.4 L36.4 9 L36 10.8 L26 10.2Z"/>
        <path d="M24 14.6 L31.4 16 L30.8 21 L28.6 20.8 L28.8 18 L23.6 17.4Z"/>
        <circle cx="27.6" cy="2.9" r="2.9"/>
    </g>
</svg>
@endif
