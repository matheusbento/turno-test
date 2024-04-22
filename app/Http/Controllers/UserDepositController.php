<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserDepositRequest;
use App\Http\Resources\UserDepositResource;
use App\Models\Enums\UserDepositStatus;
use App\Models\UserDeposit;
use App\Models\UserDepositCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserDepositController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => [
                'integer',
                'gt:0',
                'lte:1000',
            ],
            'order_by' => [
                'string',
                Rule::in(['id']),
            ],
            'direction' => [
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'q' => [
                'nullable',
                'string',
            ],
        ]);

        $user = Auth::user();
        $builder = $user->deposits()->with($this->getRelationshipsToLoad())
            ->orderBy($request->input('order_by', 'created_at'), $request->input('direction', 'desc'));

        if ($search = $request->input('q')) {
            $builder->where('name', 'LIKE', "%{$search}%");
        }

        $deposits = $request->input('all') ? $builder->get() : $builder->paginate($request->input('per_page', intval(config('general.pagination_size'))))
            ->appends($request->only(['per_page', 'order_by', 'direction', 'q']));

        return UserDepositResource::collection($deposits);
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  StoreUserDepositRequest  $request
    * @return \Illuminate\Http\Response
    */
    public function store(StoreUserDepositRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $userDeposit = $this->updateUserDeposit(new UserDeposit(), $data);

        $userDeposit->setStatus(UserDepositStatus::PENDING);

        $file = $request->file('file');

        if (isset($file)) {
            $uploadedDocument = UserDepositCheck::upload($file, "user-deposits/{$user->id}/checks", UserDepositCheck::PUBLIC_DISK);
            $user = Auth::user();

            $file = UserDepositCheck::create(
                array_merge($uploadedDocument, [
                    'owner_type' => UserDeposit::class,
                    'owner_id' => $userDeposit->id,
                    'created_by_user_id' => $user->id,
                    'file_type' => UserDepositCheck::FILE_TYPE,
                ])
            );

            $userDeposit->file()->associate($file);
            $userDeposit->save();
        }
        return new UserDepositResource($userDeposit);
    }

    private function updateUserDeposit(UserDeposit $userDeposit, array $data)
    {
        $userDeposit->fill($data);
        $userDeposit->save();

        return $userDeposit;
    }

    /**
     * Display the specified resource.
     *
     * @param  Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(UserDeposit $userDeposit)
    {
        return new UserDepositResource($userDeposit);
    }

    /**
    * Display the specified resource.
    *
    * @param  Company  $company
    * @return \Illuminate\Http\Response
    */
    public function check(Request $request, UserDeposit $userDeposit)
    {
        return $userDeposit->file->download($userDeposit->file->path);
    }

    public function approve(UserDeposit $userDeposit)
    {
        DB::beginTransaction();
        $user = $userDeposit->user()->lockForUpdate()->first();
        try {
            $userDeposit->setStatus(UserDepositStatus::APPROVED);
            $user->balance += $userDeposit->amount;
            $user->save();
            DB::commit();
            return new UserDepositResource($userDeposit);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(UserDeposit $userDeposit)
    {
        $userDeposit->setStatus(UserDepositStatus::REJECTED);
        return new UserDepositResource($userDeposit);
    }

    private function getRelationshipsToLoad()
    {
        return [];
    }
}
