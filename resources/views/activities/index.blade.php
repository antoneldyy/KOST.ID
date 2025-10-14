@extends('layout.master')

@section('content')
  <div class="main-content">
    <section class="section">
      <div class="section-header">
        <h1>Aktivitas</h1>
      </div>
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Admin</th>
                <th>Aksi</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody>
              @foreach($activities as $a)
              <tr>
                <td>{{ $a->created_at }}</td>
                <td>{{ optional($a->user)->name }}</td>
                <td>{{ $a->action }}</td>
                <td><pre class="mb-0">{{ json_encode($a->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{ $activities->links() }}
        </div>
      </div>
    </section>
  </div>
@endsection


