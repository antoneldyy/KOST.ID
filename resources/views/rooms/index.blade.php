@extends('layout.master')

@section('content')
  <div class="main-content">
    <section class="section">
      <div class="section-header">
        <h1>Data Kamar</h1>
      </div>

      <div class="section-body">
        <div class="card">
          <div class="card-header">
            <h4>Daftar Kamar</h4>
            <div class="card-header-action">
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">Tambah Kamar</button>
            </div>
          </div>
          <div class="card-body">
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
                    <td>
                      <div class="d-flex align-items-center">
                        <span>{{ $room->number }}</span>
                        <div class="dropdown ml-2">
                          <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="roomMenu{{ $room->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-chevron-down"></i>
                          </button>
                          <div class="dropdown-menu" aria-labelledby="roomMenu{{ $room->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalEdit{{ $room->id }}">
                              <i class="fas fa-edit text-primary"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#" onclick="deleteRoom({{ $room->id }})">
                              <i class="fas fa-trash text-danger"></i> Hapus
                            </a>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>{{ optional($room->tenant)->name ?? '-' }}</td>
                    <td>
                      <button class="btn btn-sm btn-info" onclick="loadPayments({{ $room->id }})" data-toggle="collapse" data-target="#payments{{ $room->id }}">
                        <i class="fas fa-receipt"></i> Lihat Bukti
                      </button>
                    </td>
                    <td>
                      <span class="badge badge-secondary">-</span>
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
                                  <option value="{{ $tenant->id }}" {{ $room->user_id == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                                @endforeach
                              </select>
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
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal fade" tabindex="-1" role="dialog" id="modalCreate">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="POST" action="{{ route('rooms.store') }}">
          @csrf
          <div class="modal-header"><h5 class="modal-title">Tambah Kamar</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Nomor Kamar</label>
              <input type="text" class="form-control" name="number" required>
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
        const statusBadge = payment.paid_at ? 
          '<span class="badge badge-success">Lunas</span>' : 
          '<span class="badge badge-warning">Menunggu</span>';
        
        const proofButton = payment.proof_path ? 
          `<a href="/storage/${payment.proof_path}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>` :
          '<span class="text-muted">-</span>';
        
        const actionButtons = !payment.paid_at ? 
          `<button class="btn btn-sm btn-success" onclick="approvePayment(${payment.id})"><i class="fas fa-check"></i> Approve</button>
           <button class="btn btn-sm btn-danger" onclick="rejectPayment(${payment.id})"><i class="fas fa-times"></i> Reject</button>` :
          '<span class="text-muted">-</span>';
        
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
      if (data.success) {
        location.reload();
      } else {
        alert('Gagal approve pembayaran');
      }
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
      if (data.success) {
        location.reload();
      } else {
        alert('Gagal reject pembayaran');
      }
    })
    .catch(() => alert('Gagal reject pembayaran'));
  }
}
</script>
@endsection
