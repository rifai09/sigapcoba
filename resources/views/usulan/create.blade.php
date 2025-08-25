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
          <div class="alert alert-success m-3">{{ session('success') }}</div>
          @endif

          @if ($errors->any())
          <div class="alert alert-danger m-3">
            <ul class="mb-0">
              @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
          </div>
          @endif

          <form action="{{ route('usulan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card-body">

              {{-- Unit Pengusul --}}
              <div class="form-group">
                <label for="unit_pengusul">Unit Pengusul</label>
                <select name="unit_id" id="unit_pengusul"
                  class="form-control select2 @error('unit_id') is-invalid @enderror" required>
                  <option value="">Pilih Unit</option>
                  @foreach($units as $unit)
                  <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->nama }}
                  </option>
                  @endforeach
                </select>
                @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <hr>

              {{-- Nama Barang/Jasa --}}
              <div class="form-group">
                <label for="nama_barang">Nama Barang/Jasa</label>
                <input type="text" name="nama_barang"
                  class="form-control @error('nama_barang') is-invalid @enderror"
                  value="{{ old('nama_barang') }}" required>
                @error('nama_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Spesifikasi & Keterangan --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="spesifikasi">Spesifikasi</label>
                  <textarea name="spesifikasi" rows="2"
                    class="form-control @error('spesifikasi') is-invalid @enderror"
                    required>{{ old('spesifikasi') }}</textarea>
                  @error('spesifikasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-6">
                  <label for="keterangan">Keterangan Tambahan</label>
                  <textarea name="keterangan" rows="2"
                    class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
                  @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Upload Gambar --}}
              <div class="form-group">
                <label for="gambar">Upload Gambar (opsional)</label>
                <input type="file" name="gambar" id="gambar"
                  class="form-control-file @error('gambar') is-invalid @enderror" accept="image/*">
                @error('gambar') <div class="text-danger">{{ $message }}</div> @enderror
                <div class="mt-2">
                  <img id="preview-gambar" src="#" alt="Preview Gambar" style="display:none; max-height:200px;">
                </div>
              </div>

              {{-- Jumlah, Harga (opsional), Satuan (dropdown) --}}
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="jumlah_display">Jumlah</label>
                  <input type="text" id="jumlah_display" class="form-control" value="{{ old('jumlah') }}" required>
                  <input type="hidden" name="jumlah" id="jumlah" value="{{ old('jumlah') }}">
                </div>

                <div class="form-group col-md-4">
                  <label for="harga_perkiraan_display">Harga Perkiraan (Rp) <small class="text-muted">(opsional)</small></label>
                  <input type="text" id="harga_perkiraan_display" class="form-control" value="{{ old('harga_perkiraan') }}">
                  <input type="hidden" name="harga_perkiraan" id="harga_perkiraan" value="{{ old('harga_perkiraan') }}">
                </div>

                <div class="form-group col-md-4">
                  <label for="satuan">Satuan</label>
                  <select name="satuan" id="satuan"
                    class="form-control @error('satuan') is-invalid @enderror" required>
                    <option value="" disabled {{ old('satuan') ? '' : 'selected' }}>Pilih satuan</option>
                    @php
                    $opsiSatuan = ['Unit','Set','Kotak','Wadah','Liter','Meter Kubik (m³)','Kilogram','Meter','Lembar','Pasang'];
                    @endphp
                    @foreach($opsiSatuan as $o)
                    <option value="{{ $o }}" {{ old('satuan')===$o ? 'selected':'' }}>{{ $o }}</option>
                    @endforeach
                  </select>
                  @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Persediaan Saat Ini --}}
              <div class="form-group">
                <label for="persediaan_saat_ini">Persediaan Saat Ini</label>
                <input type="number" name="persediaan_saat_ini" id="persediaan_saat_ini"
                  class="form-control @error('persediaan_saat_ini') is-invalid @enderror"
                  value="{{ old('persediaan_saat_ini', 0) }}" min="0" required>
                @error('persediaan_saat_ini') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Total Perkiraan (otomatis) --}}
              <div class="form-group">
                <p id="total_perkiraan" class="font-weight-bold">Total Perkiraan: —</p>
              </div>

              <hr>
              <h5>Detail Penempatan</h5>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="lantai_id">Lantai</label>
                  <select name="lantai_id" id="lantai"
                    class="form-control select2 @error('lantai_id') is-invalid @enderror" required>
                    <option value="">Pilih Lantai</option>
                    @foreach($lantais as $lantai)
                    <option value="{{ $lantai->id }}" {{ old('lantai_id') == $lantai->id ? 'selected' : '' }}>
                      {{ $lantai->nama }}
                    </option>
                    @endforeach
                  </select>
                  @error('lantai_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="ruang_id">Ruang</label>
                  <select name="ruang_id" id="ruang"
                    class="form-control select2 @error('ruang_id') is-invalid @enderror" required>
                    <option value="">Pilih Ruang</option>
                  </select>
                  @error('ruang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="sub_ruang_id">Sub Ruang</label>
                  <select name="sub_ruang_id" id="sub_ruang"
                    class="form-control select2 @error('sub_ruang_id') is-invalid @enderror" required>
                    <option value="">Pilih Sub Ruang</option>
                  </select>
                  @error('sub_ruang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="form-group mt-3">
                <label for="alasan_pengusulan">Alasan Pengusulan <span class="text-danger">*</span></label>
                <textarea name="alasan_pengusulan" id="alasan_pengusulan" rows="4"
                  class="form-control @error('alasan_pengusulan') is-invalid @enderror" required>{{ old('alasan_pengusulan') }}</textarea>
                @error('alasan_pengusulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="form-text text-muted">Jelaskan urgensi/kebutuhan, dampak, atau kondisi saat ini.</small>
              </div>
              <button type="submit" class="btn btn-primary">Kirim Usulan</button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // ===== Helper angka =====
  function formatAngka(num) {
    return Number(num || 0).toLocaleString('id-ID');
  }

  function unformatAngka(str) {
    if (!str) return 0;
    return parseInt(String(str).replace(/[^\d]/g, ''), 10) || 0;
  }
  // Setup field tampilan (ribuan) + hidden mentah
  function setupFormattedInput(displayId, hiddenId, callback) {
    const displayInput = document.getElementById(displayId);
    const hiddenInput = document.getElementById(hiddenId);
    if (!displayInput || !hiddenInput) return;

    const initRaw = unformatAngka(displayInput.value || hiddenInput.value);
    displayInput.value = formatAngka(initRaw);
    hiddenInput.value = initRaw;

    displayInput.addEventListener('input', function() {
      const raw = unformatAngka(displayInput.value);
      displayInput.value = formatAngka(raw);
      hiddenInput.value = raw;
      if (typeof callback === 'function') callback();
    });
  }

  // Hitung total perkiraan = jumlah * harga_perkiraan
  function hitungTotal() {
    const jumlah = parseInt(document.getElementById('jumlah')?.value || 0, 10);
    const harga = parseInt(document.getElementById('harga_perkiraan')?.value || 0, 10);
    const el = document.getElementById('total_perkiraan');
    if (!el) return;

    if (jumlah > 0 && harga > 0) {
      el.textContent = 'Total Perkiraan: Rp ' + formatAngka(jumlah * harga);
    } else {
      el.textContent = 'Total Perkiraan: —';
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Preview gambar
    const fileInput = document.getElementById('gambar');
    const preview = document.getElementById('preview-gambar');
    if (fileInput && preview) {
      fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) {
          preview.style.display = 'none';
          return;
        }
        const reader = new FileReader();
        reader.onload = ev => {
          preview.src = ev.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      });
    }

    // Formatter angka + kalkulasi total
    setupFormattedInput('jumlah_display', 'jumlah', hitungTotal);
    setupFormattedInput('harga_perkiraan_display', 'harga_perkiraan', hitungTotal);
    hitungTotal(); // panggil awal

    // Select2 (jika tersedia)
    if (window.$ && $.fn.select2) {
      $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih',
        allowClear: true
      });
    }

    // ===== Cascading Lokasi =====
    // Muat ruang berdasarkan lantai
    $('#lantai').on('change', function() {
      const lantaiId = $(this).val();
      const $ruang = $('#ruang');
      const $sub = $('#sub_ruang');

      $ruang.html('<option>Memuat...</option>');
      $sub.html('<option value="">Pilih Sub Ruang</option>');

      if (!lantaiId) {
        $ruang.html('<option value="">Pilih Ruang</option>');
        return;
      }

      $.get('{{ route("locations.ruang", ":lantai") }}'.replace(':lantai', lantaiId), function(data) {
        $ruang.html('<option value="">Pilih Ruang</option>');
        data.forEach(function(item) {
          $ruang.append(`<option value="${item.id}">${item.nama}</option>`);
        });
      });
    });

    // Muat sub-ruang berdasarkan ruang
    $('#ruang').on('change', function() {
      const ruangId = $(this).val();
      const $sub = $('#sub_ruang');

      $sub.html('<option>Memuat...</option>');

      if (!ruangId) {
        $sub.html('<option value="">Pilih Sub Ruang</option>');
        return;
      }

      $.get('{{ route("locations.subruang", ":ruang") }}'.replace(':ruang', ruangId), function(data) {
        $sub.html('<option value="">Pilih Sub Ruang</option>');
        data.forEach(function(item) {
          $sub.append(`<option value="${item.id}">${item.nama}</option>`);
        });
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
    max-height: 200px;
  }
</style>