<?php

namespace App\Http\Controllers;

use App\Mail\MotorReportReceived;
use App\Models\Motor;
use App\Models\MotorReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MotorReportController extends Controller
{
    public function store(Request $request, Motor $motor): RedirectResponse
    {
        $data = $request->validate([
            'reporter_email' => ['nullable', 'email', 'max:191'],
            'message' => ['required', 'string', 'max:2000'],
            'website' => ['prohibited'],
        ]);

        unset($data['website']);
        $data['motor_id'] = $motor->id;

        $report = MotorReport::query()->create($data);

        Mail::to('jake@helloitsme.online')->send(new MotorReportReceived($report));

        return back()->with('status', 'Bedankt voor de melding, we controleren dit zo snel mogelijk.');
    }
}
