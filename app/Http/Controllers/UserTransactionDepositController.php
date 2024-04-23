<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserTransactionDepositRequest;
use App\Http\Resources\UserTransactionResource;
use App\Models\Enums\UserTransactionOperationType;
use App\Models\Enums\UserTransactionStatus;
use App\Models\Enums\UserTransactionType;
use App\Models\UserTransaction;
use App\Models\UserTransactionCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserTransactionDepositController extends Controller
{
    /**
    * Store a newly created resource in storage.
    *
    * @param  StoreUserTransactionDepositRequest  $request
    * @return \Illuminate\Http\Response
    */
    public function store(StoreUserTransactionDepositRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['operation'] = UserTransactionOperationType::CREDIT;
        $data['type'] = UserTransactionType::DEPOSIT;
        $userTransaction = $this->updateUserTransaction(new UserTransaction(), $data);

        $userTransaction->setStatus(UserTransactionStatus::PENDING);

        $file = $request->file('file');

        if (isset($file)) {
            $uploadedDocument = UserTransactionCheck::upload($file, "user-transactions/{$user->id}/checks", UserTransactionCheck::PUBLIC_DISK);
            $user = Auth::user();

            $file = UserTransactionCheck::create(
                array_merge($uploadedDocument, [
                    'owner_type' => UserTransaction::class,
                    'owner_id' => $userTransaction->id,
                    'created_by_user_id' => $user->id,
                    'file_type' => UserTransactionCheck::FILE_TYPE,
                ])
            );

            $userTransaction->file()->associate($file);
            $userTransaction->save();
        }
        return new UserTransactionResource($userTransaction);
    }

    private function updateUserTransaction(UserTransaction $userTransaction, array $data)
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

    /**
    * Display the specified resource.
    *
    * @param  Company  $company
    * @return \Illuminate\Http\Response
    */
    public function check(Request $request, UserTransaction $userTransaction)
    {
        if(!$userTransaction->file) {
            return response()->json(['error' => 'No file uploaded'], 404);
        }
        $file = $userTransaction->file->read($userTransaction->file->path);

        return [
            'data' => base64_encode($file),
        ];
    }

    public function approve(UserTransaction $userTransaction)
    {
        DB::beginTransaction();
        $user = $userTransaction->user()->lockForUpdate()->first();
        try {
            $userTransaction->setStatus(UserTransactionStatus::APPROVED);
            $user->balance += $userTransaction->amount;
            $user->save();
            DB::commit();
            return new UserTransactionResource($userTransaction);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(UserTransaction $userTransaction)
    {
        $userTransaction->setStatus(UserTransactionStatus::REJECTED);
        return new UserTransactionResource($userTransaction);
    }

    private function getRelationshipsToLoad()
    {
        return [];
    }
}
