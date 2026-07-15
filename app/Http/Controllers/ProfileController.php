<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'weight_kg' => ['nullable', 'integer', 'between:35,180'],
            'height_cm' => ['nullable', 'integer', 'between:120,230'],
            'age' => ['nullable', 'integer', 'between:16,90'],
            'riding_style' => ['required', 'in:recreatief,sportief,track'],
            'riding_experience_years' => ['nullable', 'integer', 'between:0,70'],
            'license_category' => ['required', 'in:A,A2,A1'],
        ]);

        $request->user()->update($data);

        return back()->with('status', 'Profiel opgeslagen.');
    }
}
