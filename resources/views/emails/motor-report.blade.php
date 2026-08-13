Melding "klopt dit niet?" via rev-race.nl

Motor: {{ $report->motor->label() }} (id {{ $report->motor->id }})
Link: {{ route('brands.model', ['merk' => \Illuminate\Support\Str::slug($report->motor->brand), 'model' => $report->motor->slug()]) }}
Melder e-mail: {{ $report->reporter_email ?: '(niet opgegeven)' }}

Bericht:
{{ $report->message }}
