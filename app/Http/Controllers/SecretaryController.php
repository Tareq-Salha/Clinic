<?php

namespace App\Http\Controllers;

use App\ResponseJson;
use App\Services\SecretaryService;
use Illuminate\Http\Request;

class SecretaryController extends Controller
{
    use ResponseJson;
    private $SecretaryServece;
    public function __construct(SecretaryService $SecretaryServece)
    {
        $this->SecretaryServece = $SecretaryServece;
    }
    public function reverse()
    {
        $data = $this->SecretaryServece->reverse();
        return match ($data['status']) {
            201 => $this->response(__('messages.appointment_added_successfully'), ['apointment' => $data['data']], 201),
            400 => $this->response(__('messages.validation_failed'), $data['errors'], 400),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function reverseUnApp()
    {
        $data = $this->SecretaryServece->reverseUnApp();
        return match ($data['status']) {
            201 => $this->response(__('messages.appointment_added_successfully'), ['apointment' => $data['data']], 201),
            400 => $this->response(__('messages.validation_failed'), $data['errors'], 400),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function acceptReverse($id)
    {
        $data = $this->SecretaryServece->acceptReverse($id);
        return match ($data['status']) {
            200 => $this->response($data['message'], ['appointment' => $data['data']], 200),
            400 => $this->response($data['message'], null, 400),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function rejectReverse($id)
    {
        $data = $this->SecretaryServece->deleteReverse($id);
        return match ($data['status']) {
            200 => $this->response($data['message'], ['appointment' => null], 200),
            400 => $this->response($data['message'], null, 400),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function appointments($doctor_id, $appointment_date)
    {
        $data = $this->SecretaryServece->appointments($doctor_id, $appointment_date);

        return match ($data['status']) {
            200 => $this->response($data['message'], $data['data'], 200),
            400 => $this->response($data['errors'], null, 400),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }

    public function search()
    {

        $data = $this->SecretaryServece->search();
        return match ($data['status']) {
            200 => $this->response(__('messages.found_successfully'), ['results' => $data['data']], 200),
            404 => $this->response(__('messages.no_results_found'), null, 404),
            422 => $this->response(__('messages.validation_failed'), null, 422),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function apointments()
    {
        $data = $this->SecretaryServece->apointments();
        return match ($data['status']) {
            200 => $this->response($data['message'], $data['data'], 200),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520)
        };
    }
    public function monthlyLeaves()
    {
        $data = $this->SecretaryServece->monthlyLeaves();
        return match ($data['status']) {
            200 => $this->response($data['message'], $data['data'], 200),
            400 => $this->response($data['message'], null, 400),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520)
        };
    }

    public function addMounthlyLeaves()
    {
        $data = $this->SecretaryServece->addMonthlyLeaves();
        return match ($data['status']) {
            201 => $this->response(
                $data['message'],
                ['leaves' => $data['data']],
                201
            ),
            207 => $this->response(//Multi status
                $data['message'],
                [
                    'leaves' => $data['data'],
                    'errors' => $data['errors']
                ],
                207
            ),
            400 => $this->response(
                __('messages.validation_failed'),
                ['errors' => $data['errors']],
                400
            ),
            404 => $this->response(
                $data['message'],
                ['errors' => $data['errors'] ?? null],
                404
            ),
            409 => $this->response(
                $data['message'],
                ['errors' => $data['errors'] ?? null],
                409
            ),
            500 => $this->response(
                __('messages.server_error', ['error' => $data['errors'] ?? $data['message'] ?? '']),
                null,
                500
            ),
            default => $this->response(
                __('messages.unknown_error'),
                null,
                520
            ),
        };
    }
    public function removeMonthlyleaves()
    {
        $data = $this->SecretaryServece->removeMonthlyleaves();
        return match ($data['status']) {
            200 => $this->response(__('messages.all_monthly_leaves'), null, 200),
            500 => $this->response($data['error'], null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };


    }
    public function relaseRate()
    {
        $data = $this->SecretaryServece->relaseRate();
        return match ($data['status']) {
            200 => $this->response($data['message'], null, 200),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response($data['error'], null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function enterPatient($id)
    {
        $data = $this->SecretaryServece->enterPatient($id);
        return match ($data['status']) {
            200 => $this->response($data['message'], null, 200),
            404 => $this->response($data['message'], null, 404),
            400 => $this->response($data['message'], null, 400),
            500 => $this->response($data['error'], null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function secretaryInfo($dayId)
    {
        $data = $this->SecretaryServece->secretaryInfo($dayId);
        return match ($data['status']) {
            200 => $this->response($data['message'], $data['data'], 200),
            400 => $this->response($data['message'], null, 400),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
    public function getDoctors()
    {
        $data = $this->SecretaryServece->getDoctors();

        return match ($data['status']) {
            200 => $this->response($data['message'], ['doctors' => $data['data']], 200),
            404 => $this->response($data['message'], null, 404),
            500 => $this->response(__('messages.server_error', ['error' => $data['error'] ?? '']), null, 500),
            default => $this->response(__('messages.unknown_error'), null, 520),
        };
    }
}
