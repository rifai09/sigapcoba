@extends('adminlte.layouts.app')

@section('title', 'Persetujuan Usulan')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
  .timeline { list-style: none; padding: 0; margin: 0; }
  .timeline li { padding: .5rem 0; border-bottom: 1px dashed #ddd; }
  .timeline .role { font-weight: 600; }
  .timeline .meta { font-size: 12px; color: #888; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
  <div class="container-fluid pt-3">
    <div class="row">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar Usulan Barang/Jasa</h3>
          </div>

          <div class="card-body">
            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Tabs: Menunggu / Disetujui / Ditolak --}}
            @php $activeTab = request('tab','waiting'); @endphp
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item">
                <a class="nav-link {{ $activeTab==='waiting'?'active':'' }}"
                   href="{{ route('persetujuan.index',['tab'=>'waiting']) }}">Menunggu Saya</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ $activeTab==='approved'?'active':'' }}"
                   href="{{ route('persetujuan.index',['tab'=>'approved']) }}">Disetujui Saya</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ $activeTab==='rejected'?'active':'' }}"
                   href="{{ route('persetujuan.index',['tab'=>'rejected']) }}">Ditolak Saya</a>
              </li>
            </ul>

            <table id="usulan-table" class="table table-bordered table-striped" style="width:100%">
              <thead>
                <tr>
                  <th>Nama Barang</th>
                  <th>Jumlah</th>
                  <th>Satuan</th>
                  <th>Harga Perkiraan</th>
                  <th>Total Perkiraan</th>
                  <th>Status</th>
                  <th style="width:200px">Aksi</th>
                </tr>
              </thead>
            </table>

          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Modal Detail --}}
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
              <th style="width:220px">Nama Barang</th>
              <td id="nama"></td>
            </tr>
            <tr>
              <th>Spesifikasi</th>
              <td id="spesifikasi"></td>
            </tr>
            <tr>
              <th>Alasan Pengusulan</th>
              <td id="alasan"></td>
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
              <th>Persediaan Saat Ini</th>
              <td id="persediaan_saat_ini"></td>
            </tr>
            <tr>
              <th>Harga Perkiraan</th>
              <td id="harga_perkiraan_det"></td>
            </tr>
            <tr>
              <th>Total Perkiraan</th>
              <td id="total_perkiraan_det"></td>
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
              <td><span class="badge" id="status">-</span></td>
            </tr>
            <tr>
              <th>Gambar</th>
              <td id="gambar"></td>
            </tr>
          </table>
        </div>

        <div class="modal-footer">
          {{-- === Form aksi: disembunyikan default, ditampilkan via JS sesuai role & status === --}}

          {{-- KEPALA UNIT APPROVE --}}
          <form id="form-kepalaunit-approve" method="POST" style="display:none">
            @csrf
            <div class="form-row w-100">
              <div class="form-group col-md-6 mb-2">
                <label class="mb-1">Prioritas (Kepala Unit)</label>
                <select name="prioritas" class="form-control">
                  <option value="">— (biarkan kosong)</option>
                  <option value="urgent">Urgent</option>
                  <option value="normal">Normal</option>
                </select>
              </div>
              <div class="form-group col-md-6 mb-2">
                <label class="mb-1">Catatan (opsional)</label>
                <input type="text" name="catatan" class="form-control" maxlength="1000" placeholder="Catatan singkat">
              </div>
            </div>
            <button class="btn btn-primary">Setujui (Kepala Unit)</button>
          </form>

          {{-- KEPALA UNIT REJECT --}}
          <form id="form-kepalaunit-reject" method="POST" style="display:none">
            @csrf
            <div class="form-group w-100 mb-2">
              <label class="mb-1">Alasan Penolakan (Kepala Unit)</label>
              <textarea name="catatan" class="form-control" rows="2" required></textarea>
            </div>
            <button class="btn btn-danger">Tolak (Kepala Unit)</button>
          </form>

          {{-- KATIMKER APPROVE --}}
          <form id="form-katimker-approve" method="POST" style="display:none">
            @csrf
            <div class="form-row w-100">
              <div class="form-group col-md-6 mb-2">
                <label class="mb-1">Prioritas (Katimker)</label>
                <select name="katimker_priority" class="form-control">
                  <option value="urgent">Urgent</option>
                  <option value="normal" selected>Normal</option>
                </select>
              </div>
              <div class="form-group col-md-6 mb-2">
                <label class="mb-1">Harga Perkiraan (opsional)</label>
                <input type="number" name="harga_perkiraan" class="form-control" min="0" placeholder="Biarkan kosong jika tidak mengubah">
              </div>
            </div>
            <div class="form-group w-100 mb-2">
              <label class="mb-1">Catatan (opsional)</label>
              <input type="text" name="catatan" class="form-control" maxlength="1000">
            </div>
            <button class="btn btn-primary">Setujui (Katimker)</button>
          </form>

          {{-- KATIMKER REJECT --}}
          <form id="form-katimker-reject" method="POST" style="display:none">
            @csrf
            <div class="form-group w-100 mb-2">
              <label class="mb-1">Alasan Penolakan (Katimker)</label>
              <textarea name="katimker_note" class="form-control" rows="2" required></textarea>
            </div>
            <button class="btn btn-danger">Tolak (Katimker)</button>
          </form>

          {{-- KABID APPROVE --}}
          <form id="form-kabid-approve" method="POST" style="display:none">
            @csrf
            <div class="form-row w-100">
              <div class="form-group col-md-6 mb-2">
                <label class="mb-1">Prioritas (Kabid)</label>
                <select name="kabid_priority" class="form-control">
                  <option value="urgent">Urgent</option>
                  <option value="normal" selected>Normal</option>
                </select>
              </div>
              <div class="form-group col-md-6 mb-2">
                <label class="mb-1">Harga Perkiraan (opsional)</label>
                <input type="number" name="harga_perkiraan" class="form-control" min="0" placeholder="Biarkan kosong jika tidak mengubah">
              </div>
            </div>
            <div class="form-group w-100 mb-2">
              <label class="mb-1">Catatan (opsional)</label>
              <input type="text" name="catatan" class="form-control" maxlength="1000">
            </div>
            <button class="btn btn-primary">Setujui (Kabid)</button>
          </form>

          {{-- KABID REJECT --}}
          <form id="form-kabid-reject" method="POST" style="display:none">
            @csrf
            <div class="form-group w-100 mb-2">
              <label class="mb-1">Alasan Penolakan (Kabid)</label>
              <textarea name="kabid_note" class="form-control" rows="2" required></textarea>
            </div>
            <button class="btn btn-danger">Tolak (Kabid)</button>
          </form>

          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>

      </div>
    </div>
  </div>

  {{-- Modal Riwayat Persetujuan --}}
  <div class="modal fade" id="modalLogs" tabindex="-1" role="dialog" aria-labelledby="logsLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logsLabel">Riwayat Persetujuan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <ul class="timeline" id="logs-container">
            <li class="text-muted">Memuat...</li>
          </ul>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function () {
  $('#usulan-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('persetujuan.index') }}",
      data: { tab: "{{ request('tab','waiting') }}" }
    },
    order: [[0,'desc']],
    columns: [
      { data: 'nama_barang', name: 'nama_barang' },
      { data: 'jumlah', name: 'jumlah', className:'text-right' },
      { data: 'satuan', name: 'satuan' },
      { data: 'harga_perkiraan', name: 'harga_perkiraan', className:'text-right' },
      { data: 'total_perkiraan', name: 'total_perkiraan', className:'text-right' },
      { data: 'status', name: 'status' },
      { data: 'aksi', name: 'aksi', orderable:false, searchable:false },
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
  });
});

