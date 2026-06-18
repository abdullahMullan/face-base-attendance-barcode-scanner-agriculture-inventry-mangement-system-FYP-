<x-app-layout>
    <x-slot name="header">
        <h2 class="title-gradient text-2xl font-semibold leading-tight"><i class="bi bi-truck me-2"></i>Suppliers</h2>
    </x-slot>

    <div class="page-wrap py-8">
        <div class="panel card-fade-in mb-5 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('suppliers.create') }}" class="btn-primary"><i class="bi bi-plus-circle"></i>Add Supplier</a>
                <form method="GET" action="{{ route('suppliers.index') }}" class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search name, phone, email" class="w-full md:w-72">
                    <button class="btn-secondary" type="submit"><i class="bi bi-search"></i>Search</button>
                    @if($search !== '')
                        <a href="{{ route('suppliers.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-wrap card-fade-in">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Phone</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr class="border-b border-slate-100">
                            <td class="p-3 font-medium text-slate-800">{{ $supplier->name }}</td>
                            <td class="p-3">{{ $supplier->phone }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="action-link-primary">Edit</a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-link-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $suppliers->links() }}</div>
    </div>
</x-app-layout>
