<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KabidApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kabid_priority'  => ['required','in:urgent,normal'],
            'harga_perkiraan' => ['nullable','integer','min:0'],
            'catatan'         => ['nullable','string','max:1000'],
        ];
    }
}
