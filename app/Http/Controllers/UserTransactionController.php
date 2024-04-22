<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserTransactionResource;
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
        $builder = $user->transactions()->with($this->getRelationshipsToLoad())
            ->orderBy($request->input('order_by', 'created_at'), $request->input('direction', 'desc'));

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
