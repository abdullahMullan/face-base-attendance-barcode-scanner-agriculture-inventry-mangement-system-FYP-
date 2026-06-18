<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'categories.manage',
            'products.manage',
            'purchases.manage',
            'sales.manage',
            'credits.manage',
            'reports.view',
            'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $inventoryManager = Role::firstOrCreate(['name' => 'inventory-manager']);
        $salesManager = Role::firstOrCreate(['name' => 'sales-manager']);
        $reportViewer = Role::firstOrCreate(['name' => 'report-viewer']);

        $superAdmin->syncPermissions($permissions);
        $inventoryManager->syncPermissions([
            'dashboard.view',
            'categories.manage',
            'products.manage',
            'purchases.manage',
        ]);
        $salesManager->syncPermissions([
            'dashboard.view',
            'sales.manage',
            'credits.manage',
        ]);
        $reportViewer->syncPermissions([
            'dashboard.view',
            'reports.view',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@agri.local'],
            [
                'name' => 'Admin User',
                'phone' => '03000000000',
                'is_active' => true,
                'password' => Hash::make('Admin@12345'),
            ]
        );

        $admin->assignRole($superAdmin);
    }
}
