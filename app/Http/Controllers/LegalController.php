<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Support\PublicSettings;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function __invoke(): View
    {
        return view('legal.politica-datos', [
            'settings' => PublicSettings::all(),
            'policyVersion' => Patient::DATA_POLICY_VERSION,
        ]);
    }
}
