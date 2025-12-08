<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Status;
use App\Models\Type;

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
}
