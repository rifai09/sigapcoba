@extends('adminlte.layouts.app')

@section('title', 'Form Usulan Barang')

@section('content')
<div class="content-wrapper pt-4">
    <div class="container-fluid">
        <h4>Form Usulan Barang/Jasa</h4>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('usulan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="nama_barang">Nama Barang/Jasa</label>
                <input type="text" name="nama_barang" class="form-control" required>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="spesifikasi">Spesifikasi</label>
                    <textarea name="spesifikasi" class="form-control" rows="2" required></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label for="keterangan">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="gambar">Upload Gambar (opsional)</label>
                <input type="file" name="gambar" class="form-control-file" id="gambar" accept="image/*">
                <div class="mt-2">
                    <img id="preview-gambar" src="#" alt="Preview Gambar" style="display: none; max-height: 200px;">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="jumlah">Jumlah</label>
                    <input type="text" id="jumlah_display" class="form-control" required>
                    <input type="hidden" name="jumlah" id="jumlah">
                </div>
                <div class="form-group col-md-6">
                    <label for="satuan">Satuan</label>
                    <input type="text" name="satuan" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="unit_id">Unit Pengusul</label>
                    <select name="unit_id" id="unit_id" class="form-control select2" required>
                        <option value="">Pilih Unit</option>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr>
            <h5>Detail Penempatan</h5>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="lantai_id">Lantai</label>
                    <select name="lantai_id" id="lantai" class="form-control select2" required>
                        <option value="">Pilih Lantai</option>
                        @foreach($lantais as $lantai)
                        <option value="{{ $lantai->id }}">{{ $lantai->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="ruang_id">Ruang</label>
                    <select name="ruang_id" id="ruang" class="form-control select2" required>
                        <option value="">Pilih Ruang</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="sub_ruang_id">Sub Ruang</label>
                    <select name="sub_ruang_id" id="sub_ruang" class="form-control select2" required>
                        <option value="">Pilih Sub Ruang</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Usulan</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('gambar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('preview-gambar');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
        }
    });

    function formatAngka(num) {
        return num.toLocaleString('id-ID');
    }

    function unformatAngka(str) {
        return parseInt(str.replace(/[^\d]/g, '')) || 0;
    }

    function setupFormattedInput(displayId, hiddenId) {
        const displayInput = document.getElementById(displayId);
        const hiddenInput = document.getElementById(hiddenId);

        displayInput.addEventListener('input', function() {
            const rawValue = unformatAngka(displayInput.value);
            displayInput.value = formatAngka(rawValue);
            hiddenInput.value = rawValue;
        });
    }
    setupFormattedInput('jumlah_display', 'jumlah');

    // Cascading Dropdown Logic
    $(document).ready(function () {
        // Inisialisasi Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "Pilih atau ketik",
            allowClear: true
        });

        // Muat data lantai saat pertama kali
        $.get('/get-lantai', function(data) {
            $('#lantai').html('<option value="">Pilih Lantai</option>');
            data.forEach(function(item) {
                $('#lantai').append(`<option value="${item.id}">${item.nama}</option>`);
            });
        });

        // Saat lantai berubah, muat ruang
        $('#lantai').on('change', function () {
            let lantaiId = $(this).val();
            $('#ruang').html('<option>Memuat...</option>');
            $('#sub_ruang').html('<option value="">Pilih Sub Ruang</option>');
            $.get('/get-ruang/' + lantaiId, function (data) {
                $('#ruang').html('<option value="">Pilih Ruang</option>');
                data.forEach(function (item) {
                    $('#ruang').append(`<option value="${item.id}">${item.nama}</option>`);
                });
                $('#ruang').trigger('change');
            });
        });

        // Saat ruang berubah, muat sub ruang
        $('#ruang').on('change', function () {
            let ruangId = $(this).val();
            $('#sub_ruang').html('<option>Memuat...</option>');
            $.get('/get-subruang/' + ruangId, function (data) {
                $('#sub_ruang').html('<option value="">Pilih Sub Ruang</option>');
                data.forEach(function (item) {
                    $('#sub_ruang').append(`<option value="${item.id}">${item.nama}</option>`);
                });
                $('#sub_ruang').trigger('change');
            });
        });
    });
</script>
@endpush

<style>
    #preview-gambar {
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-height: 200px;
    }
</style>
@endsection