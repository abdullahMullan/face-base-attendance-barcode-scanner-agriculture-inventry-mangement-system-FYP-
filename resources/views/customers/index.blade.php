<x-app-layout>
	<x-slot name="header">
		<h2 class="title-gradient text-2xl font-semibold leading-tight"><i class="bi bi-people me-2"></i>Customers</h2>
	</x-slot>

	<div class="page-wrap py-8">
		<div class="panel card-fade-in mb-5 p-4 sm:p-5">
			<div class="flex flex-wrap items-center justify-between gap-3">
				<a href="{{ route('customers.create') }}" class="btn-primary"><i class="bi bi-person-plus"></i>Add Customer</a>
				<form method="GET" action="{{ route('customers.index') }}" class="flex w-full flex-col gap-2 md:w-auto md:flex-row">
					<input type="text" name="q" value="{{ $search }}" placeholder="Search name, phone, email" class="w-full md:w-72">
					<button class="btn-secondary" type="submit"><i class="bi bi-search"></i>Search</button>
					@if($search !== '')
						<a href="{{ route('customers.index') }}" class="btn-secondary">Reset</a>
					@endif
				</form>
			</div>
		</div>

		<div class="table-wrap card-fade-in">
			<table class="min-w-full text-sm">
				<thead>
					<tr class="border-b border-slate-200">
						<th class="p-3 text-left">Photo</th>
						<th class="p-3 text-left">Name</th>
						<th class="p-3 text-left">Phone</th>
						<th class="p-3 text-left">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach($customers as $customer)
						<tr class="border-b border-slate-100">
							<td class="p-3">
								@if($customer->photo_path)
									<img src="{{ Storage::url($customer->photo_path) }}" class="h-10 w-10 rounded-full ring-2 ring-slate-200">
								@else
									<div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500"><i class="bi bi-person"></i></div>
								@endif
							</td>
							<td class="p-3 font-medium text-slate-800">{{ $customer->name }}</td>
							<td class="p-3">{{ $customer->phone }}</td>
							<td class="p-3">
								<div class="flex flex-wrap gap-3">
									<a href="{{ route('customers.edit', $customer) }}" class="action-link-primary">Edit</a>
									<form method="POST" action="{{ route('customers.destroy', $customer) }}">
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
		<div class="mt-4">{{ $customers->links() }}</div>
	</div>
</x-app-layout>
