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
                    <button class="btn btn-sm btn-info" onclick="loadPayments({{ $room->id }})" data-toggle="collapse" data-target="#payments{{ $room->id }}">
                      <i class="fas fa-receipt"></i> Lihat Bukti
                    </button>
                  </td>
                  <td>
                    <div class="btn-group">
                      <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit{{ $room->id }}">
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
                <div class="modal fade" tabindex="-1" role="dialog" id="modalEdit{{ $room->id }}">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('rooms.update',$room) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header"><h5 class="modal-title">Edit Kamar</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                              @foreach(\App\Models\User::where('role','user')->orderBy('name')->get() as $tenant)
                                <option value="{{ $tenant->id }}" {{ $room->user_id == $tenant->id ? 'selected' : '' }} {{ $tenant->status === 'deactive' ? 'disabled' : '' }}>
                                  {{ $tenant->name }} {{ $tenant->status === 'deactive' ? '(nonaktif)' : '' }}
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
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
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
        const statusBadge = payment.approved_at ?
          '<span class="badge badge-success">Disetujui</span>' :
          (payment.paid_at ? '<span class="badge badge-warning">Menunggu ACC</span>' : '<span class="badge badge-secondary">Belum bayar</span>');

        const proofButton = payment.proof_path ?
          `<a href="/storage/${payment.proof_path}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>` :
          '<span class="text-muted">-</span>';

        // Tampilkan tombol Approve/Reject hanya jika sudah dibayar tapi belum di-ACC
        const actionButtons = (payment.paid_at && !payment.approved_at && !payment.approved_by) ?
          `<button class="btn btn-sm btn-success" onclick="approvePayment(${payment.id})"><i class="fas fa-check"></i> Approve</button>
           <button class="btn btn-sm btn-danger" onclick="rejectPayment(${payment.id})"><i class="fas fa-times"></i> Reject</button>` :
          '-';

        html += `<tr>
          <td>${payment.month}/${payment.year}</td>
          <td>Rp ${payment.amount.toLocaleString()}</td>
          <td>${proofButton}</td>
          <td>${statusBadge}</td>
          <td>${actionButtons}</td>
        </tr>`;
      });

      html += '</tbody></table></div>';
      target.innerHTML = html;
    })
    .catch(() => target.innerHTML = '<div class="text-center py-3 text-danger">Gagal memuat data</div>');
}

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
    fetch('/admin/payments/' + paymentId + '/approve', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) location.reload();
      else alert(data.message || 'Gagal approve pembayaran');
    })
    .catch(() => alert('Gagal approve pembayaran'));
  }
}

function rejectPayment(paymentId) {
  if (confirm('Reject pembayaran ini?')) {
    fetch('/admin/payments/' + paymentId + '/reject', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) location.reload();
      else alert(data.message || 'Gagal reject pembayaran');
    })
    .catch(() => alert('Gagal reject pembayaran'));
  }
}
</script>
@endsection
