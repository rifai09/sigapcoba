@extends('adminlte.layouts.app')

@section('title', 'Persetujuan Usulan')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-3">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Usulan Barang/Jasa</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                        <div class=" alert alert-success">{{ session('success') }}</div>
                        @endif

                        <table id="usulan-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Unit</th>
                                    <th>Lantai</th>
                                    <th>Ruang</th>
                                    <th>Sub Ruang</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
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
                    <table class="table table-borderless full" >
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

    @push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function() {
            $('#usulan-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('persetujuan.index') }}",
                columns: [{
                        data: 'nama_barang',
                        name: 'nama_barang'
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'unit',
                        name: 'unit'
                    },
                    {
                        data: 'lantai',
                        name: 'lantai'
                    },
                    {
                        data: 'ruang',
                        name: 'ruang'
                    },
                    {
                        data: 'sub_ruang',
                        name: 'sub_ruang'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
          $(document).on('click', '.btn-detail', function () {
        $('#nama').text($(this).data('nama'));
        $('#spesifikasi').text($(this).data('spesifikasi'));
        $('#keterangan').text($(this).data('keterangan'));
        $('#jumlah-satuan').text($(this).data('jumlah') + ' ' + $(this).data('satuan'));
        $('#unit_pengusul').text($(this).data('unit'));
        $('#lantai').text($(this).data('lantai'));
        $('#ruang').text($(this).data('ruang'));
        $('#sub_ruang').text($(this).data('sub_ruang'));

        const status = $(this).data('status');
        $('#status').text(status);

        const badgeClass = status === 'Disetujui' ? 'badge-success' :
            status === 'Ditolak' ? 'badge-danger' : 'badge-warning';
        $('#status').attr('class', 'badge ' + badgeClass);

        // Tampilkan gambar jika ada
        const gambar = $(this).data('gambar');
        if (gambar) {
            const img = $('<img>', {
                src: "{{ asset('storage') }}/" + gambar,
                alt: 'Gambar Barang',
                class: 'img-fluid',
                css: { maxHeight: '200px' }
            });
            $('#gambar').html(img);
        } else {
            $('#gambar').html('Tidak ada gambar tersedia');
        }

        // Set action form persetujuan
        const id = $(this).data('id');
        $('#form-setujui').attr('action', '/persetujuan/' + id + '/setujui');
        $('#form-tolak').attr('action', '/persetujuan/' + id + '/tolak');
    });
    </script>
    @endpush
    <!-- Script -->
    <script>
      
    </script>
    @endsection