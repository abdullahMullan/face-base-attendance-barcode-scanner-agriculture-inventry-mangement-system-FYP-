<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between gap-3 flex-wrap">
            <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Add Category</a>
            <form method="GET" action="{{ route('categories.index') }}" class="flex gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search name, slug, description" class="rounded border-slate-300 w-72">
                <button class="px-3 py-2 bg-slate-700 text-white rounded">Search</button>
                @if($search !== '')<a href="{{ route('categories.index') }}" class="px-3 py-2 bg-slate-100 rounded">Reset</a>@endif
            </form>
        </div>
        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="border-b"><th class="p-3 text-left">Name</th><th class="p-3 text-left">Slug</th><th class="p-3 text-left">Action</th></tr></thead>
                <tbody>
                @foreach($categories as $category)
                    <tr class="border-b">
                        <td class="p-3">{{ $category->name }}</td>
                        <td class="p-3">{{ $category->slug }}</td>
                        <td class="p-3 flex gap-2">
                            <a href="{{ route('categories.edit', $category) }}" class="text-blue-600">Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $categories->links() }}</div>
    </div>
</x-app-layout>
