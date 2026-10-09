<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>AI-verbruik, RevRace</title>
    <style>
        :root { color-scheme: light dark; }
        body { font: 15px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 24px; background: #f6f7f9; color: #16181d; }
        @media (prefers-color-scheme: dark) { body { background: #14161a; color: #e8eaed; } }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 15px; text-transform: uppercase; letter-spacing: .06em; margin: 32px 0 8px; opacity: .6; }
        .wrap { max-width: 1100px; margin: 0 auto; }
        .sub { opacity: .6; margin: 0 0 24px; font-size: 13px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; }
        .card { background: #fff; border-radius: 10px; padding: 14px 16px; border: 1px solid rgba(0,0,0,.08); }
        @media (prefers-color-scheme: dark) { .card { background: #1d2027; border-color: rgba(255,255,255,.08); } }
        .card b { display: block; font-size: 24px; font-weight: 650; }
        .card span { font-size: 12px; opacity: .6; }
        .bar { height: 8px; border-radius: 4px; background: rgba(0,0,0,.1); overflow: hidden; margin-top: 8px; }
        .bar i { display: block; height: 100%; background: #2f8f4e; }
        .bar i.warn { background: #d99000; }
        .bar i.stop { background: #c0392b; }
        .scroll { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; font-size: 13px; background: #fff; border-radius: 10px; overflow: hidden; }
        @media (prefers-color-scheme: dark) { table { background: #1d2027; } }
        th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid rgba(0,0,0,.07); white-space: nowrap; }
        @media (prefers-color-scheme: dark) { th, td { border-color: rgba(255,255,255,.07); } }
        th { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; opacity: .55; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tr:last-child td { border-bottom: 0; }
        .tag { font-size: 11px; padding: 2px 7px; border-radius: 20px; background: rgba(0,0,0,.07); }
        @media (prefers-color-scheme: dark) { .tag { background: rgba(255,255,255,.1); } }
        .tag.success { background: #d8f0df; color: #1c5c33; }
        .tag.rejected { background: #fdeccd; color: #7a5200; }
        .tag.blocked { background: #fbdcd8; color: #8e2b1e; }
        .tag.error { background: #fbdcd8; color: #8e2b1e; }
        .tag.cached { background: #dde5f5; color: #2c4478; }
        .empty { opacity: .5; font-style: italic; padding: 12px 0; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>AI-verbruik</h1>
    <p class="sub">Elke aanroep van de OpenAI API, inclusief de geblokkeerde. Bedragen in dollars, want OpenAI rekent in dollars.</p>

    @php
        $share = $budget > 0 ? min(1, $spentToday / $budget) : 0;
        $barClass = $share >= 1 ? 'stop' : ($share >= 0.5 ? 'warn' : '');
    @endphp

    <div class="cards">
        <div class="card">
            <span>Vandaag verbruikt</span>
            <b>${{ number_format($spentToday, 4) }}</b>
            <span>van ${{ number_format($budget, 2) }} dagbudget</span>
            <div class="bar"><i class="{{ $barClass }}" style="width: {{ $share * 100 }}%"></i></div>
        </div>
        <div class="card">
            <span>Status</span>
            <b>{{ $share >= 1 ? 'Gestopt' : 'Actief' }}</b>
            <span>{{ $share >= 1 ? 'Dagbudget bereikt, er gaan vandaag geen calls meer uit' : 'Nog ' . number_format(max(0, $budget - $spentToday), 4) . ' dollar ruimte' }}</span>
        </div>
        <div class="card">
            <span>Limiet per account</span>
            <b>{{ $userLimit }}</b>
            <span>nieuwe motors per 24 uur</span>
        </div>
    </div>

    <h2>Per dag, laatste 30 dagen</h2>
    <div class="scroll">
        <table>
            <tr><th>Dag</th><th class="num">Kosten</th><th class="num">Betaalde calls</th><th class="num">Geblokkeerd</th><th class="num">Uit cache</th><th class="num">Totaal</th></tr>
            @forelse ($perDay as $row)
                <tr>
                    <td>{{ $row->dag }}</td>
                    <td class="num">${{ number_format((float) $row->kosten, 4) }}</td>
                    <td class="num">{{ $row->betaald }}</td>
                    <td class="num">{{ $row->geblokkeerd }}</td>
                    <td class="num">{{ $row->uit_cache }}</td>
                    <td class="num">{{ $row->pogingen }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">Nog geen AI-calls vastgelegd.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Naar uitkomst, laatste 30 dagen</h2>
    <div class="scroll">
        <table>
            <tr><th>Uitkomst</th><th class="num">Aantal</th><th class="num">Kosten</th></tr>
            @forelse ($byOutcome as $row)
                <tr>
                    <td><span class="tag {{ $row->outcome }}">{{ $row->outcome }}</span></td>
                    <td class="num">{{ $row->aantal }}</td>
                    <td class="num">${{ number_format((float) $row->kosten, 4) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Nog niets.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Drukste IP-adressen</h2>
    <div class="scroll">
        <table>
            <tr><th>IP</th><th class="num">Pogingen</th><th class="num">Geblokkeerd</th><th class="num">Kosten</th><th>Laatst</th></tr>
            @forelse ($topIps as $row)
                <tr>
                    <td>{{ $row->ip_address }}</td>
                    <td class="num">{{ $row->pogingen }}</td>
                    <td class="num">{{ $row->geblokkeerd }}</td>
                    <td class="num">${{ number_format((float) $row->kosten, 4) }}</td>
                    <td>{{ $row->laatst }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Nog niets.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Drukste accounts</h2>
    <div class="scroll">
        <table>
            <tr><th>Account</th><th class="num">Pogingen</th><th class="num">Kosten</th><th>Laatst</th></tr>
            @forelse ($topUsers as $row)
                <tr>
                    <td>{{ $row->user?->email ?? '#' . $row->user_id }}</td>
                    <td class="num">{{ $row->pogingen }}</td>
                    <td class="num">${{ number_format((float) $row->kosten, 4) }}</td>
                    <td>{{ $row->laatst }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Nog niets.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Meest herhaalde afwijzingen</h2>
    <p class="sub">Een zoekopdracht met veel hits is geen bezoeker die zich vertypt. Deze kosten sinds de negatieve cache niets meer.</p>
    <div class="scroll">
        <table>
            <tr><th>Zoekopdracht</th><th class="num">Hits</th><th>Eerst gezien</th><th>Laatst gezien</th></tr>
            @forelse ($topRejections as $row)
                <tr>
                    <td>{{ $row->original_query }}</td>
                    <td class="num">{{ $row->hits }}</td>
                    <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $row->last_seen_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Nog geen afwijzingen vastgelegd.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Laatste 50 aanroepen</h2>
    <div class="scroll">
        <table>
            <tr><th>Tijd</th><th>Uitkomst</th><th>Doel</th><th>Account</th><th>IP</th><th>Zoekopdracht</th><th class="num">In</th><th class="num">Uit</th><th class="num">Kosten</th></tr>
            @forelse ($recent as $row)
                <tr>
                    <td>{{ $row->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td><span class="tag {{ $row->outcome }}">{{ $row->outcome }}</span></td>
                    <td>{{ $row->purpose }}</td>
                    <td>{{ $row->user?->email ?? '—' }}</td>
                    <td>{{ $row->ip_address ?? '—' }}</td>
                    <td>{{ $row->query ?? '—' }}</td>
                    <td class="num">{{ $row->input_tokens }}</td>
                    <td class="num">{{ $row->output_tokens }}</td>
                    <td class="num">${{ number_format((float) $row->cost_usd, 6) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty">Nog niets.</td></tr>
            @endforelse
        </table>
    </div>
</div>
</body>
</html>
