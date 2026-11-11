<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Support\PublicSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('patient.profile', [
            'settings' => PublicSettings::all(),
            'patient' => $request->user('patient'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Patient $patient */
        $patient = $request->user('patient');

        $request->merge(['phone' => Patient::normalizePhone($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'regex:/^\d{7,15}$/'],
            'marketing_consent' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ], [
            'name.required' => 'Escribe tu nombre.',
            'name.max' => 'El nombre es demasiado largo.',
            'phone.required' => 'Escribe tu número de celular.',
            'phone.regex' => 'Escribe un número de celular válido (solo números, entre 7 y 15 dígitos).',
            'current_password.required_with' => 'Para cambiar la contraseña escribe tu contraseña actual.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
        ]);

        $phoneTaken = Patient::query()
            ->where('clinic_id', $patient->clinic_id)
            ->where('phone', $data['phone'])
            ->whereKeyNot($patient->getKey())
            ->exists();

        if ($phoneTaken) {
            return back()->withInput($request->except('current_password', 'password', 'password_confirmation'))
                ->withErrors(['phone' => 'Ese número ya está registrado en otra cuenta. Escríbenos si necesitas ayuda.']);
        }

        if (! empty($data['password'])) {
            if (! $patient->password || ! Hash::check((string) $data['current_password'], $patient->password)) {
                return back()->withInput($request->except('current_password', 'password', 'password_confirmation'))
                    ->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }

            $patient->password = $data['password'];
        }

        $consent = $request->boolean('marketing_consent');
        if ($consent !== (bool) $patient->marketing_consent) {
            $patient->marketing_consent = $consent;
            $patient->marketing_consent_at = now(); // fecha de la última decisión del paciente
        }

        $patient->name = $data['name'];
        $patient->phone = $data['phone'];
        $patient->save();

        return redirect()->route('patient.profile')->with('status', 'Tus datos se actualizaron.');
    }
}
