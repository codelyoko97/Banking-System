<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return auth()->check();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $action = $this->route()->getActionMethod();

    switch ($action) {
      case 'transaction':
        return [
          'account_id' => 'required',
          'amount' => 'required',
          'type' => 'required',
          'account_related_id' => 'sometimes',
          'employee_name' => 'sometimes',
          'description' => 'sometimes',
        ];
      default:
        return [];
    }
  }
}
