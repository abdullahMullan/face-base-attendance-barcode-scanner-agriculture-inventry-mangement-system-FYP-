<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-tag me-2"></i>Add Category</h2></x-slot>
    <div class="page-wrap py-8">
        <div class="mx-auto max-w-3xl">
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('categories.store') }}" class="panel space-y-4 p-6">
            @csrf
            <div><label class="block mb-1 font-medium text-slate-700">Name</label><input name="name" value="{{ old('name') }}" class="w-full"></div>
            <div><label class="block mb-1 font-medium text-slate-700">Description</label><textarea name="description" rows="4" class="w-full">{{ old('description') }}</textarea></div>
            <div class="flex items-center gap-2">
                <button class="btn-primary"><i class="bi bi-check-circle me-1"></i>Save</button>
                <a href="{{ route('categories.index') }}" class="btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
            </div>
        </form>
        </div>
    </div>
</x-app-layout>
