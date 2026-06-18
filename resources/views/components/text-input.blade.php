@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border-slate-300 bg-white/90 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}>
