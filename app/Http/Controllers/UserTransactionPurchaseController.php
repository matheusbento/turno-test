<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserTransactionPurchaseRequest;
use App\Http\Resources\UserTransactionResource;
use App\Models\Enums\UserTransactionOperationType;
use App\Models\Enums\UserTransactionType;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserTransactionPurchaseController extends Controller
{
    /**
    * Store a newly created resource in storage.
    *
    * @param  StoreUserTransactionPurchaseRequest  $request
    * @return \Illuminate\Http\Response
    */
    public function store(StoreUserTransactionPurchaseRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        abort_if($user->balance < $data['amount'], 400, 'Insufficient balance');

        $data['user_id'] = $user->id;
        $data['operation'] = UserTransactionOperationType::DEBIT;
        $data['type'] = UserTransactionType::PURCHASE;
        DB::beginTransaction();
        User::where('id', $user->id)->lockForUpdate()->first();
        try {
            $userTransaction = $this->updateUserPurchase(new UserTransaction(), $data);
            $user->balance -= $data['amount'];
            $user->save();
            DB::commit();
            return new UserTransactionResource($userTransaction);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateUserPurchase(UserTransaction $userTransaction, array $data)
    {
        $userTransaction->fill($data);
        $userTransaction->save();

        return $userTransaction;
    }

    /**
     * Display the specified resource.
     *
     * @param  Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(UserTransaction $userTransaction)
    {
        return new UserTransactionResource($userTransaction);
    }

    private function getRelationshipsToLoad()
    {
        return [];
    }
}
