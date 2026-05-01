<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Staff;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Part;

class WorkshopSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permissions ───────────────────────────────────────
        $permissions = [
            'manage-staff',
            'view-reports',
            'manage-inventory',
            'create-job-orders',
            'complete-job-orders',
            'manage-invoices',
            'view-all-jobs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── Roles ─────────────────────────────────────────────
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $manager    = Role::firstOrCreate(['name' => 'manager']);
        $technician = Role::firstOrCreate(['name' => 'technician']);

        $admin->syncPermissions(Permission::all());

        $manager->syncPermissions([
            'view-reports',
            'manage-inventory',
            'create-job-orders',
            'complete-job-orders',
            'manage-invoices',
            'view-all-jobs',
        ]);

        $technician->syncPermissions([
            'create-job-orders',
            'complete-job-orders',
        ]);

        // ── Admin user ────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@workshop.com'],
            [
                'name'               => 'Admin User',
                'phone'              => '+968 9000 0001',
                'password'           => bcrypt('password'),
                'email_verified_at'  => now(),
            ]
        );
        $adminUser->assignRole('admin');

        Staff::firstOrCreate(
            ['user_id' => $adminUser->id],
            ['employee_id' => 'EMP-001', 'status' => 'active']
        );

        // ── Manager user ──────────────────────────────────────
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@workshop.com'],
            [
                'name'               => 'Workshop Manager',
                'phone'              => '+968 9000 0002',
                'password'           => bcrypt('password'),
                'email_verified_at'  => now(),
            ]
        );
        $managerUser->assignRole('manager');

        Staff::firstOrCreate(
            ['user_id' => $managerUser->id],
            ['employee_id' => 'EMP-002', 'status' => 'active', 'hourly_rate' => 8.500]
        );

        // ── Technician ────────────────────────────────────────
        $techUser = User::firstOrCreate(
            ['email' => 'tech@workshop.com'],
            [
                'name'               => 'Ahmed Al-Rashidi',
                'phone'              => '+968 9000 0003',
                'password'           => bcrypt('password'),
                'email_verified_at'  => now(),
            ]
        );
        $techUser->assignRole('technician');

        Staff::firstOrCreate(
            ['user_id' => $techUser->id],
            [
                'employee_id'    => 'EMP-003',
                'specialization' => 'Engine & Transmission',
                'hourly_rate'    => 5.000,
                'status'         => 'active',
            ]
        );

        // ── Sample customers ──────────────────────────────────
        $customer1 = Customer::firstOrCreate(
            ['phone' => '+968 9100 1001'],
            ['name' => 'Khalid Al-Balushi', 'email' => 'khalid@example.com', 'city' => 'Muscat']
        );

        $customer2 = Customer::firstOrCreate(
            ['phone' => '+968 9100 1002'],
            ['name' => 'Fatima Al-Zahra', 'email' => 'fatima@example.com', 'city' => 'Sohar']
        );

        // ── Sample vehicles ───────────────────────────────────
        Vehicle::firstOrCreate(
            ['plate_number' => 'AA 12345'],
            [
                'customer_id' => $customer1->id,
                'make'        => 'Toyota',
                'model'       => 'Land Cruiser',
                'year'        => 2022,
                'color'       => 'White',
                'mileage'     => 45000,
            ]
        );

        Vehicle::firstOrCreate(
            ['plate_number' => 'BB 67890'],
            [
                'customer_id' => $customer2->id,
                'make'        => 'Nissan',
                'model'       => 'Patrol',
                'year'        => 2021,
                'color'       => 'Black',
                'mileage'     => 62000,
            ]
        );

        // ── Sample parts / inventory ──────────────────────────
        $parts = [
            ['sku' => 'OIL-5W40-1L',   'name' => 'Engine Oil 5W-40 (1L)',    'category' => 'Fluids',      'cost_price' => 2.500, 'selling_price' => 4.000, 'quantity_in_stock' => 50, 'reorder_level' => 10],
            ['sku' => 'FLT-OIL-TYT',   'name' => 'Toyota Oil Filter',         'category' => 'Filters',     'cost_price' => 1.800, 'selling_price' => 3.500, 'quantity_in_stock' => 20, 'reorder_level' => 5],
            ['sku' => 'FLT-AIR-UNI',   'name' => 'Universal Air Filter',      'category' => 'Filters',     'cost_price' => 3.000, 'selling_price' => 5.500, 'quantity_in_stock' => 15, 'reorder_level' => 5],
            ['sku' => 'BRK-PAD-FRT',   'name' => 'Front Brake Pads (Set)',    'category' => 'Brakes',      'cost_price' => 8.000, 'selling_price' => 14.000,'quantity_in_stock' => 8,  'reorder_level' => 3],
            ['sku' => 'BRK-DSC-FRT',   'name' => 'Front Brake Disc',         'category' => 'Brakes',      'cost_price' => 12.000,'selling_price' => 22.000,'quantity_in_stock' => 4,  'reorder_level' => 2],
            ['sku' => 'BAT-12V-70AH',  'name' => 'Car Battery 12V 70Ah',     'category' => 'Electrical',  'cost_price' => 18.000,'selling_price' => 28.000,'quantity_in_stock' => 6,  'reorder_level' => 2],
            ['sku' => 'SPK-NGK-BKRT',  'name' => 'NGK Spark Plug BKR6E',     'category' => 'Ignition',    'cost_price' => 1.200, 'selling_price' => 2.500, 'quantity_in_stock' => 32, 'reorder_level' => 8],
            ['sku' => 'BLT-TIMING-UNI','name' => 'Timing Belt Universal',    'category' => 'Engine',      'cost_price' => 6.500, 'selling_price' => 11.000,'quantity_in_stock' => 3,  'reorder_level' => 2],
            ['sku' => 'FLT-FUEL-UNI',  'name' => 'Fuel Filter Universal',    'category' => 'Filters',     'cost_price' => 2.200, 'selling_price' => 4.000, 'quantity_in_stock' => 2,  'reorder_level' => 4],
            ['sku' => 'CLT-FLUID-1L',  'name' => 'Coolant Fluid (1L)',        'category' => 'Fluids',      'cost_price' => 1.500, 'selling_price' => 2.800, 'quantity_in_stock' => 30, 'reorder_level' => 10],
        ];

        foreach ($parts as $part) {
            Part::firstOrCreate(['sku' => $part['sku']], $part);
        }

        $this->command->info('Workshop seeded successfully.');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin:     admin@workshop.com    / password');
        $this->command->info('  Manager:   manager@workshop.com  / password');
        $this->command->info('  Tech:      tech@workshop.com     / password');
    }
}
