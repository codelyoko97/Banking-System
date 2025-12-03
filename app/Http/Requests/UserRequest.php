<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:35',
            'email' => 'email',
            'password' => 'required|min:8|max:255',
            'phone' => 'string|min:10|max:10',
            'is_verified' => 'boolean|required',
            'role_id' => 'required'
        ];
    }
}
