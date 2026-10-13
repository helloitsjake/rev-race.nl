{{--
    Sorteerbare modellijst (13-10-2026), gekoppeld aan partials/spec-plot via data-id.
    - $motors:   de modellen
    - $limit:    zoveel rijen staan er standaard, de rest achter "Toon alle"
    - $segments: optioneel, [key => label] voor een segmentfilter (A2-pagina)
    - $a2Toggle: knop "Alleen A2"
    Zonder JavaScript staan alle rijen er gewoon, gesorteerd op pk/kg.
--}}
@php
    $limit = $limit ?? 12;
    $segments = $segments ?? [];
    $a2Toggle = $a2Toggle ?? false;
    $maxRatio = max(0.01, $motors->max(fn ($m) => $m->powerToWeight()));
    $sorted = $motors->sortByDesc(fn ($m) => $m->powerToWeight())->values();
    $fmt = fn ($n) => number_format($n, 2, ',', '.');
@endphp
<div class="speclist" data-speclist data-limit="{{ $limit }}">
    <div class="speclist__controls">
        @if($segments)
            <div class="filter-scroll">
                <div class="filter" role="group" aria-label="Segment" data-key="cat">
                    <div class="filter__base">
                        <button type="button" data-value="all" aria-pressed="true">Alle {{ $motors->count() }}</button>
                        @foreach($segments as $key => $segLabel)
                            <button type="button" data-value="{{ $key }}" aria-pressed="false">{{ $segLabel }} {{ $motors->where('category', $key)->count() }}</button>
                        @endforeach
                    </div>
                    <div class="filter__pill" aria-hidden="true"></div>
                </div>
            </div>
        @endif
        <div class="speclist__sort">
            <span class="speclist__label" id="sort-label">Sorteer op</span>
            <div class="filter-scroll">
                <div class="filter filter--small" role="group" aria-labelledby="sort-label" data-key="sort">
                    <div class="filter__base">
                        <button type="button" data-value="ratio" aria-pressed="true">pk/kg</button>
                        <button type="button" data-value="pk" aria-pressed="false">vermogen</button>
                        <button type="button" data-value="kg" aria-pressed="false">laagste gewicht</button>
                        <button type="button" data-value="year" aria-pressed="false">nieuwste</button>
                    </div>
                    <div class="filter__pill" aria-hidden="true"></div>
                </div>
            </div>
            @if($a2Toggle)
                <button type="button" class="chip" data-a2-toggle aria-pressed="false"><span class="chip__box" aria-hidden="true"></span>Alleen A2</button>
            @endif
        </div>
    </div>

    <div class="speclist__panel">
        <div class="spec-row spec-row--head" aria-hidden="true">
            <span>#</span><span>Model</span><span>PK</span><span>KG</span><span>PK/KG</span>
        </div>
        <ol class="speclist__rows">
            @foreach($sorted as $i => $m)
                <li class="spec-row{{ $i >= $limit ? ' is-extra' : '' }}" data-id="{{ $m->id }}" data-pk="{{ $m->power_hp }}" data-kg="{{ $m->weight_kg }}"
                    data-ratio="{{ round($m->powerToWeight(), 4) }}" data-year="{{ $m->year }}" data-a2="{{ $m->isA2Eligible() ? 1 : 0 }}" data-cat="{{ $m->category }}">
                    <span class="spec-row__pos">{{ $i + 1 }}</span>
                    <span class="spec-row__name">
                        <a href="{{ route('brands.model', [\Illuminate\Support\Str::slug($m->brand), $m->slug()]) }}">{{ $m->brand }} {{ $m->model }}</a>
                        <small>{{ $m->year }}@if($segments) &middot; {{ $m->categoryLabel() }}@endif<span class="spec-row__mob"> &middot; {{ $m->power_hp }} pk &middot; {{ $m->weight_kg }} kg</span></small>
                        @if(! $segments && $m->isA2Eligible())<span class="a2">A2</span>@endif
                    </span>
                    <span class="spec-row__num"><b>{{ $m->power_hp }}</b><small> pk</small></span>
                    <span class="spec-row__num">{{ $m->weight_kg }}<small> kg</small></span>
                    <span class="spec-row__ratio"><b>{{ $fmt($m->powerToWeight()) }}</b><span class="meter" aria-hidden="true"><i style="--w: {{ round($m->powerToWeight() / $maxRatio, 3) }}"></i></span></span>
                </li>
            @endforeach
        </ol>
        <p class="speclist__empty" hidden>Geen modellen met deze combinatie.</p>
        @if($sorted->count() > $limit)
            <button type="button" class="btn btn--ghost speclist__more" data-more>Toon alle {{ $sorted->count() }} modellen</button>
        @endif
    </div>
</div>
