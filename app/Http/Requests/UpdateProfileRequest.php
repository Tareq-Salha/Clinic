<?php

namespace App\Http\Requests;

use App\Http\Responses\Response;
use \Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validate = $this->input('validate');

        if ($validate === '1') {
            return [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . Auth::id(),
                'phone' => 'required|unique:users,phone,' . Auth::id() . '|regex:/^\+963\d{9}$/',
            ];
        } elseif ($validate === '0') {
            return [
                'password' => 'required|confirmed|min:8',
            ];
        } else {
            return [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'sometimes|email|unique:users,email,' . Auth::id(),
                'phone' => 'required|unique:users,phone,' . Auth::id() . '|regex:/^\+963\d{9}$/',
                'password' => 'sometimes|confirmed|min:8',
            ];
        }
    }
    protected function failedValidation(Validator $validator)
    {
        // throw a validationException with the translated error messages
        throw new ValidationException($validator, Response::Validation([], $validator->errors()));
    }
}
