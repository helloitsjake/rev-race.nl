<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Models\Partner;
use App\Models\SimulationResult;
use App\Services\SimulationLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $motors = Motor::query()->orderBy('brand')->orderBy('model')->get();
        $topPowerWeight = $motors
            ->sortByDesc(fn (Motor $motor) => $motor->power_hp / max($motor->weight_kg, 1))
            ->take(5);

        return view('home', [
            'motors' => $motors,
            'topPowerWeight' => $topPowerWeight,
            'mostSearched' => $this->mostSearchedMotors(),
        ]);
    }

    public function about(): View
    {
        return view('about', [
            'motors' => Motor::query()->get(),
        ]);
    }

    public function howItWorks(): View
    {
        return view('how-it-works');
    }

    public function partnerApply(): View
    {
        return view('partner-apply');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Motor>
     */
    private function mostSearchedMotors(int $limit = 5)
    {
        $counts = DB::table('simulation_results')
            ->select('motor_id', DB::raw('COUNT(*) as uses'))
            ->fromSub(function ($query) {
                $query->from('simulation_results')->select('motor_a_id as motor_id')
                    ->unionAll(
                        DB::table('simulation_results')->select('motor_b_id as motor_id')
                    );
            }, 'combined')
            ->groupBy('motor_id')
            ->orderByDesc('uses')
            ->limit($limit)
            ->pluck('uses', 'motor_id');

        if ($counts->isEmpty()) {
            return Motor::query()->orderBy('brand')->orderBy('model')->limit($limit)->get();
        }

        return Motor::query()
            ->whereIn('id', $counts->keys())
            ->get()
            ->sortByDesc(fn (Motor $motor) => $counts[$motor->id] ?? 0)
            ->values();
    }

    public function partners(): View
    {
        $partners = Partner::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('partners', [
            'partners' => $partners,
            'categories' => $partners->pluck('category')->unique()->values(),
        ]);
    }

    public function partnerShow(Partner $partner): View
    {
        abort_unless($partner->is_active, 404);

        return view('partner-show', ['partner' => $partner]);
    }

    public function privacy(): View
    {
        return view('privacy');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function embed(Request $request, SimulationLimitService $limits): View
    {
        return view('embed', [
            'motors' => Motor::query()->orderBy('brand')->orderBy('model')->get(),
            'limit' => $limits->status($request->user(), $request->ip()),
            'embedded' => true,
        ]);
    }
}
