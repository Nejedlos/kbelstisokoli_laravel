@props(['action'])

<a href="{{ $action->url }}" target="_blank"
   class="group w-full flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-primary-100 focus-visible:ring-4 focus-visible:ring-primary-500/50 focus-visible:outline-none transition-all">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-primary-600 group-hover:text-white transition-all shadow-sm">
            <i class="fa-light {{ $action->icon ?? 'fa-link' }} text-lg"></i>
        </div>
        <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition-colors">{{ $action->label_str ?? (method_exists($action, 'getTranslation') ? $action->getTranslation('label', app()->getLocale(), false) : ($action->label ?? 'Action')) }}</span>
    </div>
    <i class="fa-light fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-primary-600 transition-colors"></i>
</a>
