<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KepalaUnitRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catatan' => ['required','string','max:1000'],
        ];
    }
}
