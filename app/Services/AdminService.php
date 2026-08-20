<?php

namespace App\Services;

use App\Mail\TwoFactorMail;
use App\Models\Apointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PaymentCompany;
use App\Models\User;
use App\UploadImageTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminService
{
    use UploadImageTrait;

    public function dashboardStatistics(): array
    {
        try {
            $now = Carbon::now();
            $months = collect(range(6, 0))->map(function (int $monthsAgo) use ($now) {
                $date = $now->copy()->subMonths($monthsAgo);

                return [
                    'name' => $date->format('M'),
                    'visits' => Apointment::where('status', 'accepted')
                        ->whereYear('apointment_date', $date->year)
                        ->whereMonth('apointment_date', $date->month)
                        ->count(),
                ];
            })->values()->all();

            $weekStart = $now->copy()->startOfWeek();
            $appointments = Apointment::whereBetween('apointment_date', [
                $weekStart,
                $weekStart->copy()->endOfWeek(),
            ])->get(['status', 'apointment_date']);

            $appointmentData = collect(range(0, 6))->map(function (int $day) use ($weekStart, $appointments) {
                $date = $weekStart->copy()->addDays($day);
                $dayAppointments = $appointments->filter(
                    fn(Apointment $appointment) => $appointment->apointment_date->isSameDay($date)
                );

                return [
                    'name' => $date->format('D'),
                    'completed' => $dayAppointments->where('status', 'accepted')->count(),
                    'pending' => $dayAppointments->where('status', 'waiting')->count(),
                    'canceled' => $dayAppointments->where('status', 'rejected')->count(),
                ];
            })->values()->all();

            $topDoctors = Doctor::with(['user', 'department'])
                ->withCount('rates')
                ->withAverageRating()
                ->orderByDesc('average_rating')
                ->limit(3)
                ->get()
                ->map(fn(Doctor $doctor) => [
                    'id' => 'doc_' . $doctor->id,
                    'name' => trim('Dr. ' . $doctor->user->first_name . ' ' . $doctor->user->last_name),
                    'specialty' => $doctor->department?->name,
                    'rating' => (float) $doctor->average_rating,
                    'reviews' => $doctor->rates_count,
                    'avatar' => strtoupper(substr($doctor->user->first_name, 0, 1) . substr($doctor->user->last_name, 0, 1)),
                ])->values()->all();

            $recentActivity = Apointment::with(['department', 'patient.user'])
                ->latest()
                ->limit(4)
                ->get()
                ->map(fn(Apointment $appointment) => [
                    'id' => 'act_appointment_' . $appointment->id,
                    'type' => $appointment->status === 'rejected' ? 'appointment_canceled' : 'appointment_booked',
                    'description' => $appointment->status === 'rejected'
                        ? (($appointment->department?->name ?? 'Department') . ' appointment canceled by patient.')
                        : ('Appointment booked for ' . ($appointment->department?->name ?? 'Department') . '.'),
                    'timestamp' => $appointment->created_at?->diffForHumans(),
                ])->values()->all();

            return [
                'status' => 200,
                'data' => [
                    'overviewStats' => [
                        ['id' => 1, 'titleKey' => 'Admin.Dashboard.stats.totalPatients', 'defaultTitle' => 'Total Patients', 'value' => number_format(Patient::count())],
                        ['id' => 2, 'titleKey' => 'Admin.Dashboard.stats.totalDoctors', 'defaultTitle' => 'Total Doctors', 'value' => (string) Doctor::count()],
                        ['id' => 3, 'titleKey' => 'Admin.Dashboard.stats.activeDepartments', 'defaultTitle' => 'Active Departments', 'value' => (string) Department::has('doctors')->count()],
                        ['id' => 4, 'titleKey' => 'Admin.Dashboard.stats.todaysAppointments', 'defaultTitle' => "Today's Appointments", 'value' => (string) Apointment::today()->count()],
                    ],
                    'patientVisitsData' => $months,
                    'appointmentData' => $appointmentData,
                    'recentActivity' => $recentActivity,
                    'topDoctors' => $topDoctors,
                    'activeSecretaries' => User::where('role', 'secretary')
                        ->latest()
                        ->get(['id', 'first_name', 'last_name', 'email'])
                        ->map(fn(User $user) => [
                            'id' => 'sec_' . $user->id,
                            'name' => trim($user->first_name . ' ' . $user->last_name),
                            'email' => $user->email,
                        ])->values()->all(),
                ],
            ];
        } catch (Exception $e) {
            return ['status' => 500, 'error' => $e->getMessage()];
        }
    }

    public function createSecretary()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|unique:users,phone|regex:/^\+963\d{9}$/',
                'password' => 'required|string|confirmed|min:8',
                'secretary_sallary' => 'required'
            ]);

            if ($validator->fails()) {
                return [
                    'status' => 400,
                    'errors' => $validator->errors()->toArray(),
                ];
            }
            $existing = User::where('role', 'secretary')->first();
            if ($existing) {
                return ['status' => 409];
            }

            $user = User::create([
                'first_name' => request('first_name'),
                'last_name' => request('last_name'),
                'email' => request('email'),
                'phone' => request('phone'),
                'password' => bcrypt(request('password')),
                'secretary_sallary' => request('secretary_sallary'),
                'role' => 'secretary',
            ]);

            $paymentCompany = PaymentCompany::create([
                'user_id' => $user->id,
                'phone_number' => $user->phone,
                'company_name' => in_array(request('phone')[5], ['9', '8']) || in_array(substr(request('phone'), 5, 7), ['98,81,95,82,98,96,87,97']) ? "Syriatel_cash" : "MTN_Cash",
                'balance' => rand(2, 3) * 100000
            ]);


            return [
                'status' => 201,
                'user' => $user,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function updateSecretary()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,',
                'phone' => 'required|regex:/^\+963\d{9}$/|unique:users,phone,',
                'secretary_sallary' => 'required',
                'password' => 'confirmed|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            $user = User::where('role', 'secretary')->first();
            $user->update([
                'email' => request('email'),
                'first_name' => request('first_name'),
                'last_name' => request('last_name'),
                'secretary_sallary' => request('secretary_sallary'),
                'phone' => request('phone'),
                'password' => bcrypt(request('password')),

            ]);
            // $user->save();
            return $user;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updateDoctor($id)
    {
        try {
            $locale = request()->input('lang');
            App::setLocale($locale);
            $doctor = Doctor::find($id);
            $department = Department::where('name', request('department'))->first();
            if (!$doctor)
                return response()->json(['message' => __('messages.doctor_not_found')], 404);
            if (!$department)
                return response()->json(['message' => __('messages.department_not_found')], 404);

            $validator = Validator::make(request()->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'bio' => 'required',
                'department' => 'required',
                'subscription' => 'required',
                'price_of_examination' => 'required',
                'email' => 'required|email|unique:users',
                'phone' => 'required|unique:users|regex:/^\+963\d{9}$/',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            User::where('id', $doctor->user_id)->update([
                'first_name' => request('first_name'),
                'last_name' => request('last_name'),
                'phone' => request('phone'),
                'email' => request('email'),
            ]);
            $doctor->update([
                'bio' => request('bio'),
                'department_id' => request('department'),
                'subscription' => request('subscription'),
                'price_of_examination' => request('price_of_examination'),

            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function deleteSecretary()
    {
        try {
            $secretary = User::where('role', 'secretary')->first();
            if ($secretary) {
                $secretary->delete();
                return null;
            }
            return __('messages.no_secretary_account_for_delete');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function createDoctor()
    {
        $locale = request()->input('lang');
        App::setLocale($locale);
        if (!$locale) {
            return [
                'status' => 400,
                'message' => __('messages.language_required')
            ];
        }
        try {
            $validator = Validator::make(request()->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'bio' => 'required',
                'department' => 'required',
                'subscription' => 'required',
                'price_of_examination' => 'required',
                'email' => 'required|email|unique:users',
                'phone' => 'required|unique:users|regex:/^\+963\d{9}$/',
                'password' => 'required|confirmed|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            $department = Department::where("name->{$locale}", request('department'))->first();

            if (!$department) {
                return response()->json(['error' => __('messages.department_not_found')], 404);
            }
            $user = User::create([
                'email' => request('email'),
                'first_name' => request('first_name'),
                'last_name' => request('last_name'),
                'phone' => request('phone'),
                'password' => bcrypt(request('password')),
                'role' => 'doctor',
            ]);



            $doctor = Doctor::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'bio' => request('bio'),
                'subscription' => request('subscription'),
                'price_of_examination' => request('price_of_examination'),
            ]);
            $paymentCompany = PaymentCompany::create([
                'user_id' => $user->id,
                'phone_number' => $user->phone,
                'company_name' => in_array(request('phone')[5], ['9', '8']) || in_array(substr(request('phone'), 5, 7), ['98,81,95,82,98,96,87,97']) ? "Syriatel_cash" : "MTN_Cash",
                'balance' => rand(2, 3) * 100000
            ]);
            return $doctor;

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteDoctor($id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            if ($doctor) {
                Doctor::destroy($id);
                User::destroy($doctor->user_id);
                return null;
            }
            return __('messages.doctor_not_found');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createDepartment()
    {

        try {
            $validator = Validator::make(request()->all(), [
                'name_en' => 'required|unique:departments,name->en',
                'name_ar' => 'required|unique:departments,name->ar',
                'description_en' => 'required',
                'description_ar' => 'required',
                'image' => 'required|image'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $department = Department::create([
                'name->en' => request("name_en"),
                'name->ar' => request("name_ar"),
                'description->en' => request('description_en'),
                'description->ar' => request('description_ar'),
                'image' => ''
            ]);
            $url = $this->ImageUpload(request(), $department->id, 'departments');
            $department->update(['image' => $url]);
            $department->save();
            return $department;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteDepartment($id)
    {
        try {
            $department = Department::findOrFail($id);
            if ($department) {
                Department::destroy($id);
                return null;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user) {
                User::destroy($id);
                return null;
            }
            return __('messages.user_not_found');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}