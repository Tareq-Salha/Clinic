<?php

namespace App\Services;

use App\Mail\TwoFactorMail;
use App\Models\Apointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\FavoritePost;
use App\Models\Patient;
use App\Models\PaymentCompany;
use App\Models\Post;
use App\Models\Preview;
use App\Models\Rate;
use App\Models\Son;
use App\Models\Symbtom;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use App\UploadImageTrait;
use App\Models\MedicalAnalysis;
use Illuminate\Support\Facades\Storage;
use App\Models\Appointment;
use function PHPUnit\Framework\isNull;
use function PHPUnit\Framework\returnArgument;

class PatientService
{
    use UploadImageTrait;
    // some functions for formate the response
    private function addPatientInfo($array, $patient)
    {
        $patientInfo = [
            'id' => $patient->id,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'birth_date' => $patient->birth_date,
        ];
        $array['patient_info'] = $patientInfo;
        return $array;
    }
    public function addFavToArticles($articles, $patient)
    {
        $formatedArticles = [];
        foreach ($articles as $article) {
            $flagFav = FavoritePost::where('post_id', $article->id)->where('patient_id', $patient->id)->get();
            if ($flagFav->isNotEmpty()) {
                $article['fav'] = true;
            } else {
                $article['fav'] = false;
            }
            $formatedArticles = $article;
        }
        return collect($formatedArticles);
    }
    public function addInfoForAppointment($array, $patient, $user, $doctor, $son = null)
    {

        $doctorUser = User::find($doctor->user_id);
        if ($son)
            $sonPatient = Patient::find($son->patient_id);
        $appointmentInfo = [
            'imgPath' => $son == null ? $user->img_path : null,
            'patientName' => $son == null ? ($user->first_name . " " . $user->last_name) : ($son->first_name . " " . $son->last_name),
            'doctorName' => $doctorUser->first_name . " " . $doctorUser->last_name,
            'gender' => $son == null ? $patient->gender : $sonPatient->gender
        ];
        $array['appointment_info'] = $appointmentInfo;
        return $array;
    }
    // ____________________________________________
    public function postPatientInformation($request)
    {
        $user_id = auth()->user()->id;
        $patient = Patient::create([
            'user_id' => $user_id,
            'birth_date' => $request->birth_date,
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_type' => Patient::encryptField($request->blood_type),
            'chronic_diseases' => Patient::encryptField($request->chronic_diseases),
            'medication_allergies' => Patient::encryptField($request->medication_allergies),
            'permanent_medications' => Patient::encryptField($request->permanent_medications),
            'previous_surgeries' => Patient::encryptField($request->previous_surgeries),
            'previous_illnesses' => Patient::encryptField($request->previous_illnesses),
            'first_name' => auth()->user()->first_name,
            'last_name' => auth()->user()->last_name,
            'honest_score' => 100,
            'discount_point' => 50
        ]);
        if ($patient) {
            $message = 'patient profile added successfullt';
            $code = 200;
        } else {
            $message = 'patient profile not added to the system';
            $code = 400;
        }
        return ['message' => $message, 'patient' => $patient, 'code' => $code];
    }
    public function addChild($request)
    {
        $user_id = auth()->user()->id;
        $patient = Patient::create([
            'birth_date' => $request->birth_date,
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_type' => Patient::encryptField($request->blood_type),
            'chronic_diseases' => Patient::encryptField($request->chronic_diseases),
            'medication_allergies' => Patient::encryptField($request->medication_allergies),
            'permanent_medications' => Patient::encryptField($request->permanent_medications),
            'previous_surgeries' => Patient::encryptField($request->previous_surgeries),
            'previous_illnesses' => Patient::encryptField($request->previous_illnesses),
            'discount_point' => 50
        ]);
        $son = Son::create([
            'patient_id' => $patient->id,
            'parent_id' => $user_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name
        ]);
        $this->addPatientInfo($son, $patient);
        if ($patient && $son) {
            $code = 200;
            $message = 'son profile added successfullt';
        } else {
            $message = 'son profile not added to the system';
            $code = 400;
        }
        return ['message' => $message, 'son' => $son, 'code' => $code];
    }
    public function getArticles()
    {
        $patient = auth()->user()->patient;
        $articles = Post::paginate(5);
        $this->addFavToArticles($articles, $patient);
        if ($articles) {
            $message = "articles return successfully";
        } else {
            $message = "articles return failed";
        }
        return ['message' => $message, 'articles' => $articles];
    }
    public function addArticleFav($id)
    {
        $article = Post::find($id);
        $patient = auth()->user()->patient;
        $articleFavFound = FavoritePost::where('post_id', $id)->where('patient_id', $patient->id)->first();
        if ($articleFavFound) {
            return ['fav' => $article, 'message' => 'article already in fav', 'code' => 200];
        }
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['fav' => null, 'message' => $message, 'code' => $code];
        }
        if ($article) {
            $addFav = FavoritePost::create([
                'patient_id' => $patient->id,
                'post_id' => $id
            ]);
            if ($addFav) {
                $message = "article added to favorite successfully";
                $code = 200;
            } else {
                $message = "article added to favorite failed";
                $code = 400;
            }
        } else {
            $message = "article not found";
            $code = 404;
        }
        return ['fav' => $article, 'message' => $message, 'code' => $code];
    }
    public function deleteArticleFav($id)
    {
        $article = Post::find($id);
        $patient = auth()->user()->patient;
        $favArticle = $patient->favoritePost($id);
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['fav' => null, 'message' => $message, 'code' => $code];
        }
        if (!$article) {
            $message = "article not found";
            $code = 404;
            return ['fav' => null, 'message' => $message, 'code' => $code];
        }
        if ($favArticle) {
            $deleteFav = $favArticle->delete();
            if ($deleteFav) {
                $message = "article deleted from favorite successfully";
                $code = 200;
            } else {
                $message = "article deleted from favorite failed";
                $code = 400;
            }
        } else {
            $message = "article is not in the favorite to delete";
            $code = 404;
        }
        return ['fav' => $article, 'message' => $message, 'code' => $code];
    }
    public function getFavArticles()
    {
        $patient = auth()->user()->patient;
        $favArticles = $patient->favoritePosts()->get();
        if ($favArticles) {
            $formatedArticles = [];
            foreach ($favArticles as $favArticle) {
                $formatedArticles[] = Post::find($favArticle->post_id);
            }
            $message = "favorite articles return successfully";
            $code = 200;
        } else {
            $message = "no favorite articles";
            $code = 404;
        }
        return ['fav' => $formatedArticles, 'message' => $message, 'code' => $code];
    }
    public function bookAppointment($request, $doctor_id)
    {
        $doctor = Doctor::find($doctor_id);
        $user = auth()->user();
        $patient = $user->patient;
        $son_id = $request->son_id ?? null;
        $son = Son::find($son_id);
        if ($son && ($son->parent_id !== auth()->user()->id)) {
            $message = "patient don't have this son";
            $code = 404;
            return ['message' => $message, 'appointment' => null, 'code' => $code];
        }
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['appointment' => null, 'message' => $message, 'code' => $code];
        }
        if ($doctor) {
            $department = $doctor->department;
            if ($department) {
                $doctorPayment = PaymentCompany::where('user_id', $doctor->user_id)->first();
                $payment = PaymentCompany::find($request->payment_id);
                if ($payment->user_id !== $user->id) {
                    return ['message' => 'you dont have aceess for this payment account', 'appointment' => null, 'code' => 400];
                }
                $priceWithDiscount = ($doctor->price_of_examination - ($son_id == null ? ($patient->discount_point * 200) : ((Patient::find($son->patient_id)->discount_point) * 200)));
                $previews = Preview::where('patient_id', $son_id == null ? $patient->id : $son->patient_id)->where('doctor_id', $doctor_id)->get();
                $paysFlag = true;
                foreach ($previews as $preview) {
                    if ($preview->diagnoseis_type === 0) { // because its boolean value
                        $paysFlag = false;
                    }
                }
                if ($priceWithDiscount > $payment->balance && $paysFlag) {
                    return [
                        'message' => "sorry you don't have enough mouny in your acount",
                        'appointment' => null,
                        'code' => 400,
                    ];
                }
                $appointment = Apointment::create([
                    'patient_id' => $son_id == null ? $patient->id : $son->patient_id,
                    'doctor_id' => $doctor_id,
                    'department_id' => $department->id,
                    'payment_id' => $request->payment_id,
                    'apointment_date' => $request->appointment_date,
                    'apoitment_status' => "app",
                    'status' => "waiting",
                    'price_after_discount' => $paysFlag ? $priceWithDiscount : 0
                ]);

                $payment->update([
                    'balance' => $paysFlag ? $payment->balance -= $priceWithDiscount : $payment->balance -= 0,
                ]);
                $doctorPayment->update([
                    'balance' => $paysFlag ? $doctorPayment->balance += $priceWithDiscount : $doctorPayment->balance += 0,
                ]);
                $son_id == null ? $patient->update([
                    'discount_point' => $paysFlag ? 0 : $patient->discount_point
                ]) :
                    Patient::find($son->patient_id)->update([
                        'discount_point' => $paysFlag ? 0 : $patient->discount_point
                    ]);


                $this->addInfoForAppointment($appointment, $patient, $user, $doctor, $son);
            } else {
                $code = 404;
                $message = "department not found";
            }
            if ($appointment) {
                $code = 200;
                $message = "appointment created successfully";
            } else {
                $code = 400;
                $message = "appointment created failed";
            }
        } else {
            $code = 404;
            $message = "doctor not found";
        }
        return ['message' => $message, 'appointment' => $appointment ?? null, 'code' => $code];
    }
    public function updateApointment($request, $appointment_id)
    {
        $appointment = Apointment::find($appointment_id);
        $user = auth()->user();
        $patient = $user->patient;
        // $son_id = $request->son_id ?? null;
        // $son = Son::find($son_id);
        // if ($son && ($son->parent_id !== auth()->user()->id)) {
        //     $message = "patient don't have this son";
        //     $code = 404;
        //     return ['message' => $message, 'appointment' => null, 'code' => $code];
        // }
        if ($appointment) {
            if ($appointment->status === "waiting") {
                // $updateData = [];
                if ($request->has('appointment_date') && $request->appointment_date !== null) {
                    $appointment->update([
                        'apointment_date' => $request->appointment_date
                    ]);
                    // $updateData['apointment_date'] = $request->appointment_date;
                }
                if ($request->has('payment_id') && $appointment->payment_id != $request->payment_id) {
                    $oldPayment = PaymentCompany::find($appointment->payment_id);
                    $newPayment = PaymentCompany::find($request->payment_id);
                    if ($newPayment->balance >= $appointment->price_after_discount) {
                        $newPayment->balance -= $appointment->price_after_discount;
                        $appointment->payment_id = $request->payment_id;
                        $appointment->save();
                        $newPayment->save();
                    } else {
                        return ['message' => "you don't have enough money in this payment acount", 'appointment' => null, 'code' => 400];
                    }
                    $oldPayment->balance += $appointment->price_after_discount;
                    $oldPayment->save();
                }
                // if ($son && $son_id !== null) {
                //     $updateData['patient_id'] = $son->patient_id;
                // } else {
                //     $updateData['patient_id'] = $patient->id;
                // }
                // $appointment->update($updateData);

                $doctor = Doctor::find($appointment->doctor_id);
                $this->addInfoForAppointment($appointment, $patient, $user, $doctor, );

                if ($appointment) {
                    $message = "apointment updated successfully";
                    $code = 200;
                } else {
                    $message = "apointment updated failed";
                    $code = 400;
                }
            } else {
                $message = "this appointment accepted you cant update it";
                $code = 400;
            }
        } else {
            $message = "appointment not found";
            $code = 404;
        }
        return ['message' => $message, 'appointment' => $appointment, 'code' => $code];
    }
    public function deleteAppointment($appointment_id)
    {
        $appointment = Apointment::find($appointment_id);
        $user = auth()->user();
        if (!$user) {
            return ['message' => 'user not found', 'appointment' => null, 'code' => 404];
        }

        if ($appointment) {

            $payment = PaymentCompany::find($appointment->payment_id);
            $payment->balance += $appointment->price_after_discount;
            $payment->save();

            $doctor = Doctor::find($appointment->doctor_id);

            $doctorPayment = PaymentCompany::where('user_id', $doctor->user_id)->first();
            $doctorPayment->balance -= $appointment->price_after_discount;
            $doctorPayment->save();

            $patient = Patient::find($appointment->patient_id);
            $point = ($doctor->price_of_examination - $appointment->price_after_discount) / 200;
            if ($appointment->price_after_discount != 0) {
                $patient->discount_point += $point;
                $patient->save();
            }
            $checkDelete = $appointment->delete();
            if ($checkDelete) {
                $message = "Appointment deleted successfully";
                $code = 200;
            } else {
                $message = "Appointment deleted failed";
                $code = 400;
            }
        } else {
            $message = "Appointment not found";
            $code = 404;
        }
        return ['message' => $message, 'appointment' => $appointment, 'code' => $code];
    }
    public function getAppointments()
    {
        $user = auth()->user();
        $patient = $user->patient;
        $sons = Son::where('parent_id', $user->id)->get();
        $formatedAppointments = [];
        $acceptedAppointmentsForPatient = Apointment::ofPatient($patient->id)->accepted()->get();
        foreach ($acceptedAppointmentsForPatient as $acceptedAppointment) {
            $patient = Patient::find($acceptedAppointment['patient_id']);
            $user = User::find($patient['user_id']);
            $doctor = Doctor::find($acceptedAppointment['doctor_id']);
            $this->addInfoForAppointment($acceptedAppointment, $patient, $user, $doctor);
        }
        $waitingAppointmentsForPatient = Apointment::ofPatient($patient->id)->waiting()->get();
        foreach ($waitingAppointmentsForPatient as $waitingAppointment) {
            $patient = Patient::find($waitingAppointment['patient_id']);
            $user = User::find($patient['user_id']);
            $doctor = Doctor::find($waitingAppointment['doctor_id']);
            $this->addInfoForAppointment($waitingAppointment, $patient, $user, $doctor);
        }
        $formatedAppointments['accepted_patient'] = $acceptedAppointmentsForPatient ?? null;
        $formatedAppointments['waiting_patient'] = $waitingAppointmentsForPatient ?? null;
        $acceptedAppointmentsForPatientSon = [];
        $waitingAppointmentsForPatientSon = [];
        if ($sons) {
            foreach ($sons as $son) {
                $acceptedAppointment = Apointment::ofPatient($son->patient_id)->accepted()->get();
                foreach ($acceptedAppointment as $accepted) {
                    $patient = Patient::find($accepted['patient_id']);
                    $user = User::find($patient['user_id']);
                    $doctor = Doctor::find($accepted['doctor_id']);
                    $son = Son::where('patient_id', $patient->id)->first();
                    $this->addInfoForAppointment($accepted, $patient, $user, $doctor, $son);
                }
                $acceptedAppointmentsForPatientSon[] = $acceptedAppointment;

                $waitingAppointment = Apointment::ofPatient($son->patient_id)->waiting()->get();
                foreach ($waitingAppointment as $waiting) {
                    $patient = Patient::find($waiting['patient_id']);
                    $user = User::find($patient['user_id']);
                    $doctor = Doctor::find($waiting['doctor_id']);
                    $son = Son::where('patient_id', $patient->id)->first();
                    $this->addInfoForAppointment($waiting, $patient, $user, $doctor, $son);
                }
                $waitingAppointmentsForPatientSon[] = $waitingAppointment;
            }


            $formatedAppointments['accepted_sons'] = $acceptedAppointmentsForPatientSon ?? null;
            $formatedAppointments['waiting_sons'] = $waitingAppointmentsForPatientSon ?? null;
        }
        if ($formatedAppointments) {
            $message = "appointemts return successfully";
        } else {
            $message = "appointemts return failed";
        }
        return ['message' => $message, 'appointments' => $formatedAppointments];
    }
    public function getChilds()
    {
        $sons = Son::where('parent_id', auth()->user()->id)->get();
        if ($sons) {
            $formatedSonArray = [];
            foreach ($sons as $son) {
                $patient = Patient::find($son->patient_id);
                $this->addPatientInfo($son, $patient);
                $formatedSonArray[] = $son;
            }
            $message = "sons return successfully";
            $code = 200;
        } else {
            $message = "this patient dont have any sons in the application";
            $code = 404;
        }
        return ['message' => $message, 'sons' => $formatedSonArray, 'code' => $code];
    }
    public function updateChild($request, $id)
    {
        $son = Son::find($id);
        if (!$son) {
            return ['message' => "son not found", 'son' => null, 'code' => 404];
        }
        $patientSonProfile = Patient::find($son->patient_id);
        if ($patientSonProfile) {
            $updatePatientResult = $patientSonProfile->update([
                'birth_date' => $request->birth_date,
                'age' => $request->age,
                'gender' => Patient::encryptField($request->gender),
                'blood_type' => Patient::encryptField($request->blood_type),
                'chronic_diseases' => Patient::encryptField($request->chronic_diseases),
                'medication_allergies' => Patient::encryptField($request->medication_allergies),
                'permanent_medications' => Patient::encryptField($request->permanent_medications),
                'previous_surgeries' => Patient::encryptField($request->previous_surgeries),
                'previous_illnesses' => Patient::encryptField($request->previous_illnesses),
            ]);
        }
        if ($son) {
            $updateSonResult = $son->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name
            ]);
        }
        $this->addPatientInfo($son, $patientSonProfile);
        if ($updatePatientResult && $updateSonResult) {
            $message = "son profile update successfully";
            $code = 200;
        } else {
            $message = "son profile update faild";
            $code = 400;
        }
        return ['message' => $message, 'son' => $son, 'code' => $code];
    }
    public function deleteChild($id)
    {
        $son = Son::find($id);
        if ($son) {
            $patient = Patient::find($son->patient_id);
            if (!$patient) {
                return ['message' => 'patient not found', 'son' => null, 'code' => 404];
            }
            $deletePatientCheck = $patient->delete();
            $deletedCheck = $son->delete();
            if ($deletedCheck && $deletePatientCheck) {
                $message = "Son deleted successfully";
                $code = 200;
            } else {
                $message = "Son deleted failed";
                $code = 400;
            }
        } else {
            $message = "Son not found";
            $code = 404;
        }
        return ['message' => $message, 'son' => $son, 'code' => $code];
    }
    public function getPreviews()
    {
        $user = auth()->user();
        $patient = $user->patient;
        if (!$patient) {
            return ['message' => 'patient not found', 'previews' => null, 'code' => 404];
        }
        $sonPatientIds = Son::where('parent_id', $user->id)->pluck('patient_id')->toArray();
        $allPatientIds = array_merge([$patient->id], $sonPatientIds);
        $allPreviews = Preview::whereIn('patient_id', $allPatientIds)
            ->with(['patient.user', 'doctor.user'])
            ->get();
        $allPreviews->transform(function ($preview) {
            $previewUser = $preview->patient ?? null;
            $doctorUser = $preview->doctor->user ?? null;
            $preview->patientName = $previewUser ? $previewUser->first_name . ' ' . $previewUser->last_name : null;
            $preview->doctorName = $doctorUser ? $doctorUser->first_name . ' ' . $doctorUser->last_name : null;
            $preview->gender = $previewUser ? $previewUser->gender : null;
            $preview->imgPath = $previewUser ? $previewUser->user?->img_path : null;
            $preview->makeHidden(['patient', 'doctor']);
            return $preview;
        });
        $formatedPreviews = [
            'completePreviews' => $allPreviews->where('patient_id', $patient->id)->where('diagnoseis_type', 1)->values(),
            'partlyPreviews' => $allPreviews->where('patient_id', $patient->id)->where('diagnoseis_type', 0)->values(),
            'completePreviewsSons' => $allPreviews->whereIn('patient_id', $sonPatientIds)->where('diagnoseis_type', 1)->values(),
            'partlyPreviewsSons' => $allPreviews->whereIn('patient_id', $sonPatientIds)->where('diagnoseis_type', 0)->values(),
        ];

        $hasPreviews = $allPreviews->isNotEmpty();

        return [
            'message' => $hasPreviews ? "preview return successfully" : "no previews yet",
            'previews' => $formatedPreviews,
            'code' => $hasPreviews ? 200 : 400
        ];
    }
    public function updatePatientProfile($request)
    {
        $patient = auth()->user()->patient;
        if (!$patient) {
            return ['message' => 'patient not found', 'patient' => null, 'code' => 404];
        }
        $updatedStatus = $patient->update([
            'birth_date' => $request->birth_date,
            'age' => $request->age,
            'gender' => $request->gender,
            'blood_type' => Patient::encryptField($request->blood_type),
            'chronic_diseases' => Patient::encryptField($request->chronic_diseases),
            'medication_allergies' => Patient::encryptField($request->medication_allergies),
            'permanent_medications' => Patient::encryptField($request->permanent_medications),
            'previous_surgeries' => Patient::encryptField($request->previous_surgeries),
            'previous_illnesses' => Patient::encryptField($request->previous_illnesses),
            'honest_score' => 100
        ]);
        if ($updatedStatus) {
            $message = "Patient profile updated successfully";
            $code = 200;
        } else {
            $message = "!somthing went wrong the patient information not updated";
            $code = 400;
        }
        return ['message' => $message, 'patient' => $patient, 'code' => $code];
    }
    public function updateProfileInfo($request)
    {
        $user = auth()->user();
        if (!$user) {
            $message = "user not found";
            $code = 404;
            return ['message' => $message, 'patientInfo' => null, 'code' => $code];
        }
        $old_email = $user->email;
        $updateStatus = $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        if ($updateStatus) {
            if ($request->email !== $old_email) {
                $user->generateCode();
                Mail::to($user->email)->send(new TwoFactorMail($user->code, $user->first_name));
            }
            $message = "profile updated successfully";
            $code = 200;
        } else {
            $message = "profile updated failed";
            $code = 400;
        }
        return ['message' => $message, 'patientInfo' => $user, 'code' => $code];
    }
    public function updatePassword($request)
    {
        $user = auth()->user();
        if (!$user) {
            $message = "user not found";
            $code = 404;
            return ['message' => $message, 'password' => null, 'code' => $code];
        }
        $updateStatus = $user->update([
            'password' => $request->password
        ]);
        if ($updateStatus) {
            $message = "password updated successfully";
            $code = 200;
        } else {
            $message = "password updated failed";
            $code = 400;
        }
        return ['message' => $message, 'password' => $user, 'code' => $code];
    }
    public function postMedicalAnalysis($request, $preview_id)
    {
        $user = auth()->user();
        if (!$user) {
            $message = "user not found";
            $code = 404;
            return ['message' => $message, 'filePath' => null, 'code' => $code];
        }
        $preview = Preview::find($preview_id);
        if (!$preview) {
            return ['message' => 'preview not found', 'filePath' => null, 'code' => 404];
        }
        // $patient = $user->patient;
        // $medical_analysis = MedicalAnalysis::where('patient_id', $patient->id)->where('preview_id', $preview_id)->first();
        // if ($medical_analysis) {
        //     $path = $medical_analysis->medical_analysis_path;
        //     $storagePath = str_replace('/storage/', '', $path);
        //     if (Storage::disk('public')->exists($storagePath))
        //         Storage::disk('public')->delete($storagePath);
        //     $medical_analysis->delete();
        // }
        if ($preview->diagnoseis_type !== 0) {
            $message = "diagnoseis type for this preview is completed you can't add a medical analysis";
            $code = 400;
            return ['message' => $message, 'filePath' => null, 'code' => $code];
        }
        $patient = auth()->user()->patient;
        $user_id = $user->id;
        $url = $this->ImageUpload($request, $user_id, 'Medical_analysis', 'file');
        if ($url) {
            MedicalAnalysis::create([
                'patient_id' => $patient->id,
                'preview_id' => $preview_id,
                'medical_analysis_path' => $url
            ]);
            $message = "medical analysis uploaded successfully";
            $code = 200;
        } else {
            $message = 'there is no file to upload';
            $code = 400;
        }
        return ['message' => $message, 'filePath' => $url, 'code' => $code];
    }
    public function getMedicalAnalysis($preview_id)
    {
        $user = auth()->user();
        if ($user) {
            $preview = Preview::find($preview_id);
            // if ($preview->diagnoseis_type !== 0) {
            //     $message = "diagnoseis type for this preview is completed you can't add a medical analysis";
            //     $code = 400;
            //     return ['message' => $message, 'Path' => null, 'code' => $code];
            // }
            $patient = $user->patient;
            $medical_analysis = MedicalAnalysis::where('patient_id', $patient->id)->where('preview_id', $preview_id)->get();
            if ($medical_analysis) {
                // $path = $medical_analysis->medical_analysis_path;
                // if ($path) {
                //     $message = 'file uploaded successfully';
                //     $code = 200;
                // } else {
                //     $message = 'you dont uploaded file yet';
                //     $code = 400;
                // }
                $message = "medical_analysis return successfully";
                $code = 200;
            } else {
                $message = "medical_analysis not found";
                $code = 404;
            }
        } else {
            $message = 'user not found';
            $code = 404;
        }
        return ['message' => $message, 'path' => $medical_analysis, 'code' => $code];
    }
    public function deleteMedicalAnalysis($medical_id)
    {
        $user = auth()->user();
        if (!$user) {
            $message = "user not found";
            $code = 404;
            return ['message' => $message, 'filePath' => null, 'code' => $code];
        }
        // $preview = Preview::find($preview_id);
        // if ($preview->diagnoseis_type !== 0) {
        //     $message = "diagnoseis type for this preview is completed you can't add a medical analysis";
        //     $code = 400;
        //     return ['message' => $message, 'filePath' => null, 'code' => $code];
        // }
        $patient = $user->patient;
        $medical_analysis = MedicalAnalysis::find($medical_id);
        if ($medical_analysis) {
            $path = $medical_analysis->medical_analysis_path;
            $storagePath = str_replace('/storage/', '', $path);
            if (Storage::disk('public')->exists($storagePath))
                Storage::disk('public')->delete($storagePath);
            $medical_analysis->delete();
            $message = "medical analysis deleted succussfully";
            $code = 200;
        } else {
            $message = "there is no medical analysis";
            $code = 400;
        }
        return ['message' => $message, 'filePath' => $medical_analysis, 'code' => $code];
    }
    public function addDoctorRate($request, $doctor_id)
    {
        $patient = auth()->user()->patient;
        $doctor = Doctor::find($doctor_id);
        if (!$doctor) {
            $message = "doctor not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        $preview = Preview::ofPatientAndDoctor($patient->id, $doctor_id)->first();
        if ($preview) {
            $rate = Rate::ofPatientAndDoctor($patient->id, $doctor_id)->first();
            if ($rate) {
                $message = "you rated this doctor before";
                $code = 400;
                return ['rate' => $rate, 'message' => $message, 'code' => $code];
            }
            $rate = Rate::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor_id,
                'rate' => $request->rate
            ]);
            if ($rate) {
                $message = "doctor rated successfully";
                $code = 200;
            } else {
                $message = "doctor rated failed";
                $code = 400;
            }
        } else {
            $message = "you dont have any preview for this doctor so you cant rated";
            $code = 400;
            $rate = null;
        }
        return ['message' => $message, 'rate' => $rate, 'code' => $code];
    }
    public function updateDoctorRate($request, $doctor_id)
    {
        $patient = auth()->user()->patient;
        $doctor = Doctor::find($doctor_id);
        if (!$doctor) {
            $message = "doctor not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        $rate = Rate::ofPatientAndDoctor($patient->id, $doctor_id)->first();
        if ($rate) {
            $updateRate = $rate->update([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor_id,
                'rate' => $request->rate
            ]);
            if ($updateRate) {
                $message = "rate updated successfully";
                $code = 200;
            } else {
                $message = "rate updated failed";
                $code = 400;
            }
        } else {
            $message = "rate not found";
            $code = 404;
        }
        return ['rate' => $rate, 'message' => $message, 'code' => $code];
    }
    public function deleteDoctorRate($doctor_id)
    {
        $patient = auth()->user()->patient;
        $doctor = Doctor::find($doctor_id);
        if (!$doctor) {
            $message = "doctor not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        $rate = Rate::ofPatientAndDoctor($patient->id, $doctor_id)->first();
        if ($rate) {
            $deleteRate = $rate->delete();
            if ($deleteRate) {
                $message = "rate deleted successfully";
                $code = 200;
            } else {
                $message = "rate deleted failed";
                $code = 400;
            }
        } else {
            $message = "rate not found";
            $code = 404;
        }
        return ['rate' => $rate, 'message' => $message, 'code' => $code];
    }
    public function getDoctorRate($doctor_id)
    {
        $patient = auth()->user()->patient;
        $doctor = Doctor::find($doctor_id);
        if (!$doctor) {
            $message = "doctor not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        if (!$patient) {
            $message = "patient not found";
            $code = 404;
            return ['rate' => null, 'message' => $message, 'code' => $code];
        }
        $rate = Rate::ofPatientAndDoctor($patient->id, $doctor_id)->first();
        if ($rate) {
            $message = "rate return successfully";
            $code = 200;
        } else {
            $message = "rate not found";
            $code = 404;
        }
        return ['rate' => $rate, 'message' => $message, 'code' => $code];
    }

    public function analyseSymtoms()
    {
        $locale = request()->input('lang');
        App::setLocale($locale);
        if (!$locale) {
            return [
                'status' => 400,
                'message' => 'you must enter the lang type'
            ];
        }
        try {
            $symptoms = request()->input('symptoms', []);

            if (empty($symptoms)) {
                return [
                    'status' => 400,
                    'message' => 'No symptoms provided.',
                ];
            }
            $identifiedDepartments = [];
            $chestPain = Symbtom::where('symbtom_name', '{"en":"Chest Pain","ar":"ألم في الصدر"}')->first();
            $shortnessOfBreath = Symbtom::where('symbtom_name', '{"en":"Shortness of Breath","ar":"ضيق في التنفس"}')->first();
            $palpitations = Symbtom::where('symbtom_name', '{"en":"Palpitations","ar":"خفقان القلب"}')->first();
            $swellingInLegs = Symbtom::where('symbtom_name', '{"en":"Swelling in Legs","ar":"تورم الساقين"}')->first();
            $fatigue = Symbtom::where('symbtom_name', '{"en":"Fatigue","ar":"إرهاق"}')->first();
            $rapidHeartbeat = Symbtom::where('symbtom_name', '{"en":"Rapid Heartbeat","ar":"خفقان سريع"}')->first();
            $fainting = Symbtom::where('symbtom_name', '{"en":"Fainting","ar":"إغماء"}')->first();
            $highBloodPressure = Symbtom::where('symbtom_name', '{"en":"High Blood Pressure","ar":"ارتفاع ضغط الدم"}')->first();
            // _____________________________________________________________________________________
            $headaches = Symbtom::where('symbtom_name', '{"en":"Headaches","ar":"صداع"}')->first();
            $dizziness = Symbtom::where('symbtom_name', '{"en":"Dizziness","ar":"دوخة"}')->first();
            $numbness = Symbtom::where('symbtom_name', '{"en":"Numbness","ar":"خدر"}')->first();
            $muscleWeakness = Symbtom::where('symbtom_name', '{"en":"Muscle Weakness","ar":"ضعف العضلات"}')->first();
            $seizures = Symbtom::where('symbtom_name', '{"en":"Seizures","ar":"نوبات صرع"}')->first();
            $memoryLoss = Symbtom::where('symbtom_name', '{"en":"Memory Loss","ar":"فقدان الذاكرة"}')->first();
            $tingling = Symbtom::where('symbtom_name', '{"en":"Tingling","ar":"تنميل"}')->first();
            $lossOfConsciousness = Symbtom::where('symbtom_name', '{"en":"Loss of Consciousness","ar":"فقدان الوعي"}')->first();
            $tremors = Symbtom::where('symbtom_name', '{"en":"Tremors","ar":"رعشة"}')->first();
            $visionProblems = Symbtom::where('symbtom_name', '{"en":"Vision Problems","ar":"مشاكل في الرؤية"}')->first();
            // _____________________________________________________________________________________

            $abdominalPain = Symbtom::where('symbtom_name', '{"en":"Abdominal Pain","ar":"ألم في البطن"}')->first();
            $nausea = Symbtom::where('symbtom_name', '{"en":"Nausea","ar":"غثيان"}')->first();
            $diarrhea = Symbtom::where('symbtom_name', '{"en":"Diarrhea","ar":"إسهال"}')->first();
            $constipation = Symbtom::where('symbtom_name', '{"en":"Constipation","ar":"إمساك"}')->first();
            $heartburn = Symbtom::where('symbtom_name', '{"en":"Heartburn","ar":"حرقة المعدة"}')->first();
            $bloating = Symbtom::where('symbtom_name', '{"en":"Bloating","ar":"انتفاخ"}')->first();
            $vomiting = Symbtom::where('symbtom_name', '{"en":"Vomiting","ar":"قيء"}')->first();
            $bloodInStool = Symbtom::where('symbtom_name', '{"en":"Blood in Stool","ar":"دم في البراز"}')->first();
            $lossOfAppetite = Symbtom::where('symbtom_name', '{"en":"Loss of Appetite","ar":"فقدان الشهية"}')->first();
            $difficultySwallowing = Symbtom::where('symbtom_name', '{"en":"Difficulty Swallowing","ar":"صعوبة في البلع"}')->first();

            // _____________________________________________________________________________________
            $chronicCough = Symbtom::where('symbtom_name', '{"en":"Chronic Cough","ar":"سعال مزمن"}')->first();
            $wheezing = Symbtom::where('symbtom_name', '{"en":"Wheezing","ar":"صفير في التنفس"}')->first();
            $dyspnea = Symbtom::where('symbtom_name', '{"en":"Shortness of Breath (Dyspnea)","ar":"ضيق في التنفس"}')->first();
            $chestTightness = Symbtom::where('symbtom_name', '{"en":"Chest Tightness","ar":"ضيق في الصدر"}')->first();
            $respInfections = Symbtom::where('symbtom_name', '{"en":"Frequent Respiratory Infections","ar":"عدوى تنفسية متكررة"}')->first();
            $coughingUpBlood = Symbtom::where('symbtom_name', '{"en":"Coughing Up Blood","ar":"السعال مع دم"}')->first();
            $snoring = Symbtom::where('symbtom_name', '{"en":"Snoring","ar":"الشخير"}')->first();
            $hoarseness = Symbtom::where('symbtom_name', '{"en":"Hoarseness","ar":"بحة الصوت"}')->first();

            // _____________________________________________________________________________________
            $jointPain = Symbtom::where('symbtom_name', '{"en":"Joint Pain","ar":"ألم المفاصل"}')->first();
            $jointSwelling = Symbtom::where('symbtom_name', '{"en":"Swelling of Joints","ar":"تورم المفاصل"}')->first();
            $limitedMotion = Symbtom::where('symbtom_name', '{"en":"Limited Range of Motion","ar":"نطاق حركة محدود"}')->first();
            $musclePain = Symbtom::where('symbtom_name', '{"en":"Muscle Pain","ar":"ألم العضلات"}')->first();
            $fractures = Symbtom::where('symbtom_name', '{"en":"Fractures","ar":"كسور"}')->first();
            $backPain = Symbtom::where('symbtom_name', '{"en":"Back Pain","ar":"ألم في الظهر"}')->first();
            $neckPain = Symbtom::where('symbtom_name', '{"en":"Neck Pain","ar":"ألم الرقبة"}')->first();
            $bonePain = Symbtom::where('symbtom_name', '{"en":"Bone Pain","ar":"ألم العظام"}')->first();
            $stiffness = Symbtom::where('symbtom_name', '{"en":"Stiffness","ar":"تصلب"}')->first();

            // _____________________________________________________________________________________

            $feverChildren = Symbtom::where('symbtom_name', '{"en":"Fever (in children)","ar":"حمى (عند الأطفال)"}')->first();
            $rashChildren = Symbtom::where('symbtom_name', '{"en":"Rash (in children)","ar":"طفح جلدي (عند الأطفال)"}')->first();
            $earInfection = Symbtom::where('symbtom_name', '{"en":"Ear Infection","ar":"عدوى الأذن"}')->first();
            $soreThroat = Symbtom::where('symbtom_name', '{"en":"Sore Throat","ar":"التهاب الحلق"}')->first();
            $growthDelays = Symbtom::where('symbtom_name', '{"en":"Growth Delays","ar":"تأخر في النمو"}')->first();
            $behavioralChanges = Symbtom::where('symbtom_name', '{"en":"Behavioral Changes","ar":"تغيرات سلوكية"}')->first();
            $poorAppetite = Symbtom::where('symbtom_name', '{"en":"Poor Appetite","ar":"ضعف الشهية"}')->first();
            $developmentalDelays = Symbtom::where('symbtom_name', '{"en":"Developmental Delays","ar":"تأخر في النمو"}')->first();
            $bedwetting = Symbtom::where('symbtom_name', '{"en":"Bedwetting","ar":"التبول اللاإرادي"}')->first();

            // _____________________________________________________________________________________
            $skinRash = Symbtom::where('symbtom_name', '{"en":"Skin Rash","ar":"طفح جلدي"}')->first();
            $itching = Symbtom::where('symbtom_name', '{"en":"Itching (Pruritus)","ar":"حكة (حِكاك)"}')->first();
            $acne = Symbtom::where('symbtom_name', '{"en":"Acne","ar":"حب الشباب"}')->first();
            $eczema = Symbtom::where('symbtom_name', '{"en":"Eczema","ar":"أكزيما"}')->first();
            $psoriasis = Symbtom::where('symbtom_name', '{"en":"Psoriasis","ar":"صدفية"}')->first();
            $skinLesions = Symbtom::where('symbtom_name', '{"en":"Moles/Skin Lesions","ar":"شامات/آفات جلدية"}')->first();
            $hairLoss = Symbtom::where('symbtom_name', '{"en":"Hair Loss","ar":"تساقط الشعر"}')->first();
            $nailChanges = Symbtom::where('symbtom_name', '{"en":"Nail Changes","ar":"تغيرات الأظافر"}')->first();
            $skinDiscoloration = Symbtom::where('symbtom_name', '{"en":"Skin Discoloration","ar":"تغير لون الجلد"}')->first();

            // _____________________________________________________________________________________
            $moles = Symbtom::where('symbtom_name', '{"en":"Moles/Skin Lesions","ar":"شامات/آفات جلدية"}')->first();
            $frequentUrination = Symbtom::where('symbtom_name', '{"en":"Frequent Urination","ar":"تبول متكرر"}')->first();
            $dysuria = Symbtom::where('symbtom_name', '{"en":"Painful Urination (Dysuria)","ar":"تبول مؤلم (عُسر التبول)"}')->first();
            $hematuria = Symbtom::where('symbtom_name', '{"en":"Blood in Urine (Hematuria)","ar":"دم في البول (بيلة دموية)"}')->first();
            $kidneyStones = Symbtom::where('symbtom_name', '{"en":"Kidney Stones","ar":"حصى الكلى"}')->first();
            $incontinence = Symbtom::where('symbtom_name', '{"en":"Urinary Incontinence","ar":"سلس البول"}')->first();
            $urinaryRetention = Symbtom::where('symbtom_name', '{"en":"Urinary Retention","ar":"احتباس البول"}')->first();
            $testicularPain = Symbtom::where('symbtom_name', '{"en":"Testicular Pain","ar":"ألم في الخصية"}')->first();


            foreach ($symptoms as $symptom) {
                $cleanedSymptom = $symptom;
                $matchedDepartment = null;
                if (in_array($cleanedSymptom, [$chestPain->getTranslation('symbtom_name', $locale), $shortnessOfBreath->getTranslation('symbtom_name', $locale), $palpitations->getTranslation('symbtom_name', $locale), $swellingInLegs->getTranslation('symbtom_name', $locale), $fatigue->getTranslation('symbtom_name', $locale), $rapidHeartbeat->getTranslation('symbtom_name', $locale), $fainting->getTranslation('symbtom_name', $locale), $highBloodPressure->getTranslation('symbtom_name', $locale)])) {
                    $matchedDepartment = Department::where('name->en', 'Cardiology')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $headaches->getTranslation('symbtom_name', $locale),
                        $dizziness->getTranslation('symbtom_name', $locale),
                        $numbness->getTranslation('symbtom_name', $locale),
                        $muscleWeakness->getTranslation('symbtom_name', $locale),
                        $seizures->getTranslation('symbtom_name', $locale),
                        $memoryLoss->getTranslation('symbtom_name', $locale),
                        $tingling->getTranslation('symbtom_name', $locale),
                        $lossOfConsciousness->getTranslation('symbtom_name', $locale),
                        $tremors->getTranslation('symbtom_name', $locale),
                        $visionProblems->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Neurology')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $abdominalPain->getTranslation('symbtom_name', $locale),
                        $nausea->getTranslation('symbtom_name', $locale),
                        $diarrhea->getTranslation('symbtom_name', $locale),
                        $constipation->getTranslation('symbtom_name', $locale),
                        $heartburn->getTranslation('symbtom_name', $locale),
                        $bloating->getTranslation('symbtom_name', $locale),
                        $vomiting->getTranslation('symbtom_name', $locale),
                        $bloodInStool->getTranslation('symbtom_name', $locale),
                        $lossOfAppetite->getTranslation('symbtom_name', $locale),
                        $difficultySwallowing->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Gastroenterology')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $chronicCough->getTranslation('symbtom_name', $locale),
                        $wheezing->getTranslation('symbtom_name', $locale),
                        $shortnessOfBreath->getTranslation('symbtom_name', $locale),
                        $chestTightness->getTranslation('symbtom_name', $locale),
                        $respInfections->getTranslation('symbtom_name', $locale),
                        $coughingUpBlood->getTranslation('symbtom_name', $locale),
                        $snoring->getTranslation('symbtom_name', $locale),
                        $hoarseness->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Pulmonology')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $jointPain->getTranslation('symbtom_name', $locale),
                        $jointSwelling->getTranslation('symbtom_name', $locale),
                        $limitedMotion->getTranslation('symbtom_name', $locale),
                        $musclePain->getTranslation('symbtom_name', $locale),
                        $fractures->getTranslation('symbtom_name', $locale),
                        $backPain->getTranslation('symbtom_name', $locale),
                        $neckPain->getTranslation('symbtom_name', $locale),
                        $bonePain->getTranslation('symbtom_name', $locale),
                        $stiffness->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Orthopedics')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $feverChildren->getTranslation('symbtom_name', $locale),
                        $rashChildren->getTranslation('symbtom_name', $locale),
                        $earInfection->getTranslation('symbtom_name', $locale),
                        $soreThroat->getTranslation('symbtom_name', $locale),
                        $growthDelays->getTranslation('symbtom_name', $locale),
                        $behavioralChanges->getTranslation('symbtom_name', $locale),
                        $poorAppetite->getTranslation('symbtom_name', $locale),
                        $developmentalDelays->getTranslation('symbtom_name', $locale),
                        $bedwetting->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Pediatrics')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $skinRash->getTranslation('symbtom_name', $locale),
                        $itching->getTranslation('symbtom_name', $locale),
                        $acne->getTranslation('symbtom_name', $locale),
                        $eczema->getTranslation('symbtom_name', $locale),
                        $psoriasis->getTranslation('symbtom_name', $locale),
                        $skinLesions->getTranslation('symbtom_name', $locale),
                        $hairLoss->getTranslation('symbtom_name', $locale),
                        $nailChanges->getTranslation('symbtom_name', $locale),
                        $skinDiscoloration->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Dermatology')->first();
                } elseif (
                    in_array($cleanedSymptom, [
                        $frequentUrination->getTranslation('symbtom_name', $locale),
                        $dysuria->getTranslation('symbtom_name', $locale),
                        $hematuria->getTranslation('symbtom_name', $locale),
                        $kidneyStones->getTranslation('symbtom_name', $locale),
                        $incontinence->getTranslation('symbtom_name', $locale),
                        $urinaryRetention->getTranslation('symbtom_name', $locale),
                        $testicularPain->getTranslation('symbtom_name', $locale),
                    ])
                ) {
                    $matchedDepartment = Department::where('name->en', 'Urology')->first();
                }
                if ($matchedDepartment === null) {
                    return [
                        'status' => 400,
                        'message' => "Our system can't categorize the symptom '{$symptom}'. Please consult a general practitioner.",
                    ];
                }
                $identifiedDepartments[] = $matchedDepartment;
            }
            $uniqueDepartments = array_unique($identifiedDepartments);
            if (count($uniqueDepartments) > 1) {
                return [
                    'status' => 400,
                    'message' => "Your symptoms suggest multiple departments. Please consult a general practitioner for a comprehensive diagnosis.",
                ];
            }

            $finalDepartment = !empty($uniqueDepartments) ? $uniqueDepartments[0] : null;
            $formatedDepartment = $finalDepartment->toArray();
            $formatedDepartment['name'] = $finalDepartment->getTranslation('name', $locale);
            $formatedDepartment['description'] = $finalDepartment->getTranslation('description', $locale);
            if ($finalDepartment) {
                return [
                    'status' => 200,
                    'message' => "Based on your symptoms, you should go to the **" . $finalDepartment['name'] . "** department.",
                    'data' => [
                        'suggested_department' => $formatedDepartment,
                        'symptoms_provided' => $symptoms
                    ]
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => "No clear department could be identified based on your input.",
                ];
            }
        } catch (\Exception $e) {

            return [
                'status' => 500,
                'errors' => 'An internal server error occurred: ' . $e->getMessage()
            ];
        }
    }
    public function searchDoctors()
    {
        $query = request('query');

        if (!$query) {
            return ['message' => 'search input is required', 'doctors' => null, 'code' => 400];
        }
        $doctors = Doctor::whereHas('user', function ($q) use ($query) {
            $q->where('first_name', 'like', '%' . $query . '%')
                ->orWhere('last_name', 'like', '%' . $query . '%');
        })->with('user')->get();
        if ($doctors) {
            $message = "found successfully";
            $code = 200;
        } else {
            $message = "found failed";
            $code = 400;
        }
        return ['message' => $message, 'doctors' => $doctors, 'code' => $code];

    }
    public function searchDepartments()
    {
        $locale = request()->input('lang');
        App::setLocale($locale);
        if (!$locale) {
            return ['message' => 'you must enter the lang type', 'departments' => null, 'code' => 400];
        }

        $query = request('query');

        if (!$query) {
            return ['message' => 'search input is required', 'departments' => null, 'code' => 400];
        }
        $departments = Department::where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->get()->map(function ($department) use ($locale) {
                $data = $department->toArray();

                $data['name'] = $department->getTranslation('name', $locale);
                $data['description'] = $department->getTranslation('description', $locale);

                return $data;
            });
        if ($departments) {
            $message = "found successfully";
            $code = 200;
        } else {
            $message = "found failed";
            $code = 400;
        }
        return ['message' => $message, 'departments' => $departments, 'code' => $code];

    }
    public function getSymbtoms()
    {
        $locale = request()->input('lang');
        App::setLocale($locale);
        if (!$locale) {
            return ['message' => 'you must enter the lang type', 'symbtoms' => null, 'code' => 400];
        }
        $symbtoms = Symbtom::all()->map(function ($symbtom) use ($locale) {
            return [
                'symbtom_name' => $symbtom->getTranslation('symbtom_name', $locale),
            ];
        });
        if ($symbtoms) {
            $message = "symbtoms return successfully";
            $code = 200;
        } else {
            $message = "symbtoms return failes";
            $code = 400;
        }
        return ['message' => $message, 'symbtoms' => $symbtoms, 'code' => $code];
    }
    public function postNewPayment($request)
    {
        $user = auth()->user();
        if (!$user) {
            return ['message' => 'user not found', 'payment' => null, 'code' => 404];
        }
        $paymentCompany = PaymentCompany::create([
            'user_id' => $user->id,
            'phone_number' => $request->phone_number,
            'company_name' => $request->company_name,
            'balance' => rand(2, 3) * 100000
        ]);
        if ($paymentCompany) {
            $message = "new payment way added successfully";
            $code = 200;
        } else {
            $message = "new payment way added failed";
            $code = 400;
        }
        return ['message' => $message, 'payment' => $paymentCompany, 'code' => $code];
    }
    public function getPayments()
    {
        $user = auth()->user();
        if (!$user) {
            return ['message' => 'user not found', 'payments' => null, 'code' => 404];
        }
        $payments = PaymentCompany::where('user_id', $user->id)->get();
        if ($payments) {
            $message = "payments return successfully";
            $code = 200;
        } else {
            $message = "payments return failed";
            $code = 400;
        }
        return ['payments' => $payments, 'message' => $message, 'code' => $code];
    }
    public function getPayment($payment_id)
    {
        $user = auth()->user();
        if (!$user) {
            return ['message' => 'user not found', 'payments' => null, 'code' => 404];
        }
        $payment = PaymentCompany::where('user_id', $user->id)->find($payment_id);
        if ($payment) {
            $message = "payments return successfully";
            $code = 200;
        } else {
            $message = "payments return failed";
            $code = 400;
        }
        return ['message' => $message, 'payment' => $payment, 'code' => $code];
    }
    public function getAppointmentById($child_id)
    {
        $user = auth()->user();
        $patient = $user->patient;
        if (!$patient) {
            return ['message' => 'patient not found', 'appointment' => null, 'code' => 404];
        }
        $sonPatientIds = Son::where('parent_id', $user->id)->pluck('patient_id')->toArray();
        $allPatientIds = array_merge([$patient->id], $sonPatientIds);
        $appointment = Apointment::whereIn('patient_id', $allPatientIds)
            ->with(['patient.user', 'doctor.user'])
            ->where('patient_id', $child_id)
            ->first();
        if ($appointment) {
            // $appointmentUser = $appointment->patient->user ?? null;
            // $doctorUser = $appointment->doctor->user ?? null;
            // $appointment->patientName = $appointmentUser ? $appointmentUser->name : null;
            // $appointment->doctorName = $doctorUser ? $doctorUser->name : ($appointment->doctor->name ?? null);
            // $appointment->gender = $appointmentUser ? $appointmentUser->gender : null;
            // $appointment->imgPath = $appointmentUser ? $appointmentUser->img_path : null;
            $appointment->makeHidden(['patient', 'doctor']);
            $message = "appointment return successfully";
            $code = 200;
        } else {
            $message = "appointment return failed";
            $code = 404;
        }

        return ['message' => $message, 'appointment' => $appointment, 'code' => $code];
    }
}
