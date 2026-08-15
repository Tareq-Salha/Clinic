<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Responses\Response;
use App\ResponseJson;
use App\Services\UserService;
use Illuminate\Http\Request;
use Throwable;
class UserController extends Controller
{
    use ResponseJson;
    private $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function getDoctor($id)
    {
        $data = $this->userService->getDoctor($id);
        return match ($data['status']) {
            200 => $this->response($data['message'], ['doctor' => $data['data']], 200),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDoctors()
    {
        $data = $this->userService->getDoctors();
        return match ($data['status']) {
            200 => $this->response(__('messages.doctors_returned'), ['doctors' => $data['data']], 200),
            404 => $this->response(__('messages.no_doctors_found_general'), null, 404),
            422 => $this->response(__('messages.validation_failed'), null, 422),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDepartment($id)
    {
        $data = $this->userService->getDepartment($id);
        return match ($data['status']) {
            200 => $this->response(__('messages.department_returned'), ['department' => $data['data']], 200),
            404 => $this->response(__('messages.no_department_found'), null, 404),
            422 => $this->response(__('messages.validation_failed'), null, 422),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            400 => $this->response($data['message'], $data['data'], $data['status']),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDepartments()
    {
        $data = $this->userService->getDepartments();
        return match ($data['status']) {
            200 => $this->response(__('messages.all_departments_returned'), ['departments' => $data['data']], 200),
            404 => $this->response(__('messages.no_departments_found'), null, 404),
            422 => $this->response(__('messages.validation_failed'), null, 422),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            400 => $this->response($data['message'], $data['data'], $data['status']),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getLeaves($doctorId)
    {
        $data = $this->userService->getLeaves($doctorId);
        return match ($data['status']) {
            200 => $this->response(__('messages.doctor_info_returned'), ['leaves' => $data['data']], 200),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDoctorsByDepartment($departmentId)
    {
        $data = $this->userService->getDoctorsByDepartment($departmentId);
        return match ($data['status']) {
            200 => $this->response(__('messages.doctors_returned'), ['doctors' => $data['data']], 200),
            404 => $this->response(__('messages.department_not_found'), null, 404),
            400 => $this->response(__('messages.no_doctors_found_general'), null, 400),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDoctorsInDayAndDepartment($dayId, $departmentId)
    {
        $data = $this->userService->getDoctorsInDayAndDepartment($dayId, $departmentId);
        return match ($data['status']) {
            200 => $this->response(__('messages.doctors_returned'), ['doctors' => $data['data']], 200),
            404 => $this->response(__('messages.day_not_found_general'), null, 404),
            400 => $this->response(__('messages.no_doctors_found_general'), null, 400),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDoctorsAndDepartment($dayId)
    {
        $data = $this->userService->getDoctorsAndDepartment($dayId);
        return match ($data['status']) {
            200 => $this->response(__('messages.departments_with_doctors_of_day_returned'), $data['data'], 200),
            404 => $this->response($data['message'], null, 404),
            400 => $this->response($data['message'], null, 400),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function search()
    {
        $data = $this->userService->search();
        return match ($data['status']) {
            200 => $this->response(__('messages.found_successfully'), ['results' => $data['data']], 200),
            404 => $this->response(__('messages.no_results_found'), null, 404),
            422 => $this->response(__('messages.validation_failed'), null, 422),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            400 => $this->response($data['message'], $data['data'], $data['status']),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getProfileImage()
    {
        $data = [];
        try {
            $data = $this->userService->getProfileImage();
            return Response::Success($data['path'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }
    public function deleteProfileImage()
    {
        $data = [];
        try {
            $data = $this->userService->deleteProfileImage();
            return Response::Success($data['path'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            $message = $th->getMessage();
            return Response::Error($data, $message);
        }
    }
    public function getNotifications(){
        $data=$this->userService->getNotifications();
        return match($data['status']){
             200 => $this->response(__('messages.found_successfully'), ['notifications' => $data['data']], 200),
            500 => $this->response(__('messages.server_error', ['error' => $data['errors'] ?? '']), null, 500),
            400 => $this->response($data['message'], $data['data'], $data['status']),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }

}