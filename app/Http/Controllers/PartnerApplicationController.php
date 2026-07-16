<?php

namespace App\Http\Controllers;

use App\Mail\PartnerApplicationReceived;
use App\Models\PartnerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PartnerApplicationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'contact_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website_url' => ['nullable', 'url', 'max:300'],
            'category' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:4000'],
            'website' => ['prohibited'],
        ]);

        unset($data['website']);

        $application = PartnerApplication::query()->create($data);

        Mail::to('jake@helloitsme.online')->send(new PartnerApplicationReceived($application));

        return back()->with('status', 'Bedankt voor je aanmelding, we nemen snel contact met je op.');
    }
}