// role pengguna saat ini untuk logika tombol di modal
const CURRENT_ROLE = "{{ auth()->user()->role }}"; // 'kepala_unit' | 'katimker' | 'kabid' | 'admin' | ...

// Handler tombol detail
$(document).on('click', '.btn-detail', function () {
  // isi detail
  $('#nama').text($(this).data('nama') || '-');
  $('#spesifikasi').text($(this).data('spesifikasi') || '-');
  $('#alasan').text($(this).data('alasan') || '-');
  $('#keterangan').text($(this).data('keterangan') || '-');
  $('#jumlah-satuan').text(($(this).data('jumlah')||0) + ' ' + ($(this).data('satuan')||''));
  $('#persediaan_saat_ini').text($(this).data('persediaan') ?? '-');

  const harga = $(this).data('harga');
  const total = $(this).data('total');
  $('#harga_perkiraan_det').text(harga ? Number(harga).toLocaleString('id-ID') : '-');
  $('#total_perkiraan_det').text(total ? Number(total).toLocaleString('id-ID') : '-');

  $('#unit_pengusul').text($(this).data('unit') || '-');
  $('#lantai').text($(this).data('lantai') || '-');
  $('#ruang').text($(this).data('ruang') || '-');
  $('#sub_ruang').text($(this).data('sub_ruang') || '-');

  const status = String($(this).data('status') || '').toLowerCase();
  $('#status').text(status || '-');
  let badgeClass = 'badge-warning';
  if (status === 'disetujui') badgeClass = 'badge-success';
  else if (status === 'ditolak') badgeClass = 'badge-danger';
  $('#status').attr('class', 'badge ' + badgeClass);

  const gambar = $(this).data('gambar');
  if (gambar) {
    $('#gambar').html(
      `<img src="{{ asset('storage') }}/${gambar}" class="img-fluid" style="max-height:200px" alt="Gambar Barang">`
    );
  } else {
    $('#gambar').html('<span class="text-muted">Tidak ada gambar tersedia</span>');
  }

  // tampilkan form aksi yang sesuai role & status
  const id = $(this).data('id');

  // sembunyikan semua form
  $('#form-kepalaunit-approve, #form-kepalaunit-reject, #form-katimker-approve, #form-katimker-reject, #form-kabid-approve, #form-kabid-reject').hide();

  if (CURRENT_ROLE === 'kepala_unit' && status === 'menunggu_kepala_unit') {
    $('#form-kepalaunit-approve').attr('action', "{{ url('/persetujuan/kepala-unit') }}/" + id + "/setujui").show();
    $('#form-kepalaunit-reject').attr('action',  "{{ url('/persetujuan/kepala-unit') }}/" + id + "/tolak").show();
  } else if (CURRENT_ROLE === 'katimker' && status === 'menunggu_katimker') {
    $('#form-katimker-approve').attr('action', "{{ url('/persetujuan/katimker') }}/" + id + "/setujui").show();
    $('#form-katimker-reject').attr('action',  "{{ url('/persetujuan/katimker') }}/" + id + "/tolak").show();
  } else if (CURRENT_ROLE === 'kabid' && status === 'menunggu_kabid') {
    $('#form-kabid-approve').attr('action', "{{ url('/persetujuan/kabid') }}/" + id + "/setujui").show();
    $('#form-kabid-reject').attr('action',  "{{ url('/persetujuan/kabid') }}/" + id + "/tolak").show();
  }
});

