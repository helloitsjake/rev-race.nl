<?php

use App\Http\Controllers\AiUsageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\MotorController;
use App\Http\Controllers\MotorReportController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartnerApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SegmentController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\ToplijstController;
use App\Http\Controllers\WizardController;
use App\Http\Controllers\YearlyReportController;
use Illuminate\Support\Facades\Route;

// Publieke, niet-persoonlijke pagina's: expliciete Cache-Control zodat browser/CDN mag cachen.
// Bewust uitgesloten: pagina's met een GET-formulier dat na een mislukte POST validatiefouten
// of een sessie-flash terug kan tonen op dezelfde route (contact, partner-worden).
//
// De wizard stond hier eerder ook bij, maar ten onrechte: /welke-motor-past-bij-mij heeft geen
// POST-tegenhanger, geen @csrf en geen flash-state. Het antwoord hangt volledig af van de
// querystring, en caches sleutelen op de volledige URL inclusief query. Zonder deze header was
// de best presterende commerciële pagina van de site (246 vertoningen in Search Console over de
// laatste drie maanden) als enige publieke pagina uitgesloten van de cachelaag, en zette 'ie bij
// elk bezoek een sessie- en XSRF-cookie voor bezoekers die niet inloggen.
Route::get('/', [PageController::class, 'home'])->middleware('cache.public:300,3600,86400')->name('home');
Route::get('/welke-motor-past-bij-mij', [WizardController::class, 'index'])->middleware('cache.public:1800,3600,86400')->name('wizard.index');
Route::get('/simulatie', [SimulationController::class, 'index'])->name('simulation.index');
Route::get('/meest-gezocht', [PageController::class, 'mostSearched'])->middleware('cache.public:1800,3600,86400')->name('most-searched.index');
Route::get('/partners', [PageController::class, 'partners'])->middleware('cache.public:3600,86400,604800')->name('partners.index');
Route::get('/partners/{partner}', [PageController::class, 'partnerShow'])->middleware('cache.public:3600,86400,604800')->name('partners.show');
Route::get('/partner-worden', [PageController::class, 'partnerApply'])->name('partners.apply');
Route::post('/partner-worden', [PartnerApplicationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('partners.apply.store');
Route::get('/kennis', [PageController::class, 'kennis'])->middleware('cache.public:1800,3600,86400')->name('kennis.index');
Route::get('/kennis/{article}', [PageController::class, 'kennisShow'])->middleware('cache.public:1800,3600,86400')->name('kennis.show');
Route::get('/over-ons', [PageController::class, 'about'])->middleware('cache.public:3600,86400,604800')->name('about');
Route::get('/hoe-het-werkt', [PageController::class, 'howItWorks'])->middleware('cache.public:3600,86400,604800')->name('how-it-works');
Route::get('/privacy', [PageController::class, 'privacy'])->middleware('cache.public:3600,86400,604800')->name('privacy');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/embed', [PageController::class, 'embed'])->name('embed');
Route::get('/s/{code}', [SimulationController::class, 'showShared'])->name('share.show');
Route::get('/vergelijk/{slug}', [ComparisonController::class, 'show'])->middleware('cache.public:3600,86400,604800')->name('compare.show');
Route::get('/toplijst/{slug}', [ToplijstController::class, 'show'])->middleware('cache.public:3600,86400,604800')->name('toplijst.show');
Route::get('/merken', [BrandController::class, 'index'])->middleware('cache.public:3600,86400,604800')->name('brands.index');
Route::get('/merken/{merk}', [BrandController::class, 'show'])->middleware('cache.public:3600,86400,604800')->name('brands.show');
Route::get('/merken/{merk}/{model}', [BrandController::class, 'showModel'])->middleware('cache.public:3600,86400,604800')->name('brands.model');
Route::post('/motoren/{motor}/melding', [MotorReportController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('motors.report');
Route::get('/segmenten', [SegmentController::class, 'index'])->middleware('cache.public:3600,86400,604800')->name('segments.index');
Route::get('/segment/{categorie}', [SegmentController::class, 'show'])->middleware('cache.public:3600,86400,604800')->name('segments.show');
Route::get('/a2-motoren', [PageController::class, 'a2Motoren'])->middleware('cache.public:3600,86400,604800')->name('a2-motoren');
Route::get('/staat-van-de-nederlandse-motorrijder', [YearlyReportController::class, 'show'])->middleware('cache.public:3600,86400,604800')->name('yearly-report.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');

    // Benoemde limiters, niet de kale throttle:6,1: die deelt zijn teller met elke andere
    // throttle op de site. Zie AppServiceProvider::registerRateLimiters().
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:inloggen')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:registreren')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/garage/gedeeld/{token}', [GarageController::class, 'publicShow'])->name('garage.public');

Route::middleware('auth')->group(function (): void {
    Route::get('/garage', [GarageController::class, 'index'])->name('garage.index');
    Route::post('/garage', [GarageController::class, 'store'])->name('garage.store');
    Route::delete('/garage/{garageMotor}', [GarageController::class, 'destroy'])->name('garage.destroy');
    Route::post('/garage/share', [GarageController::class, 'share'])->name('garage.share');
    Route::get('/profiel', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profiel', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/api/motors', [MotorController::class, 'search'])->name('api.motors.search');

// De AI-lookup is de enige route op de site die per aanroep geld kost (OpenAI API).
// Daarom vereist hij een account: een aanvaller moet dan accounts aanmaken, en elke call
// is in ai_usage_logs aan een gebruiker te koppelen en dus te blokkeren. De harde remmen
// (dagbudget voor de hele site, daglimiet per account, negatieve cache) zitten in
// AiSpendGuard en MotorLookupService, niet hier: middleware alleen is per IP en dus te
// omzeilen met meerdere IP's.
Route::post('/api/motors/lookup', [MotorController::class, 'lookup'])
    ->middleware(['auth', 'throttle:ai-lookup'])
    ->name('api.motors.lookup');

// Handmatige invoer kost niets, dus die blijft open voor gasten: iemand zonder account kan
// zo nog steeds een motor toevoegen die nog niet in RevRace staat.
Route::post('/api/motors/manual', [MotorController::class, 'storeManual'])
    ->middleware('throttle:15,1')
    ->name('api.motors.manual');
Route::get('/api/simulatie/limiet', [SimulationController::class, 'limit'])->name('api.simulation.limit');
Route::post('/api/simulatie', [SimulationController::class, 'run'])->name('api.simulation.run');

// Inzicht in het AI-verbruik. Achter een token uit .env omdat RevRace geen rollen- of
// adminsysteem heeft; staat AI_DASHBOARD_TOKEN niet in .env, dan geeft deze route een 404.
Route::get('/ai-gebruik/{token}', [AiUsageController::class, 'show'])
    ->middleware('throttle:10,1')
    ->name('ai-usage');

// Eenmalige migratieroute, nodig omdat artisan niet op de server draait en de
// gedocumenteerde deploy-route (database.sqlite via scp) de productiedatabase zou
// overschrijven. Staat standaard UIT: vereist naast het token ook AI_ALLOW_REMOTE_MIGRATE=true
// in .env. Zet die vlag aan, roep de route één keer aan, zet hem daarna weer uit.
Route::get('/ai-migratie/{token}', [AiUsageController::class, 'migrate'])
    ->middleware('throttle:3,1')
    ->name('ai-migrate');

Route::get('/llms.txt', [PageController::class, 'llms'])->middleware('cache.public:3600,86400,604800')->name('llms');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
