<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-person-gear me-2"></i>User & Role Management</h2></x-slot>
<div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
	@if (session('success'))
		<div class="rounded bg-green-100 text-green-800 px-4 py-3">{{ session('success') }}</div>
	@endif
	@if ($errors->any())
		<div class="rounded bg-red-100 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
	@endif

	<form method="GET" action="{{ route('users.index') }}" class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex gap-2">
		<input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, phone" class="w-full md:w-96 border rounded">
		<button class="px-4 py-2 bg-slate-700 text-white rounded">Search</button>
		@if($search !== '')
			<a href="{{ route('users.index') }}" class="px-4 py-2 bg-slate-100 rounded">Reset</a>
		@endif
	</form>

	<div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
		<h3 class="font-semibold mb-3">Create User</h3>
		<form method="POST" action="{{ route('users.store') }}" class="space-y-4" id="create-user-form">
			@csrf
			<div class="grid md:grid-cols-2 gap-3">
				<input name="name" placeholder="Name" class="border rounded px-3 py-2" required>
				<input type="email" name="email" placeholder="Email" class="border rounded px-3 py-2" required>
				<input name="phone" placeholder="Phone" class="border rounded px-3 py-2">
				<input name="address" placeholder="Address" class="border rounded px-3 py-2">
				<input type="password" name="password" placeholder="Password" class="border rounded px-3 py-2" required>
				<input type="password" name="password_confirmation" placeholder="Confirm Password" class="border rounded px-3 py-2" required>
				<div>
					<label class="block text-sm font-medium mb-1">Role</label>
					<select name="roles[]" class="w-full border rounded px-3 py-2 role-select" id="create-role-select">
						<option value="">Select Role</option>
						@foreach($roles as $role)
							<option value="{{ $role->name }}">{{ $role->name }}</option>
						@endforeach
					</select>
				</div>
				<div>
					<label class="block text-sm font-medium mb-1">Status</label>
					<select name="is_active" class="w-full border rounded px-3 py-2">
						<option value="1">Active</option>
						<option value="0">Inactive</option>
					</select>
				</div>
			</div>

			<div>
				<label class="block text-sm font-medium mb-2">Permissions <span class="text-gray-500 text-xs">(Based on selected role)</span></label>
				<div class="grid md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded permissions-container" id="create-permissions">
					@forelse($permissions as $module => $modulePermissions)
						<div class="border border-slate-300 rounded p-3 permission-module" data-module="{{ $module }}">
							<h4 class="font-semibold text-sm mb-2 capitalize bg-slate-200 px-2 py-1 rounded">{{ ucfirst($module) }}</h4>
							<div class="space-y-1">
								@foreach($modulePermissions as $permission)
									<label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1 rounded permission-item" data-permission="{{ $permission->name }}">
										<input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded">
										<span class="text-sm">{{ str_replace($module.'.', '', $permission->name) }}</span>
									</label>
								@endforeach
							</div>
						</div>
					@empty
						<p class="text-gray-500">No permissions available</p>
					@endforelse
				</div>
			</div>

			<button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create User</button>
		</form>
	</div>

	<div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
		<h3 class="font-semibold mb-3">All Users (Edit Access)</h3>
		<table class="min-w-full text-sm">
			<thead>
				<tr class="border-b"><th class="p-2 text-left">Name</th><th class="p-2 text-left">Email</th><th class="p-2 text-left">Roles</th><th class="p-2 text-left">Permissions</th><th class="p-2 text-left">Action</th></tr>
			</thead>
			<tbody>
				@foreach($users as $user)
					<tr class="border-b align-top">
						<td class="p-2">{{ $user->name }}</td>
						<td class="p-2">{{ $user->email }}</td>
						<td class="p-2">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
						<td class="p-2">{{ $user->permissions->pluck('name')->join(', ') ?: '-' }}</td>
						<td class="p-2 space-y-2">
							<details>
								<summary class="cursor-pointer text-blue-600">Edit</summary>
								<form method="POST" action="{{ route('users.update', $user) }}" class="mt-3 space-y-3 w-96 bg-slate-50 p-4 rounded edit-user-form">
									@csrf @method('PUT')
									<input name="name" value="{{ $user->name }}" class="w-full border rounded px-3 py-2" required>
									<input type="email" name="email" value="{{ $user->email }}" class="w-full border rounded px-3 py-2" required>
									<input name="phone" value="{{ $user->phone }}" class="w-full border rounded px-3 py-2" placeholder="Phone">
									<input name="address" value="{{ $user->address }}" class="w-full border rounded px-3 py-2" placeholder="Address">
									<input type="password" name="password" class="w-full border rounded px-3 py-2" placeholder="New password (optional)">
									<input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" placeholder="Confirm password">
									<select name="is_active" class="w-full border rounded px-3 py-2"><option value="1" @selected($user->is_active)>Active</option><option value="0" @selected(!$user->is_active)>Inactive</option></select>
									<div>
										<label class="font-medium block mb-2">Roles</label>
										<div class="grid grid-cols-2 gap-2 bg-white border rounded p-2">
											@foreach($roles as $role)
												<label class="flex items-center gap-2 cursor-pointer">
													<input type="checkbox" name="roles[]" value="{{ $role->name }}" class="role-checkbox" @checked($user->roles->pluck('name')->contains($role->name))>
													<span class="text-sm">{{ $role->name }}</span>
												</label>
											@endforeach
										</div>
									</div>
									<div>
										<label class="font-medium block mb-2">Permissions <span class="text-gray-500 text-xs">(Based on selected role)</span></label>
										<div class="grid grid-cols-1 gap-3 max-h-64 overflow-auto bg-white border rounded p-3 edit-permissions-container">
											@forelse($permissions as $module => $modulePermissions)
												<div class="border border-slate-300 rounded p-2 permission-module" data-module="{{ $module }}">
													<h5 class="font-semibold text-xs mb-1 capitalize bg-slate-100 px-2 py-1 rounded">{{ ucfirst($module) }}</h5>
													<div class="space-y-1">
														@foreach($modulePermissions as $permission)
															<label class="flex items-center gap-2 cursor-pointer hover:bg-slate-50 p-1 rounded text-sm permission-item" data-permission="{{ $permission->name }}">
																<input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="permission-checkbox" @checked($user->permissions->pluck('name')->contains($permission->name))>
																<span>{{ str_replace($module.'.', '', $permission->name) }}</span>
															</label>
														@endforeach
													</div>
												</div>
											@empty
												<p class="text-gray-500 text-sm">No permissions available</p>
											@endforelse
										</div>
									</div>
									<button class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 w-full">Update</button>
								</form>
							</details>
							<a href="{{ route('attendance.enroll', $user) }}" class="block text-emerald-600">Face Enroll</a>
							<form method="POST" action="{{ route('users.destroy', $user) }}">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		<div class="mt-3">{{ $users->links() }}</div>
	</div>
