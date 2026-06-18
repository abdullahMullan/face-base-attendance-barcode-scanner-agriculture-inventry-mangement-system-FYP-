@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-100 text-indigo-700 text-sm font-semibold shadow-sm ring-1 ring-indigo-200 transition'
            : 'inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-white hover:text-slate-900 hover:shadow-sm transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
