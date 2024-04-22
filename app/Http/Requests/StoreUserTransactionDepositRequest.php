<?php

namespace App\Http\Requests;

use App\Models\UserTransactionCheck;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserTransactionDepositRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'file' => [
                'required',
                'file',
                'mimetypes:' . implode(',', UserTransactionCheck::ACCEPTABLE_FILE_TYPES),
            ],
        ];
    }
}
