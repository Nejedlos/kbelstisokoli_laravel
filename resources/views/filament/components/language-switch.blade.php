<div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 transition-all hover:border-primary/20">
    <div class="flex items-center gap-1 px-1.5 border-r border-gray-200 dark:border-gray-700 mr-0.5 text-gray-400">
        <i class="fa-light fa-language text-[12px]"></i>
    </div>
    <div class="flex items-center gap-1">
        <a href="{{ route('language.switch', ['lang' => 'cs']) }}"
           class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[10px] font-bold tracking-tight {{ app()->getLocale() === 'cs' ? 'bg-primary-600 text-white shadow-sm shadow-primary/20' : 'text-gray-500 hover:text-primary hover:bg-white dark:hover:bg-gray-900' }}">
            CZ
        </a>
        <a href="{{ route('language.switch', ['lang' => 'en']) }}"
           class="px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[10px] font-bold tracking-tight {{ app()->getLocale() === 'en' ? 'bg-primary-600 text-white shadow-sm shadow-primary/20' : 'text-gray-500 hover:text-primary hover:bg-white dark:hover:bg-gray-900' }}">
            EN
        </a>
    </div>
</div>
