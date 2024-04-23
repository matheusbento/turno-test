<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserTransactionResource;
use App\Models\Enums\UserTransactionStatus;
use App\Models\Enums\UserTransactionType;
use App\Models\Enums\UserType;
use App\Models\UserTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserTransactionController extends Controller
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

        if($user->type === UserType::ADMIN) {
            $builder = UserTransaction::where('type', UserTransactionType::DEPOSIT)->whereCurrentStatus(UserTransactionStatus::PENDING)->with($this->getRelationshipsToLoad());
        } else {
            $builder = $user->transactions()->with($this->getRelationshipsToLoad());
        }

        $builder->orderBy($request->input('order_by', 'created_at'), $request->input('direction', 'desc'));

        if ($search = $request->input('q')) {
            $builder->where('name', 'LIKE', "%{$search}%");
        }

        $transactions = $request->input('all') ? $builder->get() : $builder->paginate($request->input('per_page', intval(config('general.pagination_size'))))
            ->appends($request->only(['per_page', 'order_by', 'direction', 'q']));

        return UserTransactionResource::collection($transactions);
    }

    private function getRelationshipsToLoad()
    {
        return [];
    }
}
