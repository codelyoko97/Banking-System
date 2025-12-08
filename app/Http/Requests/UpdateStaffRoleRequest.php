<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-staff');
    }

    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id|not_in:6',
        ];
    }
}
