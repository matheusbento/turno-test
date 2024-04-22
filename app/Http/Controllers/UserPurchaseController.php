<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserPurchaseRequest;
use App\Http\Resources\UserPurchaseResource;
use App\Models\User;
use App\Models\UserPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserPurchaseController extends Controller
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
        $builder = $user->purchases()->with($this->getRelationshipsToLoad())
            ->orderBy($request->input('order_by', 'created_at'), $request->input('direction', 'desc'));

        if ($search = $request->input('q')) {
            $builder->where('name', 'LIKE', "%{$search}%");
        }

        $deposits = $request->input('all') ? $builder->get() : $builder->paginate($request->input('per_page', intval(config('general.pagination_size'))))
            ->appends($request->only(['per_page', 'order_by', 'direction', 'q']));

        return UserPurchaseResource::collection($deposits);
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  StoreUserPurchaseRequest  $request
    * @return \Illuminate\Http\Response
    */
    public function store(StoreUserPurchaseRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        abort_if($user->balance < $data['amount'], 400, 'Insufficient balance');

        $data['user_id'] = $user->id;
        DB::beginTransaction();
        User::where('id', $user->id)->lockForUpdate()->first();
        try {
            $userPurchase = $this->updateUserPurchase(new UserPurchase(), $data);
            $user->balance -= $data['amount'];
            $user->save();
            DB::commit();
            return new UserPurchaseResource($userPurchase);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateUserPurchase(UserPurchase $userPurchase, array $data)
    {
        $userPurchase->fill($data);
        $userPurchase->save();

        return $userPurchase;
    }

    /**
     * Display the specified resource.
     *
     * @param  Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(UserPurchase $userPurchase)
    {
        return new UserPurchaseResource($userPurchase);
    }

    private function getRelationshipsToLoad()
    {
        return [];
    }
}
