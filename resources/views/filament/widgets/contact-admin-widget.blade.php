<div class="rounded-club border border-slate-200 bg-white p-6">
    <div class="flex items-start gap-4">
        @if(!empty($contact['photo']))
            <img src="{{ asset('storage/' . $contact['photo']) }}" alt="admin" class="w-14 h-14 rounded-full object-cover border border-slate-200">
        @else
            <div class="w-14 h-14 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl border border-slate-200">
                <i class="fa-light fa-user-shield"></i>
            </div>
        @endif
        <div class="flex-1">
            <h3 class="text-sm font-black uppercase tracking-tight text-secondary flex items-center gap-2">
                <i class="fa-light fa-life-ring"></i>
                {{ __('admin/dashboard.contact_admin.title') }}
            </h3>
            <p class="text-xs text-slate-600 mt-1">
                {{ __('admin/dashboard.contact_admin.text') }}
            </p>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <div class="px-3 py-2 rounded-club bg-slate-50 border border-slate-200 flex items-center gap-2">
                    <i class="fa-light fa-user fa-fw"></i>
                    <span class="font-bold text-secondary">{{ $contact['name'] }}</span>
                </div>
                <div class="px-3 py-2 rounded-club bg-slate-50 border border-slate-200 flex items-center gap-2">
                    <i class="fa-light fa-envelope fa-fw"></i>
                    <span>{{ $contact['email'] ?? __('member.feedback.contact_card.not_available') }}</span>
                </div>
                <div class="px-3 py-2 rounded-club bg-slate-50 border border-slate-200 flex items-center gap-2">
                    <i class="fa-light fa-phone fa-fw"></i>
                    <span>{{ $contact['phone'] ?? __('member.feedback.contact_card.not_available') }}</span>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <a href="{{ $contactUrl }}" class="btn btn-primary w-full sm:w-auto">
                    <i class="fa-light fa-paper-plane-top mr-1.5"></i>
                    {{ __('admin/dashboard.contact_admin.cta') }}
                </a>
                @if(!empty($contact['email']))
                    <a href="mailto:{{ $contact['email'] }}" class="btn btn-outline w-full sm:w-auto">
                        <i class="fa-light fa-envelope mr-1.5"></i>
                        {{ __('admin/dashboard.contact_admin.mailto') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
