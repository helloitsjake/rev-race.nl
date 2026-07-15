<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'message' => ['required', 'string', 'max:4000'],
            'website' => ['prohibited'],
        ]);

        Mail::to('jake@helloitsme.online')->send(
            new ContactMessage($data['name'], $data['email'], $data['message'])
        );

        return back()->with('status', 'Bedankt, je bericht is verstuurd.');
    }
}
