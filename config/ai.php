<?php

return [

    /*
     * Harde daglimiet voor alles wat de Anthropic API kost, voor de hele site samen.
     * Is dit bedrag op, dan gaat er die dag geen enkele AI-call meer uit: niet voor
     * bezoekers, niet voor de nieuwscrawler. Dit is de noodrem die misbruik begrensd
     * houdt, ook als alle andere limieten falen.
     *
     * Anthropic rekent in dollars, dus dit bedrag staat ook in dollars. $2.20 is
     * ongeveer 2 euro, dat is ruwweg 150 nieuwe motors per dag.
     */
    'daily_budget_usd' => (float) env('AI_DAILY_BUDGET_USD', 2.20),

    /*
     * Bij welk deel van het dagbudget er een waarschuwingsmail uitgaat. 0.5 = bij de
     * helft. Er gaat maximaal één mail per dag per drempel uit.
     */
    'budget_warning_at' => (float) env('AI_BUDGET_WARNING_AT', 0.5),

    /*
     * Hoeveel nieuwe motors één account per 24 uur mag laten opzoeken via de AI.
     */
    'lookups_per_user_per_day' => (int) env('AI_LOOKUPS_PER_USER_PER_DAY', 5),

    /*
     * Waar de waarschuwingsmail naartoe gaat.
     */
    'alert_email' => env('AI_ALERT_EMAIL', 'jake@helloitsme.online'),

    /*
     * Token voor /ai-gebruik, de pagina waar het AI-verbruik in te zien is. Leeg
     * betekent dat de pagina volledig uit staat.
     */
    'dashboard_token' => env('AI_DASHBOARD_TOKEN'),

    /*
     * Zet /ai-migratie aan. Alleen nodig op het moment dat er migraties naar de server
     * moeten, want artisan draait daar niet. Standaard uit en na gebruik weer uit zetten:
     * een route die migraties kan draaien hoort niet permanent open te staan.
     */
    'allow_remote_migrate' => (bool) env('AI_ALLOW_REMOTE_MIGRATE', false),

    /*
     * Prijs per miljoen tokens in dollars, per model. Bron: Anthropic pricing.
     * Staat een model hier niet in, dan rekent AiSpendGuard met 'unknown': bewust
     * de duurste tarieven, zodat een onbekend model het budget nooit stil oprekt.
     */
    'prices' => [
        'claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00],
        'claude-sonnet-5' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-5' => ['input' => 1.00, 'output' => 5.00],
        'claude-opus-5' => ['input' => 5.00, 'output' => 25.00],
        'claude-opus-4-8' => ['input' => 5.00, 'output' => 25.00],
        'unknown' => ['input' => 10.00, 'output' => 50.00],
    ],

];
