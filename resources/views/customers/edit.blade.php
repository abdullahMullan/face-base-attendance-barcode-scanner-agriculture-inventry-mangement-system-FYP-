<x-app-layout>
	<x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-person-gear me-2"></i>Edit Customer</h2></x-slot>
	<div class="page-wrap py-8">
		<div class="mx-auto max-w-3xl">
			@if ($errors->any())
				<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ $errors->first() }}</div>
			@endif
			<form method="POST" action="{{ route('customers.update', $customer) }}" enctype="multipart/form-data" class="panel space-y-4 p-6">
				@csrf @method('PUT')
				<div><label class="font-medium text-slate-700">Name</label><input name="name" value="{{ old('name', $customer->name) }}" class="w-full"></div>
				<div><label class="font-medium text-slate-700">Phone</label><input name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full"></div>
				<div><label class="font-medium text-slate-700">Email</label><input name="email" value="{{ old('email', $customer->email) }}" class="w-full"></div>
				<div><label class="font-medium text-slate-700">Photo</label><input type="file" name="photo" class="w-full"></div>
				<div><label class="font-medium text-slate-700">Address</label><textarea name="address" rows="4" class="w-full">{{ old('address', $customer->address) }}</textarea></div>
				<div><label class="font-medium text-slate-700">Status</label><select name="is_active" class="w-full"><option value="1" @selected(old('is_active', $customer->is_active)==1)>Active</option><option value="0" @selected(old('is_active', $customer->is_active)==0)>Inactive</option></select></div>
				<div class="flex items-center gap-2"><button class="btn-primary"><i class="bi bi-save me-1"></i>Update</button><a href="{{ route('customers.index') }}" class="btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
			</form>
		</div>
	</div>
</x-app-layout>