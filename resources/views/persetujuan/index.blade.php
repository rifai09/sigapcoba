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

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="detailLabel">Detail Usulan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <table class="table table-borderless full">
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

                <!-- Modal Footer -->
                <div class="modal-footer">

                    {{-- Form SETUJUI --}}
                    <form id="form-setujui" method="POST" action="" style="display:inline-block; width: 100%;">
                        @csrf

                        <!-- Tombol awal -->
                        <button type="submit" class="btn btn-success" id="btn-setujui-awal">Setujui</button>

                        <!-- Form lanjutan setelah klik "Setujui" -->
                        <div id="form-lanjutan" style="display: none; margin-top: 1rem;">
                            <div class="form-group">
                                <label for="keterangan_setujui">Keterangan</label>
                                <textarea name="keterangan" id="keterangan_setujui" class="form-control" rows="3" required></textarea>
                                <input type="hidden" id="usulan_id" name="usulan_id">
                            </div>
                            <div class="form-group">
                                <label for="urgensi">Status Urgensi</label>
                                <select name="urgensi" id="urgensi" class="form-control" required>
                                    <option value="">-- Pilih Urgensi --</option>
                                    <option value="urgen">Urgent</option>
                                    <option value="not_urgent">Not Urgent</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Persetujuan</button>
                        </div>
                    </form>

                    {{-- Form TOLAK --}}
                    <form id="form-tolak" method="POST" action="" style="display:inline-block">
                        @csrf
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </form>

                    <!-- Tombol Tutup -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Toggle Form -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnSetujuiAwal = document.getElementById('btn-setujui-awal');
            const formLanjutan = document.getElementById('form-lanjutan');
            const formTolak = document.getElementById('form-tolak');

            btnSetujuiAwal.addEventListener('click', function(e) {
                e.preventDefault();

                // Sembunyikan tombol awal
                btnSetujuiAwal.style.display = 'none';
                formTolak.style.display = 'none';

                // Tampilkan form lanjutan
                formLanjutan.style.display = 'block';
            });
        });
    </script>


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
        $(document).on('click', '.btn-detail', function() {
             $('#usulan_id').val($(this).data('id'));
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
                    css: {
                        maxHeight: '200px'
                    }
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
          $(document).ready(function () {
        $('#btn-setujui-awal').on('click', function () {
            $(this).hide(); // Sembunyikan tombol awal
            $('#form-lanjutan').slideDown(); // Tampilkan form lanjutan
        });

        $('#form-setujui').on('submit', function (e) {
            e.preventDefault();

            let formData = {
                _token: '{{ csrf_token() }}',
                usulan_id: $('#usulan_id').val(),
                keterangan: $('#keterangan_setujui').val(),
                urgensi: $('#urgensi').val()
            };

            $.ajax({
                type: 'POST',
                url: '{{ route("usulan.setujui") }}',
                data: formData,
                success: function (response) {
                    alert(response.message);
                    location.reload(); // Atau redirect kalau perlu
                },
                error: function (xhr) {
                    alert("Terjadi kesalahan: " + xhr.responseJSON.message);
                }
            });
        });
    });
    </script>
    @endpush
    <!-- Script -->
    <script>

    </script>
    @endsection