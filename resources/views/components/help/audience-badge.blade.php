@props(['role', 'isMatchingRole' => false])

@php
    $label = __('member.roles.' . strtolower($role));
    if (str_starts_with($label, 'member.roles.')) {
        $label = $role;
    }

    $colorClass = match(strtolower($role)) {
        'admin', 'super_admin' => 'bg-red-50 text-red-600 border-red-100',
        'coach' => 'bg-green-50 text-green-600 border-green-100',
        'treasurer', 'hospodar' => 'bg-orange-50 text-orange-600 border-orange-100',
        'player', 'parent', 'member' => 'bg-blue-50 text-blue-600 border-blue-100',
        default => $isMatchingRole ? 'bg-white/50 text-primary-700 border-transparent' : 'bg-slate-100 text-slate-500 border-slate-200',
    };
@endphp

<span @class([
    'inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-tight border transition-colors whitespace-nowrap',
    $colorClass
])>
    {{ $label }}
</span>
