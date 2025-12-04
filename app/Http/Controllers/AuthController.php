<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterCitizenRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;
  public function __construct(AuthService $authService)
  {
    $this->authService = $authService;
  }

  public function register(RegisterCitizenRequest $req)
  {
    $data = $req->only(['name', 'email', 'phone', 'password']);
    $user = $this->authService->registerCitizen($data);
    return response()->json(['message' => 'Registered. OTP sent', 'user_id' => $user->id], 201);
  }

  public function verifyOtp(VerifyOtpRequest $req)
  {
    $user = User::find($req->user_id);
    if (!$user) {
      return response()->json(['message' => 'User not found'], 404);
    }

    if (!$this->authService->verifyOtp($user, $req->otp)) {
      return response()->json(['message' => 'Invalid or expired OTP'], 422);
    }
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
      'message' => 'Verified',
      'token'   => $token,
      'user'    => $user,
    ]);
  }

  public function resendOtp(Request $req)
  {
    $user = User::find($req->user_id);

    if (!$user) {
      return response()->json(['message' => 'User not found'], 404);
    }

    if ($user->is_verified) {
      return response()->json(['message' => 'Account already verified'], 400);
    }

    $this->authService->generateAndSendOtp($user);

    return response()->json(['message' => 'OTP resent']);
  }

  public function login(LoginRequest $req)
  {
    $res = $this->authService->attemptLogin($req->identifier, $req->password);
    if (!$res['success']) return response()->json(['message' => $res['message']], 401);

    $user = $res['user'];
    if (!$user->is_verified) return response()->json(['message' => 'Account not verified'], 403);

    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user]);
  }

  public function logout(Request $req)
  {
    $req->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
  }
}
