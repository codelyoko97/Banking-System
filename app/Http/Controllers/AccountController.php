<?php

namespace App\Http\Controllers;

use App\DTO\CreateAccountDTO;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Services\AccountService;

class AccountController extends Controller
{
  protected AccountService $service;
  public function __construct(AccountService $service)
  {
    $this->service = $service;
  }

  public function store(CreateAccountRequest $req)
  {
    // $acc = $this->service->create($req->validated());
    $data = $req->validated();

    $dto = CreateAccountDTO::fromArray($data);

    $acc = $this->service->create($dto->toArray());
    return response()->json($acc, 201);
  }

  public function update(UpdateAccountRequest $req, $id)
  {
    $acc = $this->service->update($id, $req->validated());
    return response()->json($acc);
  }

  public function close($id)
  {
    $acc = $this->service->close($id);
    return response()->json(['message' => 'closed', 'account' => $acc]);
  }

  // public function balance($id)
  // {
  //   $bal = $this->service->getBalanceComposite($id);
  //   return response()->json(['balance' => $bal]);
  // }


  public function fullBalance($id)
  {
    $balance = $this->service->getBalanceRecursive($id);
    return response()->json(['balance' => $balance]);
  }

  public function tree($id)
  {
    $tree = $this->service->getAccountTreeStructured($id);
    return response()->json($tree);
  }

  public function index()
  {
    $accounts = $this->service->listAccountsForUser(auth()->user());
    return response()->json($accounts);
  }
}
