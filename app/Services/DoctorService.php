<?php

namespace App\Services;

use App\Models\Apointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalAnalysis;
use App\Models\Patient;
use App\Models\Post;
use App\Models\Preview;
use App\Models\Rate;
use App\Models\Son;
use App\Models\User;
use App\Notifications\OutPatient;
use App\UploadImageTrait;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
class DoctorService
{
    use UploadImageTrait;
    // some functions for formate the response
    private function addDoctorInfo($array, $doctor, $user)
    {
        $doctorInfo = [
            'id' => $doctor->id,
            'user_id' => $doctor->user_id,
            'department_id' => $doctor->department_id,
            'bio' => $doctor->bio,
            'created_at' => $doctor->created_at,
            'updated_at' => $doctor->updated_at,
            'department_name' => Department::find($doctor->department_id)->name ?? 'un known',
            'name' => $user->first_name . " " . $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'img_path' => $user->img_path,
            'rates' => Rate::where('doctor_id', $doctor->id)->first()->rate ?? 0
        ];
        $array['doctor_info'] = $doctorInfo;
        return $array;
    }
    private function addPreviewInfo($array, $preview)
    {
        $previewInfo = [
            'id' => $preview->id,
            'diagnoseis' => $preview->diagnoseis,
            'diagnoseis_type' => $preview->diagnoseis_type,
            'medicine' => $preview->medicine,
            'notes' => $preview->notes,
            'date' => $preview->date,
        ];
        $array['preview_info'] = $previewInfo;
        return $array;
    }
    private function addMedicalAnalysisInfo($array, $medicalAnalysis)
    {
        $medicalAnalysiss = [];
        foreach ($medicalAnalysis as $analysis) {
            // $analysis->medical_analysis_path = url($analysis->medical_analysis_path);
            $medicalAnalysiss[] = [
                'medical_analysis_path' => $analysis,
            ];
        }
        $array['medical_analysis_info'] = $medicalAnalysiss;
        return $array;
    }
    private function addPatientInfo($array, $patient)
    {
        $patientInfo = [
            'id' => $patient->id,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'birth_date' => $patient->birth_date,
            'blood_type' => $patient->blood_type,
            'chronic_diseases' => $patient->chronic_diseases,
            'medication_allergies' => $patient->medication_allergies,
            'permanent_medications' => $patient->permanent_medications,
            'previous_surgeries' => $patient->previous_surgeries,
            'previous_illnesses' => $patient->previous_illnesses,
            'honest_score' => $patient->honest_score
        ];
        $array['patient_info'] = $patientInfo;
        return $array;
    }
    private function addBasicInfo($array, $patient, $user, $son)
    {
        $basicInfo = [
            'patient_name' => $user ? $user->first_name . " " . $user->last_name : ($son ? $son->first_name . " " . $son->last_name : $patient->first_name . " " . $patient->last_name),
            'patient_phone' => $user ? $user->phone : ($son ? $patient->phone : null),
            'patient_photo' => $user ? $user->img_path : null,
        ];
        $array['basic_info'] = $basicInfo;
        return $array;
    }
    //______________________________________
    public function postArtical($request)
    {
        $doctor_id = auth()->user()->doctor->id;
        if (!$doctor_id) {
            $message = "doctor not found";
            $code = 404;
            return ['message' => $message, 'post' => null, 'code' => $code];
        }
        $post = Post::query()->create([
            'doctor_id' => $doctor_id,
            'title' => $request->title,
            'body' => $request->body
        ]);
        if ($post) {
            $message = __('messages.article_created_successfully');
            $code = 200;
        } else {
            $message = __('messages.article_created_failed');
            $code = 400;
        }
        return ['message' => $message, 'post' => $post, 'code' => $code];
    }
    public function updateProfile($request)
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            $user->update([
                'first_name' => $request->first_name ?? $user->first_name,
                'last_name' => $request->last_name ?? $user->last_name,
                'email' => $request->email ?? $user->email,
                'phone' => $request->phone ?? $user->phone,
                'password' => $request->password ?? $user->password
            ]);
            $user->save();
            $message = __('messages.profile_updated_successfully');
            $code = 200;
        } else {
            $message = __('messages.user_not_found');
            $code = 404;
        }
        return ['message' => $message, 'user' => $user, 'code' => $code];
    }
    public function updateArticle($request, $id)
    {
        $article = Post::find($id);
        if ($article) {
            $oldbody = $article->body;
            $newbody = $request->body;
            $updateResult = $article->update([
                'title' => $request->title,
                'body' => $request->body
            ]);
            if ($updateResult) {
                preg_match_all('/\[https?:\/\/[^\/]+(\/storage\/[^\]]+)\]/', $oldbody, $matches);
                $oldPaths = $matches[1];
                preg_match_all('/\[https?:\/\/[^\/]+(\/storage\/[^\]]+)\]/', $newbody, $matches);
                $newPaths = $matches[1];

                foreach ($oldPaths as $oldPath) {
                    $flag = true;
                    foreach ($newPaths as $newPath) {
                        if ($oldPath === $newPath) {
                            $flag = false;
                            break;
                        }
                    }
                    if ($flag) {
                        if (Storage::disk('public')->exists($oldPath))
                            Storage::disk('public')->delete($oldPath);
                    }
                }
                $message = __('messages.article_updated_successfully');
                $code = 200;
            } else {
                $message = __('messages.article_updated_failed');
                $code = 400;
            }
        } else {
            $message = __('messages.article_not_found');
            $code = 404;
        }
        return ['article' => $article, 'message' => $message, 'code' => $code];
    }
    public function deleteArticle($id)
    {
        $article = Post::find($id);
        if ($article) {
            $body = $article->body;
            $deleteResult = $article->delete();
            if ($deleteResult) {
                preg_match_all('/\[https?:\/\/[^\/]+(\/storage\/[^\]]+)\]/', $body, $matches);

                $imagePaths = $matches[1];

                foreach ($imagePaths as $path) {
                    $storagePath = str_replace('/storage/', '', $path);

                    if (Storage::disk('public')->exists($storagePath)) {
                        Storage::disk('public')->delete($storagePath);
                        $deleted[] = $storagePath;
                    }
                }
                $message = __('messages.article_deleted_successfully');
                $code = 200;
            } else {
                $message = __('messages.article_deleted_failed');
                $code = 400;
            }
        } else {
            $message = __('messages.article_not_found');
            $code = 404;
        }
        return ['article' => $article, 'message' => $message, 'code' => $code];
    }
    public function getArticles()
    {
        $user = auth()->user();
        if (!$user) {
            return [
                'data' => null,
                'message' => __('messages.user_not_found'),
                'code' => 404
            ];
        }
        $doctor = $user->doctor;
        if (!$doctor) {
            return [
                'data' => null,
                'message' => __('messages.doctor_profile_not_found'),
                'code' => 404
            ];
        }
        $articles = $doctor->posts()->paginate(5)->toArray();
        $doctor->load('department');
        $doctorInfo = $doctor->toArray();
        $responseData = [
            'doctor_info' => $doctorInfo,
            'articles' => $articles
        ];
        return [
            'data' => $responseData,
            'message' => __('messages.articles_returned_successfully'),
            'code' => 200
        ];
    }
    public function getArticleById($id)
    {
        $user = auth()->user();
        if (!$user) {
            $message = __('messages.user_not_found');
            $code = 404;
            return ['message' => $message, 'article' => null, 'code' => $code];
        }
        $doctor = $user->doctor;
        $article = $doctor->posts()->where('id', $id)->first();
        if ($article) {
            $article = $this->addDoctorInfo($article, $doctor, $user);
            $message = __('messages.article_returned_successfully');
            $code = 200;
        } else {
            $message = __('messages.article_not_found');
            $code = 404;
        }
        return ['article' => $article, 'message' => $message, 'code' => $code];
    }
    public function getApointments()
    {
        $user = auth()->user();
        if (!$user) {
            $message = __('messages.user_not_found');
            $code = 404;
            return ['message' => $message, 'data' => null, 'code' => $code];
        }
        $doctor = $user->doctor;
        $apointments = Apointment::forDoctor($doctor->id)
            ->accepted()
            ->today()
            ->get();
        // if it dont work because the date in the database not today
        if ($apointments) {
            $formatedAppointments = [];

            foreach ($apointments as $apointment) {
                $patient = Patient::find($apointment->patient_id);
                $patient_info = User::find($patient->user_id);
                $son = (!$patient_info && $patient) ? Son::where('patient_id', $patient->id)->first() : null;

                $apointmentData = $apointment->toArray();

                $apointmentData['basic_info'] = $this->addBasicInfo([], $patient, $patient_info, $son)['basic_info'];
                $apointmentData['patient_info'] = $this->addPatientInfo([], $patient)['patient_info'];

                $formatedAppointments[] = $apointmentData;
            }
            $doctorInfo = $this->addDoctorInfo([], $doctor, $user)['doctor_info'];
            $message = __('messages.appointments_returned_successfully');
            $code = 200;
        } else {
            $message = __('messages.appointments_not_found');
            $code = 404;
        }
        return
            [
                'message' => $message,
                'data' => [
                    'appointments' => $formatedAppointments,
                    'doctor_info' => $doctorInfo
                ],
                'code' => $code
            ];
    }
    // public function postPreview($request, $patient_id)
    // {
    //     $doctor = auth()->user()->doctor;
    //     if (!$doctor) {
    //         $message = "doctor not found";
    //         $code = 404;
    //         return ['message' => $message, 'preview' => null, 'code' => $code];
    //     }
    //     $appointment = Apointment::where('id', $request->appointment_id)->where('patient_id', $patient_id)->first();
    //     if (!$appointment) {
    //         $message = "apointment not found";
    //         $code = 404;
    //         return ['message' => $message, 'preview' => null, 'code' => $code];
    //     }
    //     $preview = Preview::create([
    //         'patient_id' => $patient_id,
    //         'doctor_id' => $doctor->id,
    //         'department_id' => $doctor->department_id,
    //         'apointment_id' => $appointment->id,
    //         'diagnoseis' => Patient::encryptField($request->diagnoseis),
    //         'diagnoseis_type' => $request->diagnoseis_type,
    //         'medicine' => Patient::encryptField($request->medicine),
    //         'notes' => Patient::encryptField($request->notes),
    //         'date' => Carbon::today(),
    //         'status' => Patient::encryptField($request->status)
    //     ]);
    //     if ($preview) {
    //         $appointment = Apointment::where('id', $preview->apointment_id)->first();
    //         $appointment->enter = 0;
    //         $appointment->save();
    //         $scretary = User::where('role', 'secretary')->first();
    //         $scretary->notify(new OutPatient('I am finshed from this patient please enter the next one'));
    //         event(new \App\Events\OutPatient('I am finshed from this patient please enter the next one', $scretary->id));

    //         $message = "preview added successfully";
    //         $code = 200;
    //     } else {
    //         $message = "preview added failed";
    //         $code = 400;
    //     }
    //     return ['preview' => $preview, 'message' => $message, 'code' => $code];
    // }
    public function updatePreview($request, $preview_id)
    {
        $preview = Preview::find($preview_id);
        $patient = Patient::find($preview->patient_id);
        $doctor = Doctor::find($preview->doctor_id);
        $patient->discount_point += $preview->price_after_discount / 1000;
        $patient->save();
        if ($preview) {
            $updatePreview = $preview->update([
                'diagnoseis' => Patient::encryptField($request->diagnoseis),
                'diagnoseis_type' => $request->diagnoseis_type,
                'medicine' => Patient::encryptField($request->medicine),
                'notes' => Patient::encryptField($request->notes),
                'status' => Patient::encryptField($request->status)
            ]);
            $preview->save();
            if ($updatePreview) {
                $appointment = Apointment::where('id', $preview->apointment_id)->first();
                if ($appointment->enter === 1) {
                    $appointment->enter = 0;
                    $appointment->save();
                    $scretary = User::where('role', 'secretary')->first();
                    $scretary->notify(new OutPatient('I am finshed from this patient please enter the next one'));
                    event(new \App\Events\OutPatient('I am finshed from this patient please enter the next one', $scretary->id, $appointment->id, $patient->first_name . " " . $patient->last_name, $doctor->user->first_name . " " . $doctor->user->last_name));
                }

                $message = __('messages.preview_updated_successfully');
                $code = 200;
            } else {
                $message = __('messages.preview_updated_failed');
                $code = 400;
            }
        } else {
            $message = __('messages.preview_not_found');
            $code = 404;
        }
        return ['message' => $message, 'preview' => $preview, 'code' => $code];
    }
    public function deletePreview($preview_id)
    {
        $preview = Preview::find($preview_id);
        if ($preview) {
            $deletedPreview = $preview->delete();
            if ($deletedPreview) {
                $code = 200;
                $message = __('messages.preview_deleted_successfully');
            } else {
                $code = 400;
                $message = __('messages.preview_deleted_failed');
            }
        } else {
            $code = 404;
            $message = __('messages.preview_not_found');
        }
        return ['message' => $message, 'preview' => $preview, 'code' => $code];
    }
    public function getPreviews()
    {
        $user = auth()->user();
        if (!$user) {
            return [
                'data' => null,
                'message' => __('messages.user_not_found'),
                'code' => 404
            ];
        }
        $doctor = $user->doctor;
        if (!$doctor) {
            return [
                'data' => null,
                'message' => __('messages.doctor_not_found'),
                'code' => 404
            ];
        }
        $previews = Preview::with(['patient', 'doctor', 'department', 'patient.user'])
            ->where('doctor_id', $doctor->id)
            ->get();
        return [
            'data' => $previews,
            'message' => __('messages.previews_returned_successfully'),
            'code' => 200
        ];
    }
    public function getPreviewById($preview_id)
    {
        $doctor = auth()->user()->doctor;
        if (!$doctor) {
            return ['message' => __('messages.doctor_not_found'), 'data' => null, 'code' => 404];
        }
        $preview = Preview::where('id', $preview_id)->where('doctor_id', $doctor->id)->first();
        if ($preview) {
            $message = __('messages.preview_returned_successfully');
            $code = 200;
        } else {
            $message = __('messages.preview_not_found');
            $code = 404;
        }
        return ['message' => $message, 'preview' => $preview, 'code' => $code];
    }
    public function getPreviedPatients()
    {
        $doctor = auth()->user()->doctor;
        if (!$doctor) {
            return ['message' => __('messages.doctor_not_found'), 'patients' => null, 'code' => 404];
        }
        $previews = Preview::where('doctor_id', $doctor->id)->get();
        if ($previews) {
            $previedPatients = [];
            foreach ($previews as $preview) {
                $patient = Patient::find($preview->patient_id);
                $medicalAnalysis = MedicalAnalysis::where('preview_id', $preview->id)->where('patient_id', $patient->id)->get();
                if ($medicalAnalysis)
                    $this->addMedicalAnalysisInfo($patient, $medicalAnalysis);
                $this->addPreviewInfo($patient, $preview);
                $previedPatients[] = $patient;
            }
            if ($previedPatients) {
                $message = __('messages.patients_returned_successfully');
                $code = 200;
            } else {
                $message = __('messages.no_patients_found');
                $code = 404;
            }
        } else {
            $message = __('messages.doctor_has_no_previewed_patients');
            $code = 400;
        }
        return ['message' => $message, 'patients' => $previedPatients, 'code' => $code];
    }
    public function patientSearch()
    {
        $keyword = request('query');
        if (!$keyword) {
            return ['message' => __('messages.search_input_required'), 'patients' => null, 'code' => 400];
        }
        $doctor = auth()->user()->doctor;
        $doctorId = auth()->user()->doctor->id;

        $patients = Patient::with(['user', 'sons', 'previews'])
            ->whereHas('previews', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->get();

        $filteredPatients = $patients->filter(function ($patient) use ($keyword) {

            $nameMatch = stripos($patient->first_name, $keyword) !== false
                || stripos($patient->last_name, $keyword) !== false;


            $user = $patient->user;
            $userMatch = $user && (stripos($user->first_name, $keyword) !== false || stripos($user->last_name, $keyword) !== false);


            $sonsMatch = $patient->sons->contains(function ($son) use ($keyword) {
                return stripos($son->first_name, $keyword) !== false || stripos($son->last_name, $keyword) !== false;
            });


            $previewsMatch = $patient->previews->contains(function ($preview) use ($keyword) {

                return stripos($preview->diagnoseis, $keyword) !== false
                    || stripos($preview->notes, $keyword) !== false
                    || stripos($preview->medicine, $keyword) !== false
                    || stripos($preview->date, $keyword) !== false;
            });


            return $nameMatch || $userMatch || $sonsMatch || $previewsMatch;
        });
        if ($filteredPatients) {
            $message = __('messages.patients_found_successfully');
            $code = 200;
        } else {
            $message = __('messages.patient_not_found');
            $code = 404;
        }
        return ['message' => $message, 'patients' => $filteredPatients, 'code' => $code];
    }
    public function getActivePatientInfo()
    {
        $doctor = auth()->user()->doctor;
        if (!$doctor) {
            return ['message' => __('messages.doctor_not_found'), 'data' => null, 'code' => 404];
        }
        $apointmentPatient = Apointment::where('doctor_id', $doctor->id)->where('status', 'accepted')->where('enter', 1)->first();
        if ($apointmentPatient)
            $patient = Patient::find($apointmentPatient->patient_id);
        if ($patient) {
            $previews = Preview::where('doctor_id', $doctor->id)->where('patient_id', $patient->id)->get();
            $allMedicalAnalysis = [];
            foreach ($previews as $preview) {
                $patient = Patient::find($preview->patient_id);
                $medicalAnalysis = MedicalAnalysis::where('preview_id', $preview->id)->where('patient_id', $patient->id)->first();
                if ($medicalAnalysis)
                    $allMedicalAnalysis[] = $medicalAnalysis;
            }
            $message = __('messages.active_patient_info_returned_successfully');
            $code = 200;
        } else {
            $message = __('messages.patient_not_found');
            $code = 404;
        }
        return [
            'message' => $message,
            'data' => [
                'patient' => $patient,
                'previews' => $previews,
                'medicalAnalysis' => $allMedicalAnalysis
            ],
            'code' => $code
        ];
    }
}
