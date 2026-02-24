<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\FeedbackConfirmation;
use App\Mail\FeedbackMessage;
use App\Models\Setting;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function coachForm(Request $request): View
    {
        $user = $request->user();
        $teams = $user->playerProfile?->teams()->with('coaches')->get() ?? collect();

        return view('member.contact.coach', [
            'teams' => $teams,
            'user' => $user,
        ]);
    }

    public function sendCoach(Request $request): RedirectResponse
    {
        $user = $request->user();
        $locale = App::getLocale();

        $teams = $user->playerProfile?->teams ?? collect();
        $teamRule = $teams->count() > 1 ? 'required|exists:teams,id' : 'nullable|exists:teams,id';

        $data = $request->validate([
            'team_id' => $teamRule,
            'subject' => 'required|string|min:5|max:200',
            'message' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        // Urči tým
        $team = null;
        if ($teams->count() === 1) {
            $team = $teams->first();
        } elseif (!empty($data['team_id'])) {
            $team = Team::findOrFail($data['team_id']);
        }

        // E-maily trenérů pro daný tým
        $recipients = collect();
        if ($team) {
            $recipients = $team->coaches->map(function ($coach) {
                return $coach->pivot->email ?: $coach->email;
            })->filter();
        }

        // Fallback na admina
        if ($recipients->isEmpty()) {
            $adminEmail = Setting::where('key', 'admin_contact_email')->value('value') ?: env('ERROR_REPORT_EMAIL');
            if ($adminEmail) {
                $recipients->push($adminEmail);
            }
        }

        // Uložení přílohy (pokud je)
        $storedPath = null;
        if ($request->hasFile('attachment')) {
            $disk = config('filesystems.default', env('UPLOADS_DISK', 'public'));
            $dir = trim(env('UPLOADS_DIR', 'uploads'), '/').'/feedback';
            $storedPath = $request->file('attachment')->store($dir, $disk);
        }

        // Odeslání e-mailu trenérům / adminovi
        if ($recipients->isNotEmpty()) {
            $to = $recipients->shift();
            $bcc = $recipients->all();

            Mail::to($to)
                ->bcc($bcc)
                ->send(new FeedbackMessage(
                    type: 'coach',
                    user: $user,
                    subject: $data['subject'],
                    message: $data['message'],
                    team: $team,
                    attachmentDisk: $storedPath ? config('filesystems.default', env('UPLOADS_DISK', 'public')) : null,
                    attachmentPath: $storedPath,
                    locale: $locale,
                ));
        }

        // Potvrzení uživateli
        Mail::to($user->email)->send(new FeedbackConfirmation(
            type: 'coach',
            user: $user,
            subject: $data['subject'],
            message: $data['message'],
            team: $team,
            locale: $locale,
        ));

        return redirect()
            ->route('member.dashboard')
            ->with('status', __('member.feedback.sent_success'));
    }

    public function adminForm(Request $request): View
    {
        return view('member.contact.admin', [
            'user' => $request->user(),
        ]);
    }

    public function sendAdmin(Request $request): RedirectResponse
    {
        $user = $request->user();
        $locale = App::getLocale();

        $data = $request->validate([
            'subject' => 'required|string|min:5|max:200',
            'message' => 'required|string|min:10',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        $adminEmail = Setting::where('key', 'admin_contact_email')->value('value') ?: env('ERROR_REPORT_EMAIL');
        $recipients = collect();
        if ($adminEmail) {
            $recipients->push($adminEmail);
        }

        $storedPath = null;
        if ($request->hasFile('attachment')) {
            $disk = config('filesystems.default', env('UPLOADS_DISK', 'public'));
            $dir = trim(env('UPLOADS_DIR', 'uploads'), '/').'/feedback';
            $storedPath = $request->file('attachment')->store($dir, $disk);
        }

        if ($recipients->isNotEmpty()) {
            $to = $recipients->shift();
            $bcc = $recipients->all();

            Mail::to($to)
                ->bcc($bcc)
                ->send(new FeedbackMessage(
                    type: 'admin',
                    user: $user,
                    subject: $data['subject'],
                    message: $data['message'],
                    team: null,
                    attachmentDisk: $storedPath ? config('filesystems.default', env('UPLOADS_DISK', 'public')) : null,
                    attachmentPath: $storedPath,
                    locale: $locale,
                ));
        }

        Mail::to($user->email)->send(new FeedbackConfirmation(
            type: 'admin',
            user: $user,
            subject: $data['subject'],
            message: $data['message'],
            team: null,
            locale: $locale,
        ));

        return redirect()
            ->route('member.dashboard')
            ->with('status', __('member.feedback.sent_success'));
    }
}
