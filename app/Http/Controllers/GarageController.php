<?php

namespace App\Http\Controllers;

use App\Models\GarageMotor;
use App\Models\Motor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

    public function share(Request $request): RedirectResponse
    {
        $token = $request->user()->ensureGarageToken();

        return back()->with('status', 'Je garage is nu deelbaar: ' . route('garage.public', $token));
    }

    public function publicShow(string $token): View
    {
        $user = User::query()->where('garage_token', $token)->firstOrFail();

        return view('garage-public', [
            'owner' => $user,
            'garage' => $user->garageMotors()->with('motor')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'motor_id' => ['required', 'integer', 'exists:motors,id'],
            'nickname' => ['nullable', 'string', 'max:80'],
        ]);

        $state = self::add($request->user(), (int) $data['motor_id'], $data['nickname'] ?? null);

        // De garageknop op vergelijk- en wizardpagina's post met fetch en wil alleen de status.
        if ($request->wantsJson()) {
            return response()->json(['state' => $state, 'garage' => route('garage.index')]);
        }

        return $state === 'full'
            ? back()->withErrors(['motor_id' => 'De gratis garage heeft maximaal 2 motoren.'])
            : back()->with('status', 'Motor toegevoegd aan je garage.');
    }

    /**
     * @return 'added'|'exists'|'full'
     */
    public static function add(User $user, int $motorId, ?string $nickname = null): string
    {
        if ($user->garageMotors()->where('motor_id', $motorId)->exists()) {
            return 'exists';
        }

        $count = $user->garageMotors()->count();

        if ($count >= 2 && ! $user->isPremium()) {
            return 'full';
        }

        GarageMotor::query()->create([
            'user_id' => $user->id,
            'motor_id' => $motorId,
            'nickname' => $nickname,
            'sort_order' => $count,
        ]);

        return 'added';
    }

    public function destroy(Request $request, GarageMotor $garageMotor): RedirectResponse
    {
        abort_unless($garageMotor->user_id === $request->user()->id, 403);

        $garageMotor->delete();

        return back()->with('status', 'Motor verwijderd.');
    }
}
