<?php

namespace Database\Seeders;

use App\Models\Day;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MonthlyLeave;
use App\Models\Patient;
use App\Models\PaymentCompany;
use App\Models\Son;
use App\Models\Symbtom;
use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\SymbtomFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@clinic.test',
            'phone' => '+963900000011',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'first_name' => 'Clinic',
            'last_name' => 'Secretary',
            'email' => 'secretary@clinic.test',
            'phone' => '+963900000022',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'secretary',
            'secretary_sallary' => 150000,
        ]);

        Day::factory()
            ->count(6)
            ->saturdayToThursday()
            ->create();

        Department::factory(count(DepartmentFactory::$departments))->create();
        Symbtom::factory(count(SymbtomFactory::$symptoms))->create();
        // User::factory(10)->create();
        // $doctorUsers = User::factory(8)->doctor()->create();


        // Patient::factory(10)->create();

        // Son::factory(5)->create();

        // $departments = Department::all();
        // foreach ($doctorUsers as $user) {

        //     $randomDepartment = $departments->random();
        //     Doctor::factory()->for($user)->create([
        //         'department_id' => $randomDepartment->id,
        //         'bio' => fake()->realText(200),
        //         'subscription' => rand(5, 10) * 1000000,
        //         'price_of_examination' => rand(4, 8) * 10000,
        //     ]);
        // }

        // foreach (User::all() as $user) {
        //     PaymentCompany::factory()->create([
        //         'user_id' => $user->id,
        //     ]);
        // }
        $this->call(ClinicDataSeeder::class);
    }
}
