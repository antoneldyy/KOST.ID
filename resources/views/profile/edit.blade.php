@extends('layout.master')

@section('content')
  <div class="main-content">
    <section class="section">
      <div class="section-header">
        <h1>Profil</h1>
      </div>

      <div class="card">
        <div class="card-body">
          <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="form-group">
              <label>Role</label>
              <input type="text" value="{{ auth()->user()->role }}" class="form-control" disabled>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>
            <div class="form-group">
              <label>No. Telepon</label>
              <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
            </div>
            <button class="btn btn-primary">Simpan</button>
          </form>
        </div>
      </div>
    </section>
  </div>
@endsection


