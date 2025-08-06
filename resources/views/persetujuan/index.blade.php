@extends('adminlte.layouts.app')

@section('title', 'Persetujuan Usulan')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-3">
        <h4>Daftar Usulan Barang/Jasa</h4>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Nama Barang/Jasa</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Unit Pengusul</th>
                    <th>Lantai</th>
                    <th>Ruang</th>
                    <th>Sub Ruang</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usulans as $usulan)
                <tr>
                    <td>{{ $usulan->nama_barang }}</td>
                    <td>{{ number_format($usulan->jumlah) }}</td>
                    <td>{{ $usulan->satuan }}</td>
                    <td>{{ $usulan->unit->nama ?? '-' }}</td>
                    <td>{{ $usulan->lantai->nama ?? '-' }}</td>
                    <td>{{ $usulan->ruang->nama ?? '-' }}</td>
                    <td>{{ $usulan->subRuang->nama ?? '-' }}</td>

                    <td>
                        <span class="badge 
                            @if($usulan->status == 'Disetujui') badge-success 
                            @elseif($usulan->status == 'Ditolak') badge-danger 
                            @else badge-warning @endif">
                            {{ $usulan->status }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info btn-detail"
                            data-toggle="modal"
                            data-target="#modalDetail"
                            data-id="{{ $usulan->id }}"
                            data-nama="{{ $usulan->nama_barang }}"
                            data-spesifikasi="{{ $usulan->spesifikasi }}"
                            data-keterangan="{{ $usulan->keterangan }}"
                            data-jumlah="{{ $usulan->jumlah }}"
                            data-satuan="{{ $usulan->satuan }}"
                            data-unit="{{ $usulan->unit->nama ?? '-' }}"
                            data-lantai="{{ $usulan->lantai->nama ?? '-' }}"
                            data-ruang="{{ $usulan->ruang->nama ?? '-' }}"
                            data-sub_ruang="{{ $usulan->subRuang->nama ?? '-' }}"
                            data-status="{{ $usulan->status }}"
                            data-gambar="{{ $usulan->gambar }}">
                            Detail
                        </button>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="detailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailLabel">Detail Usulan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Nama Barang</th>
                        <td id="nama"></td>
                    </tr>
                    <tr>
                        <th>Spesifikasi</th>
                        <td id="spesifikasi"></td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td id="keterangan"></td>
                    </tr>
                    <tr>
                        <th>Jumlah</th>
                        <td id="jumlah-satuan"></td>
                    </tr>
                    <tr>
                        <th>Unit Pengusul</th>
                        <td id="unit_pengusul"></td>
                    </tr>
                    <tr>
                        <th>Lantai</th>
                        <td id="lantai"></td>
                    </tr>
                    <tr>
                        <th>Ruang</th>
                        <td id="ruang"></td>
                    </tr>
                    <tr>
                        <th>Sub Ruang</th>
                        <td id="sub_ruang"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><strong id="status"></strong></td>
                    </tr>
                    <tr>
                        <th>Gambar</th>
                        <td id="gambar"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <form id="form-setujui" method="POST" style="display:inline-block">
                    @csrf
                    <button type="submit" class="btn btn-success">Setujui</button>
                </form>
                <form id="form-tolak" method="POST" style="display:inline-block">
                    @csrf
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    document.querySelectorAll('.btn-detail').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('nama').textContent = this.dataset.nama;
            document.getElementById('spesifikasi').textContent = this.dataset.spesifikasi;
            document.getElementById('keterangan').textContent = this.dataset.keterangan;
            document.getElementById('jumlah-satuan').textContent = this.dataset.jumlah + ' ' + this.dataset.satuan;
            document.getElementById('unit_pengusul').textContent = this.dataset.unit;
            document.getElementById('lantai').textContent = this.dataset.lantai;
            document.getElementById('ruang').textContent = this.dataset.ruang;
            document.getElementById('sub_ruang').textContent = this.dataset.sub_ruang;
            document.getElementById('status').textContent = this.dataset.status;

            const badgeClass = this.dataset.status === 'Disetujui' ? 'badge-success' :
                this.dataset.status === 'Ditolak' ? 'badge-danger' : 'badge-warning';
            document.getElementById('status').className = 'badge ' + badgeClass;

            if (this.dataset.gambar) {
                const img = document.createElement('img');
                img.src = "{{ asset('storage') }}/" + this.dataset.gambar;
                img.alt = 'Gambar Barang';
                img.className = 'img-fluid';
                img.style.maxHeight = '200px';
                document.getElementById('gambar').innerHTML = '';
                document.getElementById('gambar').appendChild(img);
            } else {
                document.getElementById('gambar').innerHTML = 'Tidak ada gambar tersedia';
            }

            document.getElementById('form-setujui').action = "/persetujuan/" + this.dataset.id + "/setujui";
            document.getElementById('form-tolak').action = "/persetujuan/" + this.dataset.id + "/tolak";
        });
    });
</script>
@endsection