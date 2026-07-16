Nieuwe partneraanmelding via rev-race.nl

Bedrijf: {{ $application->company_name }}
Contactpersoon: {{ $application->contact_name }}
E-mail: {{ $application->email }}
Telefoon: {{ $application->phone ?: '-' }}
Website: {{ $application->website_url ?: '-' }}
Categorie: {{ $application->category ?: '-' }}

Bericht:
{{ $application->message ?: '(geen bericht)' }}
