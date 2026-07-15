<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Models\Partner;
use App\Services\SimulationLimitService;
use Illuminate\Http\Request;
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
        ]);
    }

    public function partners(): View
    {
        return view('partners', [
            'partners' => Partner::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
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
