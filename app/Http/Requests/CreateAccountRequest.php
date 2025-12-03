<?php
// app/Http/Requests/CreateAccountRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccountRequest extends FormRequest {
    public function authorize() { return true; } 
    public function rules() {
        return [
            'customer_id' => 'required|exists:users,id',
            'type_id' => 'required|exists:types,id',
            // 'name' => 'nullable|string|max:255',
            'account_related_id' => 'nullable|exists:accounts,id',
            'balance' => 'nullable|numeric|min:0',
        ];
    }
}
