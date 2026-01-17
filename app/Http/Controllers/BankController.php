<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;

class BankController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::where('status', '!=', 2)
            ->with('bank')
            ->get();

        $banks = Bank::all();

        return view('panel.bank-accounts.index', [
            'banks' => $banks,
            'accounts' => $accounts,
        ]);
    }

    public function store(BankRequest $request): JsonResponse
    {
        $account = new BankAccount();

        $account->name = $request->name;
        $account->bank_id = $request->bank_id;
        $account->number = $request->number;
        $account->identification = $request->identification;
        $account->method = (int) $request->bank_id === 1 ? BankAccount::ZELLE : BankAccount::NACIONAL;
        $account->type = $request->type;
        $account->email = $request->email;
        $account->phone = $request->phone;
        $account->status = 1;

        $account->save();

        return response()->json([
            'result' => true,
            'account' => $account->load('bank'),
        ]);
    }

    public function update(BankRequest $request, int $id): JsonResponse
    {
        $account = BankAccount::findOrFail($id);

        $account->name = $request->name;
        $account->bank_id = $request->bank_id;
        $account->method = (int) $request->bank_id === 1 ? BankAccount::ZELLE : BankAccount::NACIONAL;
        $account->identification = $request->identification;
        $account->number = $request->number;
        $account->type = $request->type;
        $account->email = $request->email;
        $account->phone = $request->phone;

        $account->save();

        return response()->json([
            'result' => true,
            'account' => $account->load('bank'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $account = BankAccount::findOrFail($id);
        $account->delete();

        return response()->json(['result' => true]);
    }

    public function status(int $id): JsonResponse
    {
        $account = BankAccount::findOrFail($id);
        $account->status = $account->status ? 0 : 1;
        $account->save();

        return response()->json([
            'result' => true,
            'status' => $account->status,
        ]);
    }

    /**
     * Compatibilidad con código legado.
     */
    public function estatus(int $id): JsonResponse
    {
        return $this->status($id);
    }
}
