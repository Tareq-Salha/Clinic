<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Middleware\DoctorMiddleware;
use App\Http\Middleware\PatientMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleWare;
use App\Http\Middleware\SecretaryMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::post('/varify', [TwoFactorController::class, 'varify']);
    Route::get('/resendCode', [TwoFactorController::class, 'resendCode']);
});
Route::group(
    ['middleware' => ['api', 'auth', AdminMiddleWare::class, SetLocale::class, TwoFactor::class]],
    function ($router) {
        Route::post('admin/secretary', [AdminController::class, 'createSecretary']);
        Route::put('admin/secretary', [AdminController::class, 'updateSecretary']);
        Route::delete('admin/secretary', [AdminController::class, 'deleteSecretary']);
        Route::post('admin/doctor', [AdminController::class, 'createDoctor']);

        Route::delete('admin/doctor/{id}', [AdminController::class, 'deleteDoctor']);
        Route::post('admin/department', [AdminController::class, 'createDepartment']);

        Route::delete('admin/department/{id}', [AdminController::class, 'deleteDepartment']);
        Route::delete('admin/user/{id}', [AdminController::class, 'deleteUser']);
    }
);

Route::group([
    'middleware' => [TwoFactor::class, DoctorMiddleware::class, SetLocale::class, 'api', 'auth']
], function ($router) {
    Route::post('/postArticle', [DoctorController::class, 'postArticale']);
    Route::put('/updateArticle/{id}', [DoctorController::class, 'updateArticle']);
    Route::delete('/deleteArticle/{id}', [DoctorController::class, 'deleteArticle']);
    Route::get('/getAtricles', [DoctorController::class, 'getArticles']);
    Route::get('/getAtricleById/{id}', [DoctorController::class, 'getArticleById']);
    Route::post('/imageUpload', [DoctorController::class, 'uploadImages']);
    Route::post('/imageProfileUpload', [DoctorController::class, 'uploadImagesForProfile']);
    Route::put('/updateProfile', [DoctorController::class, 'updateProfile']);
    Route::get('/getApointments', [DoctorController::class, 'getApointments']);
    Route::post('/postPreview/{id}', [DoctorController::class, 'postPreview']);
    Route::put('/updatePreview/{id}', [DoctorController::class, 'updatePreview']);
    Route::delete('/deletePreview/{id}', [DoctorController::class, 'deletepreview']);
    Route::get('/doctor/previews', [DoctorController::class, 'getPreviews']);
    Route::get('/getPreviedPatients', [DoctorController::class, 'getPreviedPatients']);
    Route::post('/patientSearch', [DoctorController::class, 'patientSearch']);
    Route::get('/getActivePatientInfo', [DoctorController::class, 'getActivePatientInfo']);
    Route::get('/getPreviewById/{preview_id}', [DoctorController::class, 'getPreviewById']);

});

Route::group([
    'middleware' => [TwoFactor::class, PatientMiddleware::class, 'api', 'auth']
], function ($router) {
    Route::post('/pateintProfile', [PatientController::class, 'postPatientProfile']);
    Route::put('/updatePatientProfile', [PatientController::class, 'updatePatientProfile']);
    Route::post('/addChild', [PatientController::class, 'addChild']);
    Route::put('/updateChild/{child_id}', [PatientController::class, 'updateChild']);
    Route::delete('/deleteChild/{child_id}', [PatientController::class, 'deleteChild']);
    Route::get('/getChilds', [PatientController::class, 'getChilds']);
    Route::get('/getArticlesApp', [PatientController::class, 'getArticles']);
    Route::post('/addArticleFav/{id}', [PatientController::class, 'addArticleFav']);
    Route::delete('/deleteArticleFav/{id}', [PatientController::class, 'deleteArticleFav']);
    Route::get('/getArticlesFav', [PatientController::class, 'getArticlesFav']);
    Route::post('/bookAppointment/{doctor_id}', [PatientController::class, 'bookAppointment']);
    Route::put('/updateAppointment/{appointment_id}', [PatientController::class, 'updateAppointment']);
    Route::delete('/deleteAppointment/{appointemnt_id}', [PatientController::class, 'deleteAppointment']);
    Route::get('/getAppointments', [PatientController::class, 'getAppointments']);
    Route::get('/getAppointmentById/{child_id}', [PatientController::class, 'getAppointmentById']);
    Route::get('/getPreviews', [PatientController::class, 'getPreviews']);
    Route::post('/uploadImagesForPatientProfile', [PatientController::class, 'uploadImagesForProfile']);
    Route::put('/updateProfileInfo', [PatientController::class, 'updateProfileInfo']);
    Route::put('/updatePassword', [PatientController::class, 'updatePassword']);
    Route::post('/postMedicalAnalysis/{preview_id}', [PatientController::class, 'postMedicalAnalysis']);
    Route::get('/getMedicalAnalysis/{preview_id}', [PatientController::class, 'getMedicalAnalysis']);
    Route::delete('/deleteMedicalAnalysis/{medical_id}', [PatientController::class, 'deleteMedicalAnalysis']);
    Route::post('symptom/analyze', [PatientController::class, 'analyzeSymptoms']);
    Route::post('/searchDoctors', [PatientController::class, 'searchDoctors']);
    Route::post('/searchDepartments', [PatientController::class, 'searchDepartments']);
    Route::get('/getSymbtoms', [PatientController::class, 'getSymbtoms']);
    Route::post('/postNewPayment', [PatientController::class, 'postNewPayment']);
    Route::get('/getPayments', [PatientController::class, 'getPayments']);
    Route::get('/getPayment/{payment_id}', [PatientController::class, 'getPayment']);

    Route::post('/addDoctorRate/{doctor_id}', [PatientController::class, 'addDoctorRate']);
    Route::put('/updateDoctorRate/{doctor_id}', [PatientController::class, 'updateDoctorRate']);
    Route::delete('/deleteDoctorRate/{doctor_id}', [PatientController::class, 'deleteDoctorRate']);
    Route::get('/getDoctorRate/{doctor_id}', [PatientController::class, 'getDoctorRate']);

});


