<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Departments
        $depts = [
            [
                'name' => 'Vice Chancellor Office',
                'code' => 'VCO',
                'description' => 'The executive office of the Vice Chancellor'
            ],
            [
                'name' => 'Registrar Office',
                'code' => 'REG',
                'description' => 'Registrar office for administration and records'
            ],
            [
                'name' => 'IT Resource Center',
                'code' => 'ITRC',
                'description' => 'Information Technology resource and support center'
            ],
            [
                'name' => 'Department of Software Engineering',
                'code' => 'SE',
                'description' => 'Software engineering academic department'
            ],
            [
                'name' => 'Quality Enhancement Cell',
                'code' => 'QEC',
                'description' => 'Quality assurance and auditing cell'
            ],
        ];

        $departmentModels = [];
        foreach ($depts as $dept) {
            $departmentModels[$dept['code']] = Department::create($dept);
        }

        // 2. Seed Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ustb.edu.pk',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'department_id' => null
        ]);

        // 3. Seed Staff
        $staffMembers = [
            [
                'name' => 'Registrar Secretary',
                'email' => 'registrar@ustb.edu.pk',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'dept_code' => 'REG'
            ],
            [
                'name' => 'Director IT',
                'email' => 'director.it@ustb.edu.pk',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'dept_code' => 'ITRC'
            ],
            [
                'name' => 'SE Chairperson',
                'email' => 'se.chair@ustb.edu.pk',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'dept_code' => 'SE'
            ],
            [
                'name' => 'Director QEC',
                'email' => 'qec@ustb.edu.pk',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'dept_code' => 'QEC'
            ],
            [
                'name' => 'VC Personal Secretary',
                'email' => 'vc.secretary@ustb.edu.pk',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'dept_code' => 'VCO'
            ]
        ];

        foreach ($staffMembers as $staff) {
            User::create([
                'name' => $staff['name'],
                'email' => $staff['email'],
                'password' => $staff['password'],
                'role' => $staff['role'],
                'department_id' => $departmentModels[$staff['dept_code']]->id
            ]);
        }
    }
}
