<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KepalaUnitApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // prioritas boleh kosong saat approve (kalau hanya melanjutkan)
            'prioritas' => ['nullable','in:urgent,normal'],
            'catatan'   => ['nullable','string','max:1000'],
        ];
    }
}
