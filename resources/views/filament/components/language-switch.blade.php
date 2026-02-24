<div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg text-[10px] font-black tracking-widest shadow-sm border border-gray-200 dark:border-gray-700 mr-4">
    <a href="{{ route('language.switch', ['lang' => 'cs']) }}"
       class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ app()->getLocale() === 'cs' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-primary hover:bg-white dark:hover:bg-gray-900' }}">
        CZ
    </a>
    <a href="{{ route('language.switch', ['lang' => 'en']) }}"
       class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-primary text-white shadow-sm' : 'text-gray-500 hover:text-primary hover:bg-white dark:hover:bg-gray-900' }}">
        EN
    </a>
</div>
