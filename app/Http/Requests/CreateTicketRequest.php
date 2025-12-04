<?php
// app/Http/Requests/CreateAccountRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
  public function authorize()
  {
    return true;
  }
  public function rules()
  {
    return [
      'title' => 'required|string|max:255',
      'message' => 'required|string',
    ];
  }
}
