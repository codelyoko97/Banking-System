<?php

namespace App\Http\Controllers;

use App\Services\AiBankService;
use Illuminate\Http\Request;

class AiController extends Controller
{
  public function __construct(protected AiBankService $ai) {}

  public function recommend(Request $request)
  {
    $validated = $request->validate([
      'query' => ['required', 'string', 'min:3'],
      'lang'  => ['nullable', 'string', 'in:ar,en,fr,es'],
    ]);

    $answer = $this->ai->recommendation(
      $validated['query'],
      $validated['lang'] ?? 'ar'
    );

    return response()->json([
      'answer' => $answer,
    ]);
  }
}
