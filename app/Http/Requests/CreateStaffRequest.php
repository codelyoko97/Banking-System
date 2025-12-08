<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-staff');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'role_id' => 'required|exists:roles,id|not_in:6', 
        ];
    }
}
