@props(['faq'])

<details class="group bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-md transition-all">
    <summary class="flex items-center justify-between p-6 cursor-pointer list-none select-none focus-visible:ring-4 focus-visible:ring-primary-500/50 focus-visible:outline-none focus-visible:bg-primary-50">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors shadow-sm border border-slate-50">
                <i class="fa-light fa-circle-question text-lg"></i>
            </div>
            <h4 class="text-lg font-bold text-slate-900 leading-tight">{{ $faq->question_str ?? (method_exists($faq, 'getTranslation') ? $faq->getTranslation('question', app()->getLocale(), false) : ($faq->question ?? 'Untitled')) }}</h4>
        </div>
        <div class="w-10 h-10 flex items-center justify-center text-slate-500 group-open:rotate-180 transition-transform">
            <i class="fa-light fa-chevron-down"></i>
        </div>
    </summary>
    <div class="p-6 pt-0 border-t border-slate-50">
        <div class="prose-sm prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
            {{ $faq->answer_str ?? (method_exists($faq, 'getTranslation') ? $faq->getTranslation('answer', app()->getLocale(), false) : ($faq->answer ?? '')) }}
        </div>
    </div>
</details>
