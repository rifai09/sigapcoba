<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KatimkerRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'katimker_note' => ['required','string'],
        ];
    }
}
