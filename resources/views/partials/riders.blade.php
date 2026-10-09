{{--
    Jake en Rory Andreas, de twee oprichters. Eigen trackdayfoto's (9 okt 2026 aangeleverd).
    Gebruikt op de home en op Over RevRace.
--}}
<section class="chapter" @if($first ?? false) style="padding-top:clamp(28px,4vw,56px)" @else style="padding-top:0" @endif>
    <div class="wrap">
        <div class="sec-head" data-reveal>
            <div><p class="turn"><b>{{ $turn ?? 'T4' }}</b> Waarom wij dit bouwen</p>
                @if($first ?? false)<h1 style="font-size:clamp(2.25rem,5.2vw,4.25rem)">Twee broers, twee motoren en dezelfde vraag</h1>@else<h2>Twee broers, twee motoren en dezelfde vraag</h2>@endif
            </div>
            <p>RevRace is van Jake en Rory Andreas, samen bedacht en samen gebouwd. Allebei rijden we trackdays, op heel verschillende motoren.</p>
        </div>
        <div class="riders">
            <figure class="rider-card" data-reveal>
                <div class="rider-card__img"><img src="{{ asset('images/team/jake-202.jpg') }}" alt="Jake Andreas op zijn Honda CB1300 met startnummer 202, in een bocht op het circuit" loading="lazy" width="1600" height="1066"></div>
                <figcaption class="license">
                    <span class="license__label">Rijderspas</span>
                    <strong>Jake Andreas</strong>
                    <dl><dt>Motor</dt><dd>Honda CB1300 (2006)</dd><dt>Startnummers</dt><dd>8 · 50 · 82 · 202</dd><dt>Op de weg</dt><dd>10+ jaar</dd><dt>Rol</dt><dd>Bedenker, data</dd></dl>
                </figcaption>
            </figure>
            <figure class="rider-card rider-card--offset" data-reveal style="--d:100ms">
                <div class="rider-card__img"><img src="{{ asset('images/team/rory-203.jpg') }}" alt="Rory Andreas op zijn KTM 1290 Super Duke met startnummer 203, diep ingeleund in een bocht" loading="lazy" width="1600" height="1066"></div>
                <figcaption class="license">
                    <span class="license__label">Rijderspas</span>
                    <strong>Rory Andreas</strong>
                    <dl><dt>Motor</dt><dd>KTM 1290 Super Duke (2021)</dd><dt>Startnummer</dt><dd>203</dd><dt>Rol</dt><dd>Bedenker</dd></dl>
                </figcaption>
            </figure>
        </div>
        <div class="riders-text" data-reveal>
            <p>Een Honda CB1300 uit 2006 en een KTM 1290 Super Duke uit 2021: op papier liggen ze ver uit elkaar, op het circuit soms minder dan je denkt. Dat verschil tussen de folder en wat een motor onder je doet, wilden we zichtbaar maken.</p>
            <p>Daarom rekent RevRace met vermogen, gewicht, wegdek en jouw lengte, en niet met marketingteksten.</p>
            <p class="sign">Jake en Rory Andreas<span>Oprichters van RevRace</span></p>
        </div>
    </div>
</section>
