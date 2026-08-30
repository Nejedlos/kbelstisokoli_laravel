<?php

namespace App\Http\Controllers;

use App\Events\RsvpChanged;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceEmailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceEmailController extends Controller
{
    public function respond(Request $request, AttendanceEmailService $service): View
    {
        $user = User::findOrFail($request->integer('user'));
        $modelClass = AttendanceEmailService::TYPE_MAP[$request->string('type')->value()] ?? abort(404);
        $event = $modelClass::findOrFail($request->integer('event'));
        $status = $request->string('status')->value();
        app()->setLocale(in_array($user->preferred_locale, ['cs', 'en'], true) ? $user->preferred_locale : 'cs');

        if ($request->isMethod('get')) {
            return view('attendance-email-result', [
                'success' => true,
                'message' => __('attendance_mail.response.confirm'),
                'status' => $status,
                'confirmationAction' => $request->fullUrl(),
            ]);
        }

        if (! in_array($status, ['confirmed', 'declined'], true) || ! $service->players($event)->contains('id', $user->id)) {
            return view('attendance-email-result', ['success' => false, 'message' => __('attendance_mail.response.not_allowed')]);
        }

        if ($service->startsAt($event)->isBefore(now()->addMinutes(90))) {
            return view('attendance-email-result', ['success' => false, 'message' => __('attendance_mail.response.too_late')]);
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'attendable_id' => $event->id, 'attendable_type' => $modelClass],
            ['planned_status' => $status, 'responded_at' => now()]
        );
        event(new RsvpChanged($attendance));

        return view('attendance-email-result', ['success' => true, 'message' => __('attendance_mail.response.saved'), 'status' => $status]);
    }

    public function unsubscribe(Request $request): View
    {
        $user = User::findOrFail($request->integer('user'));
        $preference = $request->string('preference')->value();
        abort_unless(in_array($preference, ['attendance_reminders', 'attendance_summaries'], true), 404);
        app()->setLocale(in_array($user->preferred_locale, ['cs', 'en'], true) ? $user->preferred_locale : 'cs');

        if ($request->isMethod('get')) {
            return view('attendance-email-result', [
                'success' => true,
                'message' => __('attendance_mail.response.confirm_unsubscribe'),
                'confirmationAction' => $request->fullUrl(),
            ]);
        }

        $preferences = $user->notification_preferences ?? [];
        data_set($preferences, $preference.'.mail', false);
        $user->update(['notification_preferences' => $preferences]);

        return view('attendance-email-result', ['success' => true, 'message' => __('attendance_mail.response.unsubscribed')]);
    }
}
