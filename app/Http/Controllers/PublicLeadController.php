<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Rules\RecaptchaV3 as RecaptchaV3Rule;
use Illuminate\Http\Request;

class PublicLeadController extends Controller
{
    /**
     * Zpracuje odeslání kontaktního formuláře.
     */
    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'consent' => 'required|accepted',
            'g-recaptcha-response' => [new RecaptchaV3Rule('contact_form')],
        ]);

        $lead = Lead::create([
            'type' => 'contact',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'payload' => [
                'consent_accepted_at' => now()->toIso8601String(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', __('Vaše zpráva byla odeslána. Brzy se vám ozveme.'));
    }

    /**
     * Zpracuje odeslání náborového formuláře.
     */
    public function storeRecruitment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'birth_year' => 'required|numeric|min:1900|max:'.date('Y'),
            'message' => 'nullable|string',
            'consent' => 'required|accepted',
            'g-recaptcha-response' => [new RecaptchaV3Rule('recruitment_form')],
        ]);

        $lead = Lead::create([
            'type' => 'recruitment',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'],
            'payload' => [
                'birth_year' => $validated['birth_year'],
                'consent_accepted_at' => now()->toIso8601String(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', __('Vaše přihláška k náboru byla odeslána. Brzy se vám ozveme.'));
    }
}
