<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Patient;
use App\Support\CurrentClinic;
use App\Support\PublicSettings;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const GENERIC_LOGIN_ERROR = 'Los datos de acceso no son correctos.';

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('patient')->check()) {
            return redirect()->route('patient.dashboard');
        }

        return view('patient.login', ['settings' => PublicSettings::all()]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Escribe tu correo o tu número de celular.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        $clinicId = $this->clinicId();
        $login = trim((string) $request->input('login'));
        $patient = null;

        if ($clinicId) {
            $query = Patient::query()->where('clinic_id', $clinicId);

            if (str_contains($login, '@')) {
                $patient = $query->where('email', Patient::normalizeEmail($login))->first();
            } elseif (($phone = Patient::normalizePhone($login)) !== '') {
                $patient = $query->where('phone', $phone)->first();
            }
        }

        // Mismo mensaje si no existe, si no tiene contraseña o si la contraseña no coincide.
        if (! $patient || ! $patient->password || ! Hash::check((string) $request->input('password'), $patient->password)) {
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => self::GENERIC_LOGIN_ERROR]);
        }

        Auth::guard('patient')->login($patient, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('patient.dashboard'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('patient')->check()) {
            return redirect()->route('patient.dashboard');
        }

        $settings = PublicSettings::all();

        return view('patient.register', [
            'settings' => $settings,
            'activationWhatsappUrl' => PublicSettings::whatsappLink($settings, 'Hola, quiero activar mi cuenta en el portal de pacientes.'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Patient::normalizeEmail($request->input('email')),
            'phone' => Patient::normalizePhone($request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200'],
            'phone' => ['required', 'regex:/^\d{7,15}$/'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'data_policy' => ['accepted'],
            'marketing_consent' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Escribe tu nombre.',
            'name.max' => 'El nombre es demasiado largo.',
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'email.max' => 'El correo es demasiado largo.',
            'phone.required' => 'Escribe tu número de celular.',
            'phone.regex' => 'Escribe un número de celular válido (solo números, entre 7 y 15 dígitos).',
            'password.required' => 'Crea una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'data_policy.accepted' => 'Debes aceptar la política de tratamiento de datos para crear tu cuenta.',
        ]);

        $clinicId = $this->clinicId();

        if (! $clinicId) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'El registro no está disponible en este momento.']);
        }

        // Si ya existe un paciente con ese correo o teléfono (con o sin contraseña), NO se reclama la cuenta.
        if ($this->patientExists($clinicId, $data['email'], $data['phone'])) {
            return $this->existingAccountResponse($request);
        }

        try {
            $patient = DB::transaction(function () use ($data, $clinicId, $request): Patient {
                $consent = $request->boolean('marketing_consent');

                $patient = new Patient([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => $data['password'],
                ]);
                $patient->clinic_id = $clinicId;
                $patient->marketing_consent = $consent;
                $patient->marketing_consent_at = $consent ? now() : null;
                $patient->data_policy_accepted_at = now();
                $patient->data_policy_version = Patient::DATA_POLICY_VERSION;
                $patient->lead_id = $this->linkLead($clinicId, $data)->id;
                $patient->save();

                return $patient;
            });
        } catch (UniqueConstraintViolationException) {
            // Registro simultáneo con los mismos datos.
            return $this->existingAccountResponse($request);
        }

        Auth::guard('patient')->login($patient);
        $request->session()->regenerate();

        return redirect()->route('patient.dashboard')->with('status', '¡Tu cuenta fue creada! Aquí verás tus procedimientos y renovaciones.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('patient')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Cerraste tu sesión.');
    }

    private function clinicId(): ?int
    {
        return app()->bound(CurrentClinic::class) ? app(CurrentClinic::class)->id() : null;
    }

    private function patientExists(int $clinicId, string $email, string $phone): bool
    {
        return Patient::query()
            ->where('clinic_id', $clinicId)
            ->where(fn ($query) => $query->where('email', $email)->orWhere('phone', $phone))
            ->exists();
    }

    private function existingAccountResponse(Request $request): RedirectResponse
    {
        return back()
            ->withInput($request->except('password', 'password_confirmation'))
            ->with('patient_exists', true);
    }

    /** Vincula con un lead existente de la clínica (correo o teléfono) o crea uno nuevo. */
    private function linkLead(int $clinicId, array $data): Lead
    {
        $lastDigits = substr($data['phone'], -7);

        // Los leads guardan el teléfono tal como se escribió ("310 555-1234", "+57 ..."): se compara sin separadores.
        $phoneDigitsSql = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', ''), ')', ''), '.', '')";

        $lead = Lead::query()
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($data, $lastDigits, $phoneDigitsSql) {
                $query->whereRaw('LOWER(email) = ?', [$data['email']])
                    ->orWhereRaw($phoneDigitsSql.' LIKE ?', ['%'.$lastDigits]);
            })
            ->orderByDesc('id')
            ->get()
            ->first(fn (Lead $lead) => mb_strtolower((string) $lead->email) === $data['email']
                || Patient::normalizePhone($lead->phone) === $data['phone']);

        // Un lead existente conserva su estado: pasa a 'converted' cuando se registra su primer tratamiento.
        if ($lead) {
            return $lead;
        }

        return Lead::create([
            'clinic_id' => $clinicId,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'source' => 'portal_paciente',
            'status' => 'new',
        ]);
    }
}
