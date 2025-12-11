<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;

class GeneralController extends Controller
{

  public function getAllAccountType()
  {
    $types = Type::all(['id', 'name']);
    return response()->json($types);
  }

  public function getAllStatuses()
  {
    $statuses = Status::all(['id', 'name']);
    return response()->json($statuses);
  }

  public function getAllRoles()
  {
    $roles = Role::all(['id', 'name']);
    return response()->json($roles);
  }

  public function getNotifications()
  {
    $user = Auth::user();
    return response()->json($user->notifications()->select('content', 'type', 'created_at')->latest()->get());
  }
}
