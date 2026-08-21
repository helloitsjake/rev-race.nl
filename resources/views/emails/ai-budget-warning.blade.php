Het AI-dagbudget van RevRace loopt vol.

Verbruikt vandaag: ${{ number_format($spentUsd, 4) }}
Dagbudget:         ${{ number_format($budgetUsd, 2) }}

Is het budget op, dan gaan er vandaag geen AI-calls meer uit. Bezoekers kunnen alles
blijven zoeken wat al in de database staat; alleen een nieuwe motor laten opzoeken
werkt dan niet meer tot morgen.

Bekijk wie het verbruikt heeft op {{ rtrim(config('app.url'), '/') }}/ai-gebruik/{{ config('ai.dashboard_token') }}

Is dit misbruik, dan kun je het dagbudget verlagen met AI_DAILY_BUDGET_USD in .env,
of de betreffende gebruiker blokkeren.
