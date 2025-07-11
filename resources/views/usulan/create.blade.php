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
                <div class="form-group col-md-6">
                    <label for="harga_pagu">Harga Pagu + PPN (Rp)</label>
                    <input type="text" id="harga_pagu_display" class="form-control" required>
                    <input type="hidden" name="harga_pagu" id="harga_pagu">
                    <small id="total_pagu" class="form-text text-muted mt-1"></small>
                </div>
                <div class="form-group col-md-6">
                    <label for="perkiraan_harga">Harga Perkiraan (Rp)</label>
                    <input type="text" id="perkiraan_harga_display" class="form-control" required>
                    <input type="hidden" name="perkiraan_harga" id="perkiraan_harga">
                    <small id="total_perkiraan" class="form-text text-muted mt-1"></small>
                </div>
            </div>
            <hr>
            <h5>Harga Pembanding</h5>

            <div class="form-row">
                <!-- Penjual 1 -->
                <div class="form-group col-md-4">
                    <label>Nama Penjual 1</label>
                    <input type="text" name="penjual_1" class="form-control">
                    <label>Harga Penjual 1 (Rp)</label>
                    <input type="text" id="harga_penjual_1_display" class="form-control">
                    <input type="hidden" name="harga_penjual_1" id="harga_penjual_1">
                    <label>Link e-Katalog 1</label>
                    <input type="url" name="link_penjual_1" class="form-control">
                </div>

                <!-- Penjual 2 -->
                <div class="form-group col-md-4">
                    <label>Nama Penjual 2</label>
                    <input type="text" name="penjual_2" class="form-control">
                    <label>Harga Penjual 2 (Rp)</label>
                    <input type="text" id="harga_penjual_2_display" class="form-control">
                    <input type="hidden" name="harga_penjual_2" id="harga_penjual_2">
                    <label>Link e-Katalog 2</label>
                    <input type="url" name="link_penjual_2" class="form-control">
                </div>

                <!-- Penjual 3 -->
                <div class="form-group col-md-4">
                    <label>Nama Penjual 3</label>
                    <input type="text" name="penjual_3" class="form-control">
                    <label>Harga Penjual 3 (Rp)</label>
                    <input type="text" id="harga_penjual_3_display" class="form-control">
                    <input type="hidden" name="harga_penjual_3" id="harga_penjual_3">
                    <label>Link e-Katalog 3</label>
                    <input type="url" name="link_penjual_3" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Usulan</button>
        </form>
    </div>
</div>
<script>
    function formatAngka(num) {
        return num.toLocaleString('id-ID');
    }

    function formatRupiah(angka) {
        return angka.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    }

    function unformatAngka(str) {
        return parseInt(str.replace(/[^\d]/g, '')) || 0;
    }

    function setupFormattedInput(displayId, hiddenId, callback) {
        const displayInput = document.getElementById(displayId);
        const hiddenInput = document.getElementById(hiddenId);

        displayInput.addEventListener('input', function() {
            const rawValue = unformatAngka(displayInput.value);
            displayInput.value = formatAngka(rawValue);
            hiddenInput.value = rawValue;
            if (callback) callback();
        });
    }
    // PREVIEW GAMBAR
    document.getElementById('gambar').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('preview-gambar');

        if (file) {
            const reader = new FileReader();

            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = 'block';
            }

            reader.readAsDataURL(file);
        } else {
            preview.src = '#';
            preview.style.display = 'none';
        }
    });

    function hitungTotal() {
        const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
        const hargaPagu = parseInt(document.getElementById('harga_pagu').value) || 0;
        const hargaPerkiraan = parseInt(document.getElementById('perkiraan_harga').value) || 0;

        const totalPagu = jumlah * hargaPagu;
        const totalPerkiraan = jumlah * hargaPerkiraan;

        document.getElementById('total_pagu').textContent = 'Harga Total Pagu + PPN: ' + formatRupiah(totalPagu);
        document.getElementById('total_perkiraan').textContent = 'Harga Total Perkiraan: ' + formatRupiah(totalPerkiraan);
    }

    document.getElementById('jumlah').addEventListener('input', hitungTotal);
    document.getElementById('harga_pagu').addEventListener('input', hitungTotal);
    document.getElementById('perkiraan_harga').addEventListener('input', hitungTotal);

    setupFormattedInput('jumlah_display', 'jumlah', hitungTotal);
    setupFormattedInput('harga_pagu_display', 'harga_pagu', hitungTotal);
    setupFormattedInput('perkiraan_harga_display', 'perkiraan_harga', hitungTotal);
    setupFormattedInput('harga_penjual_1_display', 'harga_penjual_1');
    setupFormattedInput('harga_penjual_2_display', 'harga_penjual_2');
    setupFormattedInput('harga_penjual_3_display', 'harga_penjual_3');
</script>

<style>
    #preview-gambar {
        border: 1px solid #ccc;
        padding: 5px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-height: 200px;
    }
</style>
@endsection