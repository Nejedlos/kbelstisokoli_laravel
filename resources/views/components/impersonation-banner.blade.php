@if(session()->has('impersonated_by'))
    <div class="bg-amber-100 border-b border-amber-200 px-4 py-2 text-amber-900 shadow-sm relative z-[9999] flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 bg-amber-200 p-1.5 rounded-full">
                <i class="fa-light fa-eye text-amber-700"></i>
            </div>
            <div>
                <p class="text-sm font-bold leading-tight">
                    {{ __('permissions.impersonation_active') }}
                </p>
                <p class="text-xs opacity-75 leading-tight">
                    {{ __('permissions.logged_in_as', ['name' => auth()->user()->name]) }}
                </p>
            </div>
        </div>
        <a href="{{ route('admin.impersonate.stop') }}" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1.5 px-3 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
            <i class="fa-light fa-arrow-rotate-left"></i>
            {{ __('permissions.stop_impersonation') }}
        </a>
    </div>
@endif
