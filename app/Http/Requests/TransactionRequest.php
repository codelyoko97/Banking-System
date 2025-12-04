<?php

namespace App\Http\Requests;

use App\DTO\ProcessTransactionDTO;
use App\Models\User;
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

      case 'store':
        return [
          'account_id'         => 'required',
          'type'               => 'required|in:deposit,withdraw,transfer',
          'amount'             => 'required|numeric|min:0.01',
          'account_related_id' => 'nullable',
          'frequency'          => 'required|in:daily,weekly,monthly',
          'next_run'           => 'required|date',
          'start_date'         => 'nullable|date',
          'end_date'           => 'nullable|date|after_or_equal:start_date',
          'day_of_month'       => 'nullable|integer|min:1|max:28',
        ];

      default:
        return [];
    }
  }

  public function toDTO(User $user): ProcessTransactionDTO
  {
    return new ProcessTransactionDTO(
      account_id: (int) $this->input('account_id'),
      amount: (float) $this->input('amount'),
      type: $this->input('type'),
      account_related_id: $this->input('account_related_id'),
      description: $this->input('description'),
      employee_name: $this->input('employee_name'),
      requestedBy: $user,
    );
  }
}
