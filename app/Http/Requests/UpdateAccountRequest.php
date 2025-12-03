<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class UpdateAccountRequest extends FormRequest {
    public function authorize(){ return true; }
    public function rules() {
        return [
            'name' => 'sometimes|string|max:255',
            'account_related_id' => 'nullable|exists:accounts,id'
        ];
    }
}
