@extends('adminlte.layouts.app')

@section('title', 'Form Usulan Barang')

@section('content')
<div class="content-wrapper pt-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Form Usulan Barang/Jasa</h4>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('usulan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <!-- Unit Pengusul -->
                            <div class="form-group">
                                <label for="unit_pengusul">Unit Pengusul</label>
                                <select name="unit_id" id="unit_pengusul" class="form-control select2 @error('unit_pengusul') is-invalid @enderror" required>
                                    <option value="">Pilih Unit</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_pengusul') == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>
                                    @endforeach
                                </select>
                                @error('unit_pengusul')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <hr>
                            <!-- Nama Barang/Jasa -->
                            <div class="form-group">
                                <label for="nama_barang">Nama Barang/Jasa</label>
                                <input type="text" name="nama_barang" class="form-control @error('nama_barang') is-invalid @enderror" value="{{ old('nama_barang') }}" required>
                                @error('nama_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Spesifikasi dan Keterangan -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="spesifikasi">Spesifikasi</label>
                                    <textarea name="spesifikasi" class="form-control @error('spesifikasi') is-invalid @enderror" rows="2" required>{{ old('spesifikasi') }}</textarea>
                                    @error('spesifikasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="keterangan">Keterangan Tambahan</label>
                                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2">{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Upload Gambar -->
                            <div class="form-group">
                                <label for="gambar">Upload Gambar (opsional)</label>
                                <input type="file" name="gambar" class="form-control-file @error('gambar') is-invalid @enderror" id="gambar" accept="image/*">
                                @error('gambar')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <img id="preview-gambar" src="#" alt="Preview Gambar" style="display: none; max-height: 200px;">
                                </div>
                            </div>

                            <!-- Jumlah dan Satuan -->
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="jumlah">Jumlah</label>
                                    <input type="text" id="jumlah_display" class="form-control @error('jumlah') is-invalid @enderror" onkeyup="formatHarga()" value="{{ old('jumlah') }}" required>
                                    <input type="hidden" name="jumlah" id="jumlah" value="{{ old('jumlah') }}">
                                    @error('jumlah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="harga_perkiraan">Harga Perkiraan (Rp)</label>
                                    <input type="text" id="harga_perkiraan_display" class="form-control @error('harga_perkiraan') is-invalid @enderror" onkeyup="formatHarga()" value="{{ old('harga_perkiraan') }}" required>
                                    <input type="hidden" name="harga_perkiraan" id="harga_perkiraan" value="{{ old('harga_perkiraan') }}">
                                    @error('harga_perkiraan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="satuan">Satuan</label>
                                    <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan') }}" required>
                                    @error('satuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <p id="total_perkiraan" class="font-weight-bold">Total Perkiraan: Rp 0</p>
                            </div>
                            <hr>
                            <h5>Detail Penempatan</h5>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="lantai_id">Lantai</label>
                                    <select name="lantai_id" id="lantai" class="form-control select2 @error('lantai_id') is-invalid @enderror" required>
                                        <option value="">Pilih Lantai</option>
                                        @foreach($lantais as $lantai)
                                        <option value="{{ $lantai->id }}" {{ old('lantai_id') == $lantai->id ? 'selected' : '' }}>{{ $lantai->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('lantai_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="ruang_id">Ruang</label>
                                    <select name="ruang_id" id="ruang" class="form-control select2 @error('ruang_id') is-invalid @enderror" required>
                                        <option value="">Pilih Ruang</option>
                                    </select>
                                    @error('ruang_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="sub_ruang_id">Sub Ruang</label>
                                    <select name="sub_ruang_id" id="sub_ruang" class="form-control select2 @error('sub_ruang_id') is-invalid @enderror" required>
                                        <option value="">Pilih Sub Ruang</option>
                                    </select>
                                    @error('sub_ruang_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Usulan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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

    function formatHarga() {
        const displayInput = document.getElementById('harga_perkiraan_display');
        const hiddenInput = document.getElementById('harga_perkiraan');

        let rawValue = displayInput.value.replace(/[^\d]/g, '');
        rawValue = parseInt(rawValue) || 0;

        displayInput.value = rawValue.toLocaleString('id-ID');
        hiddenInput.value = rawValue;
        hitungTotal();
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
    // ✅ Inisialisasi formatting untuk kolom jumlah dan harga perkiraan
    setupFormattedInput('jumlah_display', 'jumlah');

    function hitungTotal() {
        const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
        const harga = parseInt(document.getElementById('harga_perkiraan').value) || 0;
        const total = jumlah * harga;

        console.log('Total:', total); // Debug
        document.getElementById('total_perkiraan').textContent = 'Total Perkiraan: Rp ' + total.toLocaleString('id-ID');
    }


    setupFormattedInput('jumlah_display', 'jumlah', hitungTotal);
    setupFormattedInput('perkiraan_harga_display', 'perkiraan_harga', hitungTotal);


    // Muat data lantai saat pertama kali
    $.get('/get-lantai', function(data) {
        $('#lantai').html('<option value="">Pilih Lantai</option>');
        data.forEach(function(item) {
            $('#lantai').append(`<option value="${item.id}">${item.nama}</option>`);
        });
    });

    // Saat lantai berubah, muat ruang
    $('#lantai').on('change', function() {
        let lantaiId = $(this).val();
        $('#ruang').html('<option>Memuat...</option>');
        $('#sub_ruang').html('<option value="">Pilih Sub Ruang</option>');
        $.get('/get-ruang/' + lantaiId, function(data) {
            $('#ruang').html('<option value="">Pilih Ruang</option>');
            data.forEach(function(item) {
                $('#ruang').append(`<option value="${item.id}">${item.nama}</option>`);
            });
            $('#ruang').trigger('change');
        });
    });

    // Saat ruang berubah, muat sub ruang
    $('#ruang').on('change', function() {
        let ruangId = $(this).val();
        $('#sub_ruang').html('<option>Memuat...</option>');
        $.get('/get-subruang/' + ruangId, function(data) {
            $('#sub_ruang').html('<option value="">Pilih Sub Ruang</option>');
            data.forEach(function(item) {
                $('#sub_ruang').append(`<option value="${item.id}">${item.nama}</option>`);
            });
            $('#sub_ruang').trigger('change');
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