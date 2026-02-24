@extends('layouts.member', [
    'title' => __('member.feedback.contact_coach_title'),
    'subtitle' => __('member.feedback.contact_coach_subtitle')
])

@section('content')
    <div class="max-w-3xl">
        @if (session('status'))
            <div class="mb-6 p-4 rounded-club bg-success-50 text-success-700 border border-success-200 text-sm">
                <i class="fa-light fa-circle-check mr-1.5"></i> {{ session('status') }}
            </div>
        @endif

        <div class="card p-6 md:p-8 space-y-6">
            <form action="{{ route('member.contact.coach.send') }}" method="POST" enctype="multipart/form-data" x-data="{ loading: false }" @submit="loading = true" class="space-y-6">
                @csrf

                @if($teams->count() === 0)
                    <div class="p-4 rounded-club bg-warning-50 text-warning-800 border border-warning-200 text-sm">
                        {{ __('member.feedback.no_team_warning') }}
                    </div>
                @elseif($teams->count() === 1)
                    <input type="hidden" name="team_id" value="{{ $teams->first()->id }}">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('member.feedback.team') }}</div>
                        <div class="font-bold text-secondary">{{ $teams->first()->name }}</div>
                    </div>
                @else
                    <div>
                        <label for="team_id" class="block text-xs font-bold text-secondary mb-1">{{ __('member.feedback.team') }}</label>
                        <select id="team_id" name="team_id" class="form-select w-full">
                            <option value="">-- {{ __('member.feedback.choose_team') }} --</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('team_id')
                            <div class="text-danger-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div>
                    <label for="subject" class="block text-xs font-bold text-secondary mb-1">{{ __('member.feedback.subject') }}</label>
                    <input id="subject" type="text" name="subject" value="{{ old('subject') }}" class="form-input w-full" required>
                    @error('subject')
                        <div class="text-danger-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-xs font-bold text-secondary mb-1">{{ __('member.feedback.message') }}</label>
                    <textarea id="message" name="message" rows="6" class="form-textarea w-full" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="text-danger-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="attachment" class="block text-xs font-bold text-secondary mb-1">{{ __('member.feedback.attachment') }}</label>
                    <input id="attachment" type="file" name="attachment" class="form-input w-full">
                    <div class="text-[10px] text-slate-400 mt-1">{{ __('member.feedback.attachment_help') }}</div>
                    @error('attachment')
                        <div class="text-danger-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary" :class="{ 'is-loading': loading }">
                        <i class="fa-light fa-paper-plane mr-1.5"></i> {{ __('member.feedback.send_to_coach') }}
                    </button>
                    <a href="{{ route('member.dashboard') }}" class="btn btn-ghost">{{ __('general.back') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
