@php
  /** @var \App\Models\Usulan $row */
  /** @var string $role */
@endphp

<div class="btn-group" role="group">
  {{-- Detail --}}
  <button type="button"
          class="btn btn-sm btn-info btn-detail"
          data-id="{{ $row->id }}"
          data-nama="{{ $row->nama_barang }}"
          data-spesifikasi="{{ $row->spesifikasi }}"
          data-alasan="{{ $row->alasan_pengusulan }}"
          data-keterangan="{{ $row->keterangan }}"
          data-jumlah="{{ $row->jumlah }}"
          data-satuan="{{ $row->satuan }}"
          data-persediaan="{{ $row->persediaan_saat_ini }}"
          data-harga="{{ $row->harga_perkiraan }}"
          data-total="{{ $row->total_perkiraan }}"
          data-unit="{{ optional($row->unit)->nama }}"
          data-lantai="{{ optional($row->lantai)->nama }}"
          data-ruang="{{ optional($row->ruang)->nama }}"
          data-sub_ruang="{{ optional($row->subRuang)->nama }}"
          data-status="{{ $row->status }}"
          data-gambar="{{ $row->gambar }}"
          data-toggle="modal"
          data-target="#modalDetail">
    <i class="fas fa-search"></i> Detail
  </button>

  {{-- Riwayat --}}
  <button type="button"
          class="btn btn-sm btn-secondary btn-logs"
          data-id="{{ $row->id }}"
          data-toggle="modal"
          data-target="#modalLogs">
    <i class="fas fa-history"></i> Riwayat
  </button>
</div>
