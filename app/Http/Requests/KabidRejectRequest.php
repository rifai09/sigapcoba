<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KabidRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kabid_note' => ['required','string'],
        ];
    }
}
