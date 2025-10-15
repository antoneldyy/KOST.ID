@extends('layout.master')

@section('content')
  <div class="main-content">
    <section class="section">
      <div class="section-header">
        <h1>Data Penghuni</h1>
        <div class="section-header-breadcrumb">
          <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-filter"></i> 
              @if(request('status') == 'deactive')
                Nonaktif
              @elseif(request('status') == 'all')
                Semua
              @else
                Aktif
              @endif
            </button>
            <div class="dropdown-menu" aria-labelledby="filterDropdown">
              <a class="dropdown-item {{ request('status') == 'active' || !request('status') ? 'active' : '' }}" href="#" onclick="filterTenants('active')">
                <i class="fas fa-check-circle text-success"></i> Aktif
              </a>
              <a class="dropdown-item {{ request('status') == 'deactive' ? 'active' : '' }}" href="#" onclick="filterTenants('deactive')">
                <i class="fas fa-times-circle text-danger"></i> Nonaktif
              </a>
              <a class="dropdown-item {{ request('status') == 'all' ? 'active' : '' }}" href="#" onclick="filterTenants('all')">
                <i class="fas fa-list text-info"></i> Semua
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="section-body">
        <div class="card">
          <div class="card-header">
            <h4>Daftar Penghuni</h4>
            <div class="card-header-action">
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalCreate">Tambah Penghuni</button>
            </div>
          </div>
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
                    <th>No. Kamar</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Pekerjaan</th>
                    <th>No. HP</th>
                    <th>KTP</th>
                    <th>Status Penghuni</th>
                    <th>History Bayar</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($tenants as $tenant)
                  <tr>
                    <td>{{ optional($tenant->room)->number ?? '-' }}</td>
                    <td>{{ $tenant->name }}</td>
                    <td>{{ $tenant->email }}</td>
                    <td>{{ $tenant->occupation ?? '-' }}</td>
                    <td>{{ $tenant->phone ?? '-' }}</td>
                    <td>
                      @if($tenant->ktp_path)
                        <a href="{{ asset('storage/' . $tenant->ktp_path) }}" target="_blank" class="btn btn-sm btn-info">Lihat KTP</a>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge badge-{{ $tenant->status == 'active' ? 'success' : 'danger' }}">
                        {{ $tenant->status == 'active' ? 'Aktif' : 'Nonaktif' }}
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-info" onclick="loadPayments({{ $tenant->id }})" data-toggle="collapse" data-target="#payments{{ $tenant->id }}">Lihat</button>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modalEdit{{ $tenant->id }}">
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        @if($tenant->status == 'active')
                          <form action="{{ route('tenants.deactivate', $tenant) }}" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan penghuni ini?')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-danger">
                              <i class="fas fa-user-times"></i> Nonaktifkan
                            </button>
                          </form>
                        @else
                          <form action="{{ route('tenants.activate', $tenant) }}" method="POST" class="d-inline" onsubmit="return confirm('Aktifkan penghuni ini?')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-success">
                              <i class="fas fa-user-check"></i> Aktifkan
                            </button>
                          </form>
                        @endif
                      </div>
                    </td>
                  </tr>
                  <tr class="collapse" id="payments{{ $tenant->id }}">
                    <td colspan="9">
                      <div id="payments-body-{{ $tenant->id }}">Loading...</div>
                    </td>
                  </tr>

                  <div class="modal fade" tabindex="-1" role="dialog" id="modalEdit{{ $tenant->id }}">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <form method="POST" action="{{ route('tenants.update',$tenant) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <div class="modal-header"><h5 class="modal-title">Edit Penghuni</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                              <label>Nama</label>
                              <input type="text" class="form-control" name="name" value="{{ $tenant->name }}" required>
                            </div>
                            <div class="form-group">
                              <label>Pindahkan/Atur Kamar</label>
                              <select class="form-control" name="room_id">
                                <option value="">- Tidak ada -</option>
                                @foreach(\App\Models\Room::with('tenant')->orderBy('number')->get() as $room)
                                  <option value="{{ $room->id }}" 
                                    {{ optional($tenant->room)->id == $room->id ? 'selected' : '' }}
                                    {{ $room->user_id && $room->user_id != $tenant->id ? 'disabled' : '' }}>
                                    {{ $room->number }}
                                    @if($room->tenant && $room->tenant->id != $tenant->id)
                                      - terisi oleh {{ $room->tenant->name }}
                                    @endif
                                  </option>
                                @endforeach
                              </select>
                              <small class="form-text text-muted">Hanya kamar kosong yang bisa dipilih.</small>
                            </div>
                            <div class="form-group">
                              <label>Email</label>
                              <input type="email" class="form-control" name="email" value="{{ $tenant->email }}" required>
                            </div>
                            <div class="form-group">
                              <label>No. HP</label>
                              <input type="text" class="form-control" name="phone" value="{{ $tenant->phone }}">
                            </div>
                            <div class="form-group">
                              <label>Pekerjaan</label>
                              <input type="text" class="form-control" name="occupation" value="{{ $tenant->occupation }}">
                            </div>
                            <div class="form-group">
                              <label>Alamat</label>
                              <textarea class="form-control" name="address" rows="3">{{ $tenant->address }}</textarea>
                            </div>
                            <div class="form-group">
                              <label>KTP (Upload ulang jika ingin mengubah)</label>
                              <input type="file" class="form-control-file" name="ktp" accept="image/*,.pdf">
                              @if($tenant->ktp_path)
                                <small class="form-text text-muted">KTP saat ini: <a href="{{ asset('storage/' . $tenant->ktp_path) }}" target="_blank">Lihat</a></small>
                              @endif
                            </div>
                            <div class="form-group">
                              <label>Password (kosongkan jika tidak diubah)</label>
                              <input type="password" class="form-control" name="password">
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
        <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="modal-header"><h5 class="modal-title">Tambah Penghuni</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Nama</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="form-group">
              <label>No. HP</label>
              <input type="text" class="form-control" name="phone">
            </div>
            <div class="form-group">
              <label>Pekerjaan</label>
              <input type="text" class="form-control" name="occupation">
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea class="form-control" name="address" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label>Upload KTP</label>
              <input type="file" class="form-control-file" name="ktp" accept="image/*,.pdf" required>
              <small class="form-text text-muted">Format: JPG, JPEG, PNG, PDF (Maksimal 2MB)</small>
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

