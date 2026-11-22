<?php

use App\Http\Controllers\Admin\HomeBannersUploadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Patient\AuthController as PatientAuthController;
use App\Http\Controllers\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\Patient\ProfileController as PatientProfileController;
use App\Http\Controllers\Patient\ServiceRequestController as PatientServiceRequestController;
use App\Http\Controllers\PrescriptionPrintController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TayraiWebhookController;
use App\Http\Middleware\AuthenticatePatient;
use App\Support\MedicalPanel;
use Illuminate\Support\Facades\Route;

Route::get('/media/{path}', PublicMediaController::class)
    ->where('path', '.*')
    ->name('media.public');

Route::get('/', HomeController::class)->name('home');
Route::get('/quienes-somos', [HomeController::class, 'about'])->name('about');
Route::get('/resultados', [HomeController::class, 'results'])->name('results');
Route::get('/equipo-medico', [HomeController::class, 'team'])->name('team');
Route::get('/preguntas-frecuentes', [HomeController::class, 'faq'])->name('faq');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/servicios', [ServiceController::class, 'index'])->name('services.index');
Route::get('/procedimientos/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/contacto', [LeadController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('leads.store');

Route::post('/webhooks/tayrai', TayraiWebhookController::class)
    ->middleware('throttle:tayrai-webhook')
    ->name('webhooks.tayrai');

Route::middleware('auth')->post('/admin/home-banners/upload', HomeBannersUploadController::class)->name('admin.home-banners.upload');

// Política de tratamiento de datos (Ley 1581 de 2012)
Route::get('/politica-de-datos', LegalController::class)->name('legal.data-policy');

// Portal del paciente (guard "patient")
Route::get('/ingresar', [PatientAuthController::class, 'showLogin'])->name('patient.login');
Route::post('/ingresar', [PatientAuthController::class, 'login'])->middleware('throttle:patient-auth')->name('patient.login.attempt');
Route::get('/registro', [PatientAuthController::class, 'showRegister'])->name('patient.register');
Route::post('/registro', [PatientAuthController::class, 'register'])->middleware('throttle:patient-auth')->name('patient.register.store');
Route::post('/salir', [PatientAuthController::class, 'logout'])->name('patient.logout');

Route::middleware(AuthenticatePatient::class)->group(function () {
    Route::get('/mi-cuenta', PatientDashboardController::class)->name('patient.dashboard');
    Route::get('/mi-cuenta/perfil', [PatientProfileController::class, 'edit'])->name('patient.profile');
    Route::put('/mi-cuenta/perfil', [PatientProfileController::class, 'update'])->name('patient.profile.update');

    // Solicitar / agendar la renovación de un tratamiento ya realizado.
    Route::get('/mi-cuenta/renovar/{treatment}', [PatientServiceRequestController::class, 'create'])->whereNumber('treatment')->name('patient.requests.create');
    Route::post('/mi-cuenta/renovar/{treatment}', [PatientServiceRequestController::class, 'store'])->whereNumber('treatment')->middleware('throttle:10,1')->name('patient.requests.store');
    Route::post('/mi-cuenta/solicitudes/{serviceRequest}/cancelar', [PatientServiceRequestController::class, 'cancel'])->whereNumber('serviceRequest')->name('patient.requests.cancel');
});

// Fórmulas: solo existen con el panel médico habilitado (MEDICAL_PANEL_ENABLED); si no, 404.
if (MedicalPanel::enabled()) {
    // Fórmulas e indicaciones emitidas por el médico, vistas por el paciente dueño.
    Route::middleware(AuthenticatePatient::class)
        ->get('/mi-cuenta/formulas/{prescription}', [PrescriptionPrintController::class, 'patient'])
        ->whereNumber('prescription')
        ->name('patient.prescriptions.show');

    // Fórmula imprimible para el equipo médico (sesión del panel).
    Route::middleware('auth')->get('/formulas/{prescription}/imprimir', [PrescriptionPrintController::class, 'staff'])
        ->whereNumber('prescription')
        ->name('prescriptions.print');
}
