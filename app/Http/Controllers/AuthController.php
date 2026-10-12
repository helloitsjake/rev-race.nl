<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function loginForm(Request $request): View
    {
        $this->rememberPendingGarageMotor($request);

        return view('auth.login');
    }

    public function registerForm(Request $request): View
    {
        $this->rememberPendingGarageMotor($request);

        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Deze combinatie van e-mailadres en wachtwoord klopt niet.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($redirect = $this->savePendingGarageMotor($request)) {
            return $redirect;
        }

        return redirect()->intended(route('simulation.index'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'weight_kg' => ['nullable', 'integer', 'between:35,180'],
            'height_cm' => ['nullable', 'integer', 'between:120,230'],
            'riding_style' => ['nullable', 'in:recreatief,sportief,track'],
            'referral_source' => ['nullable', 'in:' . implode(',', array_keys(User::REFERRAL_SOURCES))],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'weight_kg' => $data['weight_kg'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'riding_style' => $data['riding_style'] ?? 'recreatief',
            'referral_source' => $data['referral_source'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($redirect = $this->savePendingGarageMotor($request)) {
            return $redirect;
        }

        return redirect()->route('profile.edit')->with('status', 'Account aangemaakt. Vul je rijdersprofiel verder aan.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Een gast die op "Bewaar in garage" klikt, komt hier met ?bewaar=<motor-id>. Die motor
     * onthouden we tot na het inloggen of registreren, zodat de klik niet verloren gaat.
     */
    private function rememberPendingGarageMotor(Request $request): void
    {
        $id = (int) $request->query('bewaar');

        if ($id > 0 && Motor::query()->whereKey($id)->exists()) {
            $request->session()->put('garage.pending', $id);
        }
    }

    private function savePendingGarageMotor(Request $request): ?RedirectResponse
    {
        $id = $request->session()->pull('garage.pending');

        if (! $id) {
            return null;
        }

        $state = GarageController::add($request->user(), (int) $id);

        return redirect()->route('garage.index')->with('status', match ($state) {
            'full' => 'Je garage zit vol (2 motoren). Haal er eerst een weg.',
            default => 'Motor staat in je garage.',
        });
    }
}