// Handler tombol Riwayat
$(document).on('click', '.btn-logs', async function () {
  const id = $(this).data('id');
  const cont = document.getElementById('logs-container');
  cont.innerHTML = '<li class="text-muted">Memuat...</li>';
  try {
    const res = await fetch("{{ url('/persetujuan/logs') }}/" + id, { headers: { 'Accept': 'application/json' }});
    const data = await res.json();
    cont.innerHTML = '';
    if (!data.length) {
      cont.innerHTML = '<li class="text-muted">Belum ada riwayat.</li>';
      return;
    }
    data.forEach(l => {
      const role = l.role || '-';
      const status = l.status || '-';
      const prio = l.prioritas ? ` • Prioritas: ${l.prioritas}` : '';
      const cat = l.catatan ? `<div>${l.catatan}</div>` : '';
      const user = l.user_name ? `oleh ${l.user_name}` : '';
      const at = l.created_at ? new Date(l.created_at).toLocaleString('id-ID') : '';

      cont.insertAdjacentHTML('beforeend', `
        <li>
          <div class="role">${role} — <span class="text-capitalize">${status}</span>${prio}</div>
          ${cat}
          <div class="meta">${user} • ${at}</div>
        </li>
      `);
    });
  } catch (e) {
    cont.innerHTML = '<li class="text-danger">Gagal memuat riwayat</li>';
    console.error(e);
  }
});
</script>
@endpush
