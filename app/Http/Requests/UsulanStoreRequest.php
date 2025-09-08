<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsulanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan user login
        return auth()->check();
    }

    /**
     * Normalisasi angka sebelum validasi:
     * - hapus pemisah ribuan/karakter non-digit pada jumlah, persediaan_saat_ini, harga_perkiraan
     */
    protected function prepareForValidation(): void
    {
        $toInt = static function ($v) {
            if ($v === null) return null;
            $v = preg_replace('/[^\d]/', '', (string) $v);
            return $v === '' ? null : (int) $v;
        };

        $this->merge([
            'jumlah'               => $toInt($this->input('jumlah')),
            'persediaan_saat_ini'  => $toInt($this->input('persediaan_saat_ini')),
            'harga_perkiraan'      => $toInt($this->input('harga_perkiraan')),
        ]);
    }

    public function rules(): array
    {
        return [
            // Data pokok
            'nama_barang'         => ['required','string','max:255'],
            'spesifikasi'         => ['required','string','min:5','max:2000'],
            'alasan_pengusulan'   => ['required','string','min:10','max:3000'],
            'keterangan'          => ['nullable','string','max:2000'],

            // File (opsional)
            'gambar'              => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            // Angka & satuan
            'jumlah'              => ['required','integer','min:1'],
            'satuan'              => ['required','string','max:50'],
            'persediaan_saat_ini' => ['required','integer','min:0'],
            'harga_perkiraan'     => ['nullable','integer','min:0'],

            // Lokasi & unit
            'unit_id'             => ['required','integer','exists:units,id'],
            'lantai_id'           => ['required','integer','exists:locations,id'],
            'ruang_id'            => ['required','integer','exists:locations,id'],
            'sub_ruang_id'        => ['nullable','integer','exists:locations,id'], // boleh kosong
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_barang'         => 'nama barang/jasa',
            'spesifikasi'         => 'spesifikasi',
            'alasan_pengusulan'   => 'alasan pengusulan',
            'keterangan'          => 'keterangan',
            'gambar'              => 'gambar',
            'jumlah'              => 'jumlah',
            'satuan'              => 'satuan',
            'persediaan_saat_ini' => 'persediaan saat ini',
            'harga_perkiraan'     => 'harga perkiraan',
            'unit_id'             => 'unit pengusul',
            'lantai_id'           => 'lantai',
            'ruang_id'            => 'ruang',
            'sub_ruang_id'        => 'sub ruang',
        ];
    }

    public function messages(): array
    {
        return [
            'alasan_pengusulan.min' => 'Alasan pengusulan minimal :min karakter.',
            'gambar.max'            => 'Ukuran gambar maksimal 2MB.',
            'sub_ruang_id.exists'   => 'Sub ruang tidak valid.',
        ];
    }
}
