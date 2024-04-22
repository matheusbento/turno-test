<?php

namespace App\Http\Requests;

use App\Models\UserDepositCheck;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserDepositRequest extends FormRequest
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
                'mimetypes:' . implode(',', UserDepositCheck::ACCEPTABLE_FILE_TYPES),
            ],
        ];
    }
}