Route::group([
    'middleware' => [TwoFactor::class, SecretaryMiddleware::class, SetLocale::class, 'api', 'auth']
], function ($router) {
    Route::post('secretary/leave/', [SecretaryController::class, 'addMounthlyLeaves']);
    Route::delete('secretary/leave', [SecretaryController::class, 'removeMonthlyLeaves']);
    Route::get('secretary/leave', [SecretaryController::class, 'monthlyLeaves']);
    Route::post('secretary/appointment/{id}', [SecretaryController::class, 'acceptReverse']);
    Route::delete('secretary/appointment/{id}', [SecretaryController::class, 'rejectReverse']);
    Route::get('secretary/appointment/{doctor_id}/{appointment_date}', [SecretaryController::class, 'appointments']);
    Route::get('secretary/appointments', [SecretaryController::class, 'apointments']);
    Route::get('/secretary/search', [SecretaryController::class, 'search']);
    Route::post('secretary/rate', [SecretaryController::class, 'relaseRate']);
    Route::post('secretary/appointment', [SecretaryController::class, 'reverse']);
    Route::post('secretary/unapp/appointment', [SecretaryController::class, 'reverseUnApp']);
    Route::get('secretary/patient/{apointmentId}', [SecretaryController::class, 'enterPatient']);
    Route::get('secretary/doctors', [SecretaryController::class, 'getDoctors']);
    Route::post('secretary/doctor/{id}', [AdminController::class, 'updateDoctor']);
    Route::get('secretary/manage/{dayId}', [SecretaryController::class, 'secretaryInfo']);
});
//////Any Body Can Access
Route::group(['middleware' => [TwoFactor::class, SetLocale::class, 'api', 'auth']], function ($router) {

    Route::get('/doctor', [UserController::class, 'getDoctors']);
    Route::get('/department', [UserController::class, 'getDepartments']);
    Route::get('department/doctor/{departmentId}', [UserController::class, 'getDoctorsByDepartment']);
    Route::get('doctor/{id}', [UserController::class, 'getDoctor']);
    Route::get('department/{id}', [UserController::class, 'getDepartment']);
    Route::get('/leave/{id}', [UserController::class, 'getLeaves']);
    Route::get('/notifications', [UserController::class, 'getNotifications']);

    Route::get('/doctor/{dayId}/{departmentId}', [UserController::class, 'getDoctorsInDayAndDepartment']);
    Route::get('/doctors/{dayId}', [UserController::class, 'getDoctorsAndDepartment']);
    Route::get('/search', [UserController::class, 'search']);

    Route::get('/getProfileImage', [UserController::class, 'getProfileImage']);
    Route::delete('/deleteProfileImage', [UserController::class, 'deleteProfileImage']);
});
Route::post('password/request', [PasswordController::class, 'sendConfirmationEmail']);
Route::post('password/confirm', [PasswordController::class, 'confirmReset']);
Route::post('password/reset', [PasswordController::class, 'resetPassword']);
