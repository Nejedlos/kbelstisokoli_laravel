<?php

namespace App\Filament\Resources\BasketballMatches\Pages\Concerns;

use App\Exceptions\DuplicateMatchException;
use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use App\Models\BasketballMatch;
use App\Services\Matches\MatchWriteService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

trait PersistsMatchSafely
{
    protected function persistMatchSafely(array $data, ?Model $record = null): BasketballMatch
    {
        $teamIds = Arr::pull($data, 'teams', []);

        try {
            return app(MatchWriteService::class)->saveManual($data, $teamIds, $record);
        } catch (DuplicateMatchException $exception) {
            $message = __('matches.validation.duplicate', ['id' => $exception->matchId]);
            $existing = BasketballMatch::find($exception->matchId);
            $notification = Notification::make()
                ->title(__('matches.duplicates.title'))
                ->body($message)
                ->warning()
                ->persistent();

            if ($existing && BasketballMatchResource::canEdit($existing)) {
                $notification->actions([
                    Action::make('openExistingMatch')
                        ->label(__('matches.duplicates.open_existing'))
                        ->url(BasketballMatchResource::getUrl('edit', ['record' => $existing]), shouldOpenInNewTab: true),
                ]);
            }

            $notification->send();

            throw ValidationException::withMessages(['data.scheduled_at' => $message]);
        } catch (ValidationException $exception) {
            $messages = [];
            foreach ($exception->errors() as $key => $errors) {
                $messages[str_starts_with($key, 'data.') ? $key : 'data.'.$key] = $errors;
            }

            throw ValidationException::withMessages($messages);
        }
    }
}
