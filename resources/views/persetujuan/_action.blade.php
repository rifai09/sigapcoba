<button class="btn btn-sm btn-info btn-detail"
    data-toggle="modal"
    data-target="#modalDetail"
    data-id="{{ $row->id }}"
    data-nama="{{ $row->nama_barang }}"
    data-spesifikasi="{{ $row->spesifikasi }}"
    data-keterangan="{{ $row->keterangan }}"
    data-jumlah="{{ $row->jumlah }}"
    data-satuan="{{ $row->satuan }}"
    data-unit="{{ $row->unit->nama ?? '-' }}"
    data-lantai="{{ $row->lantai->nama ?? '-' }}"
    data-ruang="{{ $row->ruang->nama ?? '-' }}"
    data-sub_ruang="{{ $row->subRuang->nama ?? '-' }}"
    data-status="{{ $row->status }}"
    data-gambar="{{ $row->gambar }}">
    Detail
</button>