<?php

namespace App\Http\Controllers;

use App\DTO\Dashboard\StaffDTO;
use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRoleRequest;
use App\Services\StaffService;

class StaffController extends Controller
{
  public function __construct(private StaffService $service) {}

  public function index()
  {
    $this->authorize('view-staff');
    return response()->json($this->service->listStaff());
  }

  public function store(CreateStaffRequest $request)
  {
    $dto = StaffDTO::fromRequest($request);
    return response()->json($this->service->createStaff($dto), 201);
  }

  public function updateRole(UpdateStaffRoleRequest $request, $id)
  {
    return response()->json(
      $this->service->updateRole($id, $request->role_id)
    );
  }

  public function employees()
  {
    return response()->json($this->service->getAllEmployees());
  }

  public function destroy($id)
  {
    $this->authorize('manage-staff');
    $this->service->deleteStaff($id);

    return response()->json(['message' => 'staff removed']);
  }
}
