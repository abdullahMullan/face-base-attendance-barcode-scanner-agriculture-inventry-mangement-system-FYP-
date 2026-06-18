@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-start text-sm font-semibold text-indigo-700 transition'
            : 'block w-full rounded-xl border border-transparent px-4 py-2.5 text-start text-sm font-medium text-slate-600 hover:border-slate-200 hover:bg-white hover:text-slate-900 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