@section('css')
<style>
.section-header-breadcrumb .dropdown-menu {
  min-width: 150px;
}
.section-header-breadcrumb .dropdown-item.active {
  background-color: #007bff;
  color: white;
}
.section-header-breadcrumb .dropdown-item:hover {
  background-color: #f8f9fa;
}
.section-header-breadcrumb .dropdown-item.active:hover {
  background-color: #0056b3;
}
</style>
@endsection

@section('js')
<script>
function loadPayments(userId) {
  const target = document.getElementById('payments-body-' + userId);
  target.innerHTML = 'Loading...';
  fetch('/admin/tenants/' + userId + '/payments')
    .then(r => r.json())
    .then(rows => {
      // Only show approved payments in history
      rows = Array.isArray(rows) ? rows.filter(p => p.status === 'approved') : [];
      if (!rows.length) { target.innerHTML = 'Belum ada pembayaran disetujui'; return; }
      let html = '<div class="table-responsive"><table class="table table-sm">';
      html += '<tr><th>Bulan</th><th>Tahun</th><th>Jumlah</th><th>Status</th><th>Tanggal</th></tr>';
      rows.forEach(p => {
        html += `<tr><td>${p.month}</td><td>${p.year}</td><td>${p.amount}</td><td>${p.status}</td><td>${p.paid_at ?? '-'}</td></tr>`;
      });
      html += '</table></div>';
      target.innerHTML = html;
    })
    .catch(() => target.innerHTML = 'Gagal memuat data');
}

function filterTenants(status) {
  const url = new URL(window.location);
  if (status === 'all') {
    url.searchParams.delete('status');
  } else {
    url.searchParams.set('status', status);
  }
  
  // Update dropdown button text
  const dropdownButton = document.getElementById('filterDropdown');
  const statusText = status === 'active' ? 'Aktif' : status === 'deactive' ? 'Nonaktif' : 'Semua';
  dropdownButton.innerHTML = `<i class="fas fa-filter"></i> ${statusText}`;
  
  window.location.href = url.toString();
}
</script>
@endsection


