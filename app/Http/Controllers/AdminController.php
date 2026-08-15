<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\ResponseJson;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use ResponseJson;

    protected $AdminService;

    public function __construct(AdminService $AdminService)
    {
        $this->AdminService = $AdminService;
    }
    public function createSecretary()
    {
        $data = $this->AdminService->createSecretary();
        //  if ($data['msg']) {
//             return $this->response($data['msg'], null, 400);
//         }
        return match ($data['status']) {
            201 => $this->response(__('message.secretary_created_successfully'), ['secretary' => $data['user']], 201),
            400 => $this->response(__('message.validation_failed'), $data['errors'], 400),
            409 => $this->response(__('message.secretary_already_exists'), null, 409),
            500 => $this->response(__('message.server_error', ['error' => $data['error']]), null, 500),
            default => $this->response(__('message.unknown_error'), null, 520),
        };
    }

    public function updateSecretary()
    {
        $data = $this->AdminService->updateSecretary();
        if ($data->original) {
            return $this->response($data->original, null, 400);
        }
        return $this->response(__('message.secretary_updated_successfully'), ['secretary' => $data], 200);
    }
    public function updateDoctor($id)
    {
        $data = $this->AdminService->updateDoctor($id);
        if ($data->original) {
            return $this->response($data->original, null, 400);
        }
        return $this->response(__('message.doctor_updated_successfully'), ['doctor' => $data], 200);
    }
    public function deleteSecretary()
    {
        $data = $this->AdminService->deleteSecretary();
        if ($data == null) {
            return $this->response(__('message.secretary_deleted_successfully'), ['secretary' => null], 200);
        }
        return $this->response($data, null, 400);
    }
    public function createDoctor()
    {
        $data = $this->AdminService->createDoctor();
        if ($data->original) {
            return $this->response($data->original, null, 400);
        }
        return $this->response(__('message.doctor_created_successfully'), ['doctor' => $data], 201);
    }



    public function deleteDoctor($id)
    {
        $data = $this->AdminService->deleteDoctor($id);
        if ($data == null) {
            return $this->response(__('message.doctor_deleted_successfully'), ['doctor' => null], 200);
        }
    }



    public function createDepartment()
    {
        $data = $this->AdminService->createDepartment();
        if ($data->original) {
            return $this->response($data->original, null, 400);
        }
        return $this->response(__('message.department_created_successfully'), ['department' => $data], 201);
    }


    public function deleteDepartment($id)
    {
        $data = $this->AdminService->deleteDepartment($id);
        if ($data == null) {
            return $this->response(__('message.department_deleted_successfully'), ['department' => null], 200);
        }
    }



    public function deleteUser($id)
    {
        $data = $this->AdminService->deleteUser($id);
        if ($data == null) {
            return $this->response(__('message.user_deleted_successfully'), ['user' => null], 200);
        }
        return $this->response(__('message.user_not_found'), ['user' => null], 404);
    }
}
