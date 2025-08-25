@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<li class="nav-item dropdown" id="notif-dropdown">
  <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
    <i class="far fa-bell"></i>
    <span class="badge badge-danger navbar-badge" id="notif-badge" style="{{ $unreadCount ? '' : 'display:none' }}">
      {{ $unreadCount }}
    </span>
  </a>
  <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="min-width:320px">
    <span class="dropdown-item dropdown-header">
      Notifikasi
      <button class="btn btn-sm btn-link float-right p-0" id="notif-readall">Tandai sudah dibaca</button>
    </span>
    <div class="dropdown-divider"></div>

    {{-- Container item notifikasi (akan diisi via AJAX) --}}
    <div id="notif-items">
      <div class="dropdown-item text-center text-muted py-3">Memuat...</div>
    </div>

    <div class="dropdown-divider"></div>
    <a href="{{ route('persetujuan.index', ['tab'=>'waiting']) }}" class="dropdown-item dropdown-footer">
      Lihat semua
    </a>
  </div>
</li>

@push('scripts')
<script>
(function() {
  // Helper POST JSON dengan CSRF
  async function postJson(url = '') {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      }
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  async function loadDropdown() {
    const cont  = document.getElementById('notif-items');
    const badge = document.getElementById('notif-badge');
    try {
      const res = await fetch("{{ route('notifications.dropdown') }}", {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      // Badge
      if (badge) {
        if (data.unread_count && data.unread_count > 0) {
          badge.style.display = '';
          badge.textContent = data.unread_count;
        } else {
          badge.style.display = 'none';
          badge.textContent = '0';
        }
      }

      // Items
      if (!cont) return;
      cont.innerHTML = '';

      const notifs = (data.notifications || []);
      if (notifs.length === 0) {
        cont.innerHTML = '<div class="dropdown-item text-center text-muted py-3">Tidak ada notifikasi</div>';
        return;
      }

      notifs.forEach(n => {
        const d = n.data || {};
        const url = d.url || "{{ route('persetujuan.index', ['tab'=>'waiting']) }}";
        const title = d.title || 'Notifikasi';
        const message = d.message || '';
        const created = n.created_at;

        const isUnread = !n.read_at;
        const dot = isUnread ? '<span class="badge badge-warning ml-2">baru</span>' : '';

        const item = `
          <a href="${url}" class="dropdown-item">
            <div class="d-flex flex-column">
              <div class="font-weight-bold">${title} ${dot}</div>
              <div class="text-muted" style="white-space:normal">${message}</div>
              <small class="text-secondary mt-1">${created ? new Date(created).toLocaleString('id-ID') : ''}</small>
            </div>
          </a>
          <div class="dropdown-divider"></div>
        `;
        cont.insertAdjacentHTML('beforeend', item);
      });
    } catch (e) {
      if (cont) cont.innerHTML = '<div class="dropdown-item text-danger">Gagal memuat notifikasi</div>';
      console.error(e);
    }
  }

  $(function () {
    // Pakai jQuery untuk menangkap event Bootstrap 4
    $('#notif-dropdown').on('show.bs.dropdown', loadDropdown);

    // Tombol "Tandai sudah dibaca"
    $('#notif-readall').on('click', async function(e) {
      e.preventDefault();
      try {
        await postJson("{{ route('notifications.readAll') }}");
        // Segarkan dropdown & badge
        await loadDropdown();
      } catch (err) {
        console.error(err);
      }
    });
  });
})();
</script>
@endpush
