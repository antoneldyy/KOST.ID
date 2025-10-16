@extends('layout.master')

@section('title', 'Data Kamar')

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>Data Kamar</h1>
      <div class="section-header-button">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">Tambah Kamar</button>
      </div>
    </div>

    <div class="section-body">
      <div class="card">
        <div class="card-body">
          {{-- ✅ Alert sukses & error --}}
          @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          @endif
          @if($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          @endif

          {{-- ✅ Tabel utama --}}
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Nomor Kamar</th>
                  <th>Penghuni</th>
                  <th>Bukti Bayar</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($rooms as $room)
                <tr>
                  <td>{{ $room->number }}</td>
                  <td>{{ optional($room->tenant)->name ?? '-' }}</td>
                  <td>
                    <button class="btn btn-sm btn-info"
                            onclick="loadPayments({{ $room->id }})"
                            data-toggle="collapse"
                            data-target="#payments{{ $room->id }}">
                      <i class="fas fa-receipt"></i> Lihat Bukti
                    </button>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button class="btn btn-sm btn-warning" data-toggle="modal"
                              data-target="#modalEdit{{ $room->id }}">
                        <i class="fas fa-edit"></i> Edit
                      </button>
                      <button class="btn btn-sm btn-danger" onclick="deleteRoom({{ $room->id }})">
                        <i class="fas fa-trash"></i> Hapus
                      </button>
                    </div>
                  </td>
                </tr>

                <tr class="collapse" id="payments{{ $room->id }}">
                  <td colspan="4">
                    <div id="payments-body-{{ $room->id }}">
                      <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                          <span class="sr-only">Loading...</span>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

{{-- ✅ Modal Tambah Kamar --}}
<div class="modal fade" tabindex="-1" role="dialog" id="modalCreate">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('rooms.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Kamar</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nomor Kamar</label>
            <input type="text" class="form-control" name="number" placeholder="Contoh: A01" required>
          </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ✅ Modal Edit Semua Kamar (Ditaruh di luar tabel agar tidak bentrok Bootstrap) --}}
@foreach($rooms as $room)
<div class="modal fade" tabindex="-1" role="dialog" id="modalEdit{{ $room->id }}">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('rooms.update',$room) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Kamar</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nomor Kamar</label>
            <input type="text" class="form-control" name="number" value="{{ $room->number }}" required>
          </div>
          <div class="form-group">
            <label>Penghuni</label>
            <select class="form-control" name="user_id">
              <option value="">- Kosong -</option>
              @foreach(\App\Models\User::where('role','user')->where('status','active')->orderBy('name')->get() as $tenant)
                <option value="{{ $tenant->id }}"
                        {{ $room->user_id == $tenant->id ? 'selected' : '' }}>
                  {{ $tenant->name }}
                </option>
              @endforeach
            </select>
          </div>
          @if($room->tenant)
          <div class="form-group">
            <label>Status Penghuni</label>
            <select class="form-control" name="tenant_status">
              <option value="active" {{ $room->tenant->status == 'active' ? 'selected' : '' }}>Aktif</option>
              <option value="deactive" {{ $room->tenant->status == 'deactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
          </div>
          @endif
        </div>
        <div class="modal-footer bg-whitesmoke br">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endsection

@section('js')
<script>
function loadPayments(roomId) {
  const target = document.getElementById('payments-body-' + roomId);
  target.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';

  fetch('/admin/rooms/' + roomId + '/payments')
    .then(r => r.json())
    .then(payments => {
      if (!payments.length) {
        target.innerHTML = '<div class="text-center py-3 text-muted">Belum ada bukti pembayaran</div>';
        return;
      }

      let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
      html += '<thead class="thead-light"><tr><th>Bulan/Tahun</th><th>Jumlah</th><th>Bukti Bayar</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';

      payments.forEach(payment => {
        const statusBadge = (!payment.approved_at && payment.status === 'rejected')
            ? '<span class="badge badge-danger">Ditolak</span>'
            : (payment.approved_at
                ? '<span class="badge badge-success">Disetujui</span>'
                : (payment.paid_at
                    ? '<span class="badge badge-warning">Menunggu ACC</span>'
                    : '<span class="badge badge-secondary">Belum bayar</span>'));

        const proofButton = payment.proof_path
          ? `<a href="/storage/${payment.proof_path}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>`
          : '<span class="text-muted">-</span>';

        const actionButtons = (payment.paid_at && !payment.approved_at && payment.status !== 'rejected')
          ? `<button class="btn btn-sm btn-success" onclick="approvePayment(${payment.id}, this)">
               <i class="fas fa-check"></i> Approve
             </button>
             <button class="btn btn-sm btn-danger" onclick="rejectPayment(${payment.id}, this)">
               <i class="fas fa-times"></i> Reject
             </button>`
          : '-';

        html += `<tr id="payment-row-${payment.id}">
          <td>${payment.month}/${payment.year}</td>
          <td>Rp ${payment.amount.toLocaleString()}</td>
          <td>${proofButton}</td>
          <td class="status-cell">${statusBadge}</td>
          <td class="action-cell">${actionButtons}</td>
        </tr>`;
      });

      html += '</tbody></table></div>';
      target.innerHTML = html;
    })
    .catch(() => target.innerHTML = '<div class="text-center py-3 text-danger">Gagal memuat data</div>');
}

// Auto-open room payments and optionally highlight a payment if URL params are present
document.addEventListener('DOMContentLoaded', function() {
  const params = new URLSearchParams(window.location.search);
  const openRoomId = params.get('open_room_id');
  const openPaymentId = params.get('open_payment_id');
  if (openRoomId) {
    // find the collapse toggle button and trigger it
    const targetBtn = document.querySelector(`[data-target="#payments${openRoomId}"]`);
    if (targetBtn) {
      // open collapse
      const collapseEl = document.getElementById('payments' + openRoomId);
      if (collapseEl && collapseEl.classList.contains('collapse')) {
        $(collapseEl).collapse('show');
      }
      // load payments then highlight
      loadPayments(parseInt(openRoomId));

      // after a short delay try to highlight the specific payment row when loaded
      if (openPaymentId) {
        const tryHighlight = () => {
          const row = document.getElementById('payment-row-' + openPaymentId);
          if (row) {
            row.classList.add('table-primary');
            row.scrollIntoView({behavior: 'smooth', block: 'center'});
          } else {
            // try again shortly until loaded
            setTimeout(tryHighlight, 300);
          }
        };
        setTimeout(tryHighlight, 500);
      }
    }
  }
});

function deleteRoom(roomId) {
  if (confirm('Hapus kamar?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/rooms/' + roomId;

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';

    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';

    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
  }
}

function approvePayment(paymentId) {
  if (confirm('Approve pembayaran ini?')) {
    fetch(`/admin/payments/${paymentId}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById(`payment-row-${paymentId}`);
        row.querySelector('.status-cell').innerHTML = '<span class="badge badge-success">Disetujui</span>';
        row.querySelector('.action-cell').innerHTML = '-';
      } else alert(data.message || 'Gagal approve pembayaran');
    })
    .catch(() => alert('Gagal approve pembayaran'));
  }
}

function rejectPayment(paymentId) {
  if (confirm('Reject pembayaran ini?')) {
    fetch(`/admin/payments/${paymentId}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById(`payment-row-${paymentId}`);
        row.querySelector('.status-cell').innerHTML = '<span class="badge badge-danger">Ditolak</span>';
        row.querySelector('.action-cell').innerHTML = '-';
      } else alert(data.message || 'Gagal reject pembayaran');
    })
    .catch(() => alert('Gagal reject pembayaran'));
  }
}
</script>
@endsection