</div>

<script>
// Pass role permissions to JavaScript
const rolePermissionsData = @json($rolePermissions);

// Auto-select permissions based on selected role during creation
document.getElementById('create-role-select')?.addEventListener('change', function() {
	const selectedRole = this.value;
	const permissionsContainer = document.getElementById('create-permissions');
	const allPermissionItems = permissionsContainer.querySelectorAll('.permission-item');
	
	allPermissionItems.forEach(item => {
		const permissionName = item.dataset.permission;
		const permissionCheckbox = item.querySelector('input[type="checkbox"]');
		
		// Show all permissions
		item.style.display = 'flex';
		
		if (selectedRole) {
			// Auto-check if permission belongs to selected role
			const rolePermissions = rolePermissionsData[selectedRole] || [];
			permissionCheckbox.checked = rolePermissions.includes(permissionName);
		} else {
			// Uncheck all if no role selected
			permissionCheckbox.checked = false;
		}
	});
});

// Auto-select permissions based on selected roles during edit
document.querySelectorAll('.edit-user-form').forEach(form => {
	const roleCheckboxes = form.querySelectorAll('.role-checkbox');
	const permissionsContainer = form.querySelector('.edit-permissions-container');
	
	const updateEditPermissions = () => {
		const selectedRoles = Array.from(roleCheckboxes)
			.filter(checkbox => checkbox.checked)
			.map(checkbox => checkbox.value);
		
		const allPermissionItems = permissionsContainer.querySelectorAll('.permission-item');
		
		allPermissionItems.forEach(item => {
			const permissionName = item.dataset.permission;
			const permissionCheckbox = item.querySelector('.permission-checkbox');
			
			// Show all permissions
			item.style.display = 'flex';
			
			if (selectedRoles.length === 0) {
				// Uncheck all if no role selected
				permissionCheckbox.checked = false;
			} else {
				// Auto-check if permission belongs to any selected role
				let found = false;
				for (let role of selectedRoles) {
					const rolePermissions = rolePermissionsData[role] || [];
					if (rolePermissions.includes(permissionName)) {
						found = true;
						break;
					}
				}
				permissionCheckbox.checked = found;
			}
		});
	};
	
	roleCheckboxes.forEach(checkbox => {
		checkbox.addEventListener('change', updateEditPermissions);
	});
	
	// Initialize on page load
	updateEditPermissions();
});
</script>
</x-app-layout>