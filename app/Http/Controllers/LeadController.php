<?php

namespace App\Http\Controllers;

use App\Actions\CreateLead;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, CreateLead $createLead): RedirectResponse
    {
        $createLead->handle($request->validated(), $request, 'website');

        return back()->with('success', 'Tu solicitud fue enviada correctamente. Te contactaremos pronto.');
    }
}
