<?php

namespace App\Http\Controllers;

use App\Models\GarageMotor;
use App\Models\Motor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GarageController extends Controller
{
    public function index(Request $request): View
    {
        return view('garage', [
            'garage' => $request->user()->garageMotors()->with('motor')->orderBy('sort_order')->get(),
            'motors' => Motor::query()->orderBy('brand')->orderBy('model')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'motor_id' => ['required', 'integer', 'exists:motors,id'],
            'nickname' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();
        $count = $user->garageMotors()->count();

        if ($count >= 2 && ! $user->isPremium()) {
            return back()->withErrors(['motor_id' => 'De gratis garage heeft maximaal 2 motoren.']);
        }

        GarageMotor::query()->firstOrCreate(
            ['user_id' => $user->id, 'motor_id' => $data['motor_id']],
            ['nickname' => $data['nickname'] ?? null, 'sort_order' => $count],
        );

        return back()->with('status', 'Motor toegevoegd aan je garage.');
    }

    public function destroy(Request $request, GarageMotor $garageMotor): RedirectResponse
    {
        abort_unless($garageMotor->user_id === $request->user()->id, 403);

        $garageMotor->delete();

        return back()->with('status', 'Motor verwijderd.');
    }
}
