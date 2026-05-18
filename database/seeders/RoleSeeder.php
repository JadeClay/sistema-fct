<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::all();

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $agent = Role::firstOrCreate(['name' => 'Agent']);

        $superAdmin->syncPermissions($allPermissions);
        $supervisor->syncPermissions($allPermissions);

        $agentPermissionNames = [
            'ViewAny:Ticket', 'View:Ticket', 'Create:Ticket', 'Update:Ticket',
            'ViewAny:EmailCase', 'View:EmailCase', 'Create:EmailCase', 'Update:EmailCase',
        ];
        $agent->syncPermissions(Permission::whereIn('name', $agentPermissionNames)->get());

        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin User', 'password' => bcrypt('password')],
        )->assignRole('Super Admin');

        User::firstOrCreate(
            ['email' => 'supervisor@example.com'],
            ['name' => 'Supervisor User', 'password' => bcrypt('password')],
        )->assignRole('Supervisor');

        User::firstOrCreate(
            ['email' => 'agent@example.com'],
            ['name' => 'Agent User', 'password' => bcrypt('password')],
        )->assignRole('Agent');
    }
}
