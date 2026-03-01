@extends('errors.layout')

@section('title', 'Míč v autu! (500)')
@section('code', '500')
@section('headline', 'Míč skončil v autu!')

@section('message')
    Něco nám v obraně nevyšlo a došlo k neočekávané chybě.
    Náš tým adminů už o tom ví, střídá na hřiště a pracuje na opravě.
@endsection

@section('tagline', 'Tým Sokolů se nevzdává a brzy budeme zpět ve hře.')

@section('actions')
    @php
        $backUrl = url('/');
        $currentPath = request()->getPathInfo();
        if (str_starts_with($currentPath, '/admin')) {
            $backUrl = url('/admin');
        } elseif (str_starts_with($currentPath, '/clenska-sekce')) {
            $backUrl = route('member.dashboard');
        }
    @endphp
    <a href="{{ $backUrl }}" class="btn btn-primary px-8 text-white">
        Zpět na přehled
    </a>
    <button onclick="toggleTechnical()" class="btn btn-outline px-8 border-slate-200">
        Technická zpráva pro admina
    </button>
@endsection

@section('extra')
    <div id="technical-section" class="mt-20 text-left hidden opacity-0 transition-all duration-500 transform translate-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 md:p-8 shadow-sm">
            <p class="text-sm font-medium text-slate-500 mb-6">
                Pokud chcete adminům pomoci s rychlejší opravou, můžete jim poslat tuto diagnostickou zprávu:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <strong class="block text-[10px] uppercase tracking-wider text-slate-400 mb-1">Čas</strong>
                    <span class="text-sm font-mono">{{ $report['timestamp'] ?? now()->toDateTimeString() }}</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <strong class="block text-[10px] uppercase tracking-wider text-slate-400 mb-1">URL</strong>
                    <span class="text-sm font-mono break-all">{{ $report['request']['url'] ?? request()->fullUrl() }}</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <strong class="block text-[10px] uppercase tracking-wider text-slate-400 mb-1">Soubor</strong>
                    <span class="text-sm font-mono">{{ basename($report['exception']['file'] ?? 'unknown') }}:{{ $report['exception']['line'] ?? '?' }}</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <strong class="block text-[10px] uppercase tracking-wider text-slate-400 mb-1">Chyba</strong>
                    <span class="text-sm font-mono">{{ class_basename($report['exception']['class'] ?? 'Exception') }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mb-6">
                <button onclick="copyReport()" class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors">
                    <i class="fa-light fa-copy"></i>
                    Zkopírovat kompletní zprávu
                </button>
                <button onclick="toggleTrace()" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors">
                    <i class="fa-light fa-magnifying-glass"></i>
                    Zobrazit stack trace
                </button>
            </div>

            <pre id="report-content" class="hidden bg-slate-900 text-slate-300 p-6 rounded-xl overflow-auto text-xs leading-relaxed max-h-[400px]"><code>{{ json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            <pre id="trace-content" class="hidden bg-slate-900 text-slate-300 p-6 rounded-xl overflow-auto text-xs leading-relaxed mt-4 max-h-[400px]"><code>{{ $report['exception']['trace'] ?? '' }}</code></pre>
        </div>
    </div>

    <script>
        function toggleTechnical() {
            const el = document.getElementById('technical-section');
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                setTimeout(() => {
                    el.classList.remove('opacity-0', 'translate-y-4');
                }, 10);
                el.scrollIntoView({ behavior: 'smooth' });
            } else {
                el.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => {
                    el.classList.add('hidden');
                }, 500);
            }
        }
        function copyReport() {
            const el = document.getElementById('report-content');
            const text = el.innerText || el.textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('Zpráva byla zkopírována do schránky. Můžete ji vložit do e-mailu nebo chatu správci.');
            }).catch(() => {
                alert('Chyba při kopírování. Zkuste zprávu vybrat ručně.');
            });
        }
        function toggleTrace() {
            const el = document.getElementById('trace-content');
            el.classList.toggle('hidden');
        }
    </script>
@endsection
