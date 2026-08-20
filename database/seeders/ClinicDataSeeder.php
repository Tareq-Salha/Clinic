<?php

namespace Database\Seeders;

use App\Models\Apointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PaymentCompany;
use App\Models\Preview;
use App\Models\Rate;
use App\Models\Son;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClinicDataSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();
        $doctors = collect();
        $patients = collect();

        foreach (range(1, 15) as $index) {
            $user = User::create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => "doctor{$index}@clinic.test",
                'phone' => '+9639' . str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'img_path' => 'storage/doctors_images/do' . $index . '.png',
                'role' => 'doctor',
                'fcm_token' => fake()->sha256(),
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'department_id' => $departments->get(($index - 1) % $departments->count())->id,
                'bio' => fake()->realText(220),
                'subscription' => 5000000 + ($index * 500000),
                'price_of_examination' => 40000 + ($index * 2500),
            ]);

            PaymentCompany::create([
                'user_id' => $user->id,
                'phone_number' => $user->phone,
                'company_name' => $index % 2 === 0 ? 'MTN_Cash' : 'Syriatel_cash',
                'balance' => 200000 + ($index * 10000),
            ]);

            $doctors->push($doctor);
        }

        foreach (range(1, 12) as $index) {
            $gender = $index % 2 === 0 ? 'Female' : 'Male';
            $birthDate = Carbon::now()->subYears(20 + $index)->subDays($index * 11);
            $user = User::create([
                'first_name' => fake()->firstName($gender),
                'last_name' => fake()->lastName(),
                'email' => "patient{$index}@clinic.test",
                'phone' => '+9639' . str_pad((string) (20000000 + $index), 8, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'patient',
                'fcm_token' => fake()->sha256(),
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'first_name' => Patient::encryptField($user->first_name),
                'last_name' => Patient::encryptField($user->last_name),
                'phone' => Patient::encryptField($user->phone),
                'birth_date' => $birthDate->toDateString(),
                'gender' => $gender,
                'age' => $birthDate->age,
                'blood_type' => Patient::encryptField(fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])),
                'chronic_diseases' => Patient::encryptField($index % 3 === 0 ? 'Hypertension' : null),
                'medication_allergies' => Patient::encryptField($index % 4 === 0 ? 'Penicillin' : null),
                'permanent_medications' => Patient::encryptField($index % 3 === 0 ? 'Daily medication' : null),
                'previous_surgeries' => Patient::encryptField($index % 5 === 0 ? 'Appendectomy' : null),
                'previous_illnesses' => Patient::encryptField($index % 2 === 0 ? 'Seasonal flu' : null),
                'honest_score' => 85 + ($index % 16),
                'discount_point' => $index * 2,
            ]);

            foreach (range(1, 2) as $childIndex) {
                Son::create([
                    'parent_id' => $user->id,
                    'patient_id' => $patient->id,
                    'first_name' => fake()->firstName($childIndex % 2 === 0 ? 'Female' : 'Male'),
                    'last_name' => $user->last_name,
                ]);
            }

            $patients->push($patient);
        }

        foreach ($patients as $patientIndex => $patient) {
            $doctor = $doctors->get($patientIndex % $doctors->count());
            $departmentId = $doctor->department_id;
            $appointmentDefinitions = [
                ['status' => 'accepted', 'date' => Carbon::now()->subDays($patientIndex + 1)],
                ['status' => 'waiting', 'date' => Carbon::now()->addDays(($patientIndex % 6) + 1)],
                ['status' => 'rejected', 'date' => Carbon::now()->subDays($patientIndex + 3)],
            ];

            foreach ($appointmentDefinitions as $definition) {
                $appointment = Apointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $departmentId,
                    'apointment_date' => $definition['date'],
                    'apoitment_status' => $definition['status'] === 'rejected' ? 'unapp' : 'app',
                    'status' => $definition['status'],
                    'enter' => $definition['status'] === 'accepted',
                    'price_after_discount' => $doctor->price_of_examination,
                ]);

                if ($definition['status'] === 'accepted') {
                    Preview::create([
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'department_id' => $departmentId,
                        'apointment_id' => $appointment->id,
                        'diagnoseis' => Preview::encryptField(fake()->sentence(5)),
                        'diagnoseis_type' => true,
                        'medicine' => Preview::encryptField(fake()->sentence(4)),
                        'notes' => Preview::encryptField(fake()->sentence(8)),
                        'date' => $definition['date']->toDateString(),
                        'status' => Preview::encryptField('complete'),
                        'price_after_discount' => $doctor->price_of_examination,
                    ]);

                    Rate::create([
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'rate' => 3.5 + (($patientIndex + 1) % 4) * 0.4,
                    ]);
                }
            }
        }
    }
}
