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
                        class="form-control select2 @error('unit_id') is-invalid @enderror"
                        data-placeholder="Pilih Unit" required>
                  <option></option> {{-- kosong: agar placeholder tampil namun tidak ada di list --}}
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

              {{-- Jumlah, Harga (opsional), Satuan --}}
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
                          class="form-control select2 @error('satuan') is-invalid @enderror"
                          data-placeholder="Pilih Satuan" required>
                    <option></option>
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
                  <label for="lantai">Lantai</label>
                  <select name="lantai_id" id="lantai"
                          class="form-control select2 @error('lantai_id') is-invalid @enderror"
                          data-placeholder="Pilih Lantai" required>
                    <option></option>
                    @foreach($lantais as $lantai)
                      <option value="{{ $lantai->id }}" {{ old('lantai_id') == $lantai->id ? 'selected' : '' }}>
                        {{ $lantai->nama }}
                      </option>
                    @endforeach
                  </select>
                  @error('lantai_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="ruang">Ruang</label>
                  <select name="ruang_id" id="ruang"
                          class="form-control select2 @error('ruang_id') is-invalid @enderror"
                          data-placeholder="Pilih Ruang" required>
                    <option></option>
                  </select>
                  @error('ruang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                  <label for="sub_ruang">Sub Ruang</label>
                  <select name="sub_ruang_id" id="sub_ruang"
                          class="form-control select2 @error('sub_ruang_id') is-invalid @enderror"
                          data-placeholder="Pilih Sub Ruang">
                    <option></option>
                  </select>
                  @error('sub_ruang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  <small class="form-text text-muted">Pilih tidak ada detail sub ruang jika tidak ada data.</small>
                </div>
              </div>

              {{-- Alasan Pengusulan --}}
              <div class="form-group mt-3">
                <label for="alasan_pengusulan">Alasan Pengusulan <span class="text-danger">*</span></label>
                <textarea name="alasan_pengusulan" id="alasan_pengusulan" rows="4"
                          class="form-control @error('alasan_pengusulan') is-invalid @enderror" required>{{ old('alasan_pengusulan') }}</textarea>
                @error('alasan_pengusulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet"/>
  <style>
    .select2-container--bootstrap4 .select2-selection--single {
      height: calc(2.25rem + 2px);
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
      line-height: 2.25rem;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
      height: calc(2.25rem + 2px);
    }
    #preview-gambar{
      border:1px solid #ccc;
      padding:5px;
      border-radius:8px;
      box-shadow:0 2px 4px rgba(0,0,0,.1);
      max-height:200px;
    }
  </style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  // Preview gambar
  const fileInput=document.getElementById('gambar');
  const preview=document.getElementById('preview-gambar');
  if(fileInput&&preview){
    fileInput.addEventListener('change', e=>{
      const file=e.target.files[0];
      if(!file){ preview.style.display='none'; return; }
      const reader=new FileReader();
      reader.onload=ev=>{ preview.src=ev.target.result; preview.style.display='block'; };
      reader.readAsDataURL(file);
    });
  }

  // Format angka
  function formatAngka(num){ return Number(num||0).toLocaleString('id-ID'); }
  function unformatAngka(str){ return parseInt((str||'').replace(/[^\d]/g,''))||0; }
  function setupFormattedInput(disp,hid,cb){
    const d=document.getElementById(disp), h=document.getElementById(hid);
    if(!d||!h) return;
    d.value=formatAngka(unformatAngka(d.value||h.value));
    h.value=unformatAngka(d.value);
    d.addEventListener('input',()=>{
      h.value=unformatAngka(d.value); d.value=formatAngka(h.value); if(cb) cb();
    });
  }
  function hitungTotal(){
    const j=parseInt(document.getElementById('jumlah')?.value||0,10);
    const h=parseInt(document.getElementById('harga_perkiraan')?.value||0,10);
    document.getElementById('total_perkiraan').textContent=(j>0&&h>0)?'Total Perkiraan: Rp '+formatAngka(j*h):'Total Perkiraan: —';
  }
  setupFormattedInput('jumlah_display','jumlah',hitungTotal);
  setupFormattedInput('harga_perkiraan_display','harga_perkiraan',hitungTotal);
  hitungTotal();

  // Inisialisasi Select2 (pakai placeholder dari data-placeholder)
  $('.select2').each(function(){
    const ph = this.dataset.placeholder || 'Silakan pilih';
    $(this)
      .select2({ theme:'bootstrap4', allowClear:true, width:'100%', placeholder: ph, dropdownAutoWidth: true })
      .on('focus',function(){ try{$(this).select2('open');}catch(e){} });
  });
  $(document).on('select2:open', ()=>{ document.querySelector('.select2-search__field')?.focus(); });

  // Cascading Lokasi
  const urlRuangTmpl = @json(route('locations.ruang',['lantai'=>'__LANTAI__']));
  const urlSubTmpl   = @json(route('locations.subruang',['ruang'=>'__RUANG__']));
  const $lantai=$('#lantai'), $ruang=$('#ruang'), $subRuang=$('#sub_ruang');

  // Helper: reset options -> tambahkan <option></option> (kosong) supaya placeholder aktif, tapi tidak tampil di list
  function resetOptions($sel, items){
    const prev=$sel.val();
    $sel.empty().append(new Option('', '', true, false)); // kosong untuk placeholder
    (items||[]).forEach(x=> $sel.append(new Option(x.nama, x.id, false, false)));
    $sel.val(null).trigger('change.select2'); // pastikan placeholder tampil
    if(prev && $sel.find('option[value="'+prev+'"]').length){
      $sel.val(prev).trigger('change.select2');
    }
  }

  // Lantai -> Ruang
  $lantai.on('change', function(){
    const id=this.value;
    resetOptions($ruang, []);
    resetOptions($subRuang, []);
    if(!id) return;
    fetch(urlRuangTmpl.replace('__LANTAI__',encodeURIComponent(id)))
      .then(r=>r.json())
      .then(d=> resetOptions($ruang, d))
      .catch(()=> resetOptions($ruang, []));
  });

  // Ruang -> Sub Ruang
  $ruang.on('change', function(){
    const id=this.value;
    resetOptions($subRuang, []);
    if(!id) return;
    fetch(urlSubTmpl.replace('__RUANG__',encodeURIComponent(id)))
      .then(r=>r.json())
      .then(d=> resetOptions($subRuang, d)) // d bisa berisi sentinel __NONE__
      .catch(()=> resetOptions($subRuang, []));
  });

  // Map sentinel '__NONE__' -> '' sebelum submit (supaya DB NULL)
  $('form').on('submit', function(){
    if($subRuang.val()==='__NONE__'){ $subRuang.val(''); }
  });
});
</script>
@endpush
