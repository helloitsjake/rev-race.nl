<?php

namespace App\Http\Controllers;

use App\Models\AiRejectedQuery;
use App\Models\AiUsageLog;
use App\Services\AiSpendGuard;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Inzicht in wat de AI kost en wie het verbruikt. Bewust achter een token uit .env en niet
 * achter een inlog: er is (nog) geen rollen- of adminsysteem in RevRace, en dat erbij
 * bouwen om één pagina te beschermen is een grotere ingreep dan het probleem waard is.
 * Staat AI_DASHBOARD_TOKEN niet in .env, dan bestaat de pagina niet.
 */
class AiUsageController extends Controller
{
    public function show(string $token, AiSpendGuard $guard): Renderable
    {
        $expected = (string) config('ai.dashboard_token');

        // hash_equals voorkomt dat de responstijd verraadt hoe ver een gok goed zat.
        if ($expected === '' || ! hash_equals($expected, $token)) {
            throw new NotFoundHttpException;
        }

        return view('ai-usage', [
            'spentToday' => $guard->spentToday(),
            'budget' => $guard->dailyBudget(),
            'userLimit' => $guard->userLimit(),
            'perDay' => $this->perDay(),
            'byOutcome' => $this->byOutcome(),
            'topIps' => $this->topIps(),
            'topUsers' => $this->topUsers(),
            'topRejections' => AiRejectedQuery::query()->orderByDesc('hits')->limit(15)->get(),
            'recent' => AiUsageLog::query()->with('user')->latest('created_at')->limit(50)->get(),
        ]);
    }

    /**
     * Draait de migraties. Nodig omdat artisan niet op de server beschikbaar is en de
     * gedocumenteerde deploy-route (de lokale database.sqlite via scp overzetten) de
     * productiedatabase zou overschrijven.
     *
     * Staat standaard uit. Een route die migraties kan draaien hoort niet permanent open te
     * staan, ook niet achter een token: zet AI_ALLOW_REMOTE_MIGRATE=true in .env, roep dit
     * één keer aan, en zet de vlag daarna weer uit.
     */
    public function migrate(string $token): JsonResponse
    {
        $expected = (string) config('ai.dashboard_token');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            throw new NotFoundHttpException;
        }

        if (! config('ai.allow_remote_migrate')) {
            throw new NotFoundHttpException;
        }

        Artisan::call('migrate', ['--force' => true]);

        return new JsonResponse([
            'output' => Artisan::output(),
            'let_op' => 'Zet AI_ALLOW_REMOTE_MIGRATE weer uit in .env nu de migratie klaar is.',
        ]);
    }

    /**
     * Kosten en aantallen per dag over de laatste 30 dagen. Een plotselinge piek is precies
     * het signaal waaraan misbruik te zien is.
     */
    private function perDay()
    {
        return AiUsageLog::query()
            ->selectRaw('date(created_at) as dag')
            ->selectRaw('sum(cost_usd) as kosten')
            ->selectRaw('count(*) as pogingen')
            ->selectRaw("sum(case when outcome in ('success','rejected','error') then 1 else 0 end) as betaald")
            ->selectRaw("sum(case when outcome = 'blocked' then 1 else 0 end) as geblokkeerd")
            ->selectRaw("sum(case when outcome = 'cached' then 1 else 0 end) as uit_cache")
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('dag')
            ->orderByDesc('dag')
            ->get();
    }

    private function byOutcome()
    {
        return AiUsageLog::query()
            ->select('outcome')
            ->selectRaw('count(*) as aantal')
            ->selectRaw('sum(cost_usd) as kosten')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('outcome')
            ->orderByDesc('aantal')
            ->get();
    }

    private function topIps()
    {
        return AiUsageLog::query()
            ->select('ip_address')
            ->selectRaw('count(*) as pogingen')
            ->selectRaw('sum(cost_usd) as kosten')
            ->selectRaw("sum(case when outcome = 'blocked' then 1 else 0 end) as geblokkeerd")
            ->selectRaw('max(created_at) as laatst')
            ->whereNotNull('ip_address')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('ip_address')
            ->orderByDesc(DB::raw('pogingen'))
            ->limit(20)
            ->get();
    }

    private function topUsers()
    {
        return AiUsageLog::query()
            ->with('user')
            ->select('user_id')
            ->selectRaw('count(*) as pogingen')
            ->selectRaw('sum(cost_usd) as kosten')
            ->selectRaw('max(created_at) as laatst')
            ->whereNotNull('user_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_id')
            ->orderByDesc(DB::raw('pogingen'))
            ->limit(20)
            ->get();
    }
}
