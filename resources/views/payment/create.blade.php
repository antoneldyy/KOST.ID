@extends('user_layout.master')

@section('content')
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h1>Form Pembayaran</h1>
    </div>

    <div class="section-body">

      {{-- ✅ Pesan sukses --}}
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
          {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      @endif

      {{-- ✅ Pesan error umum (validasi Laravel) --}}
      @if ($errors->any())
        <div class="alert alert-danger">
          <strong>Terjadi kesalahan!</strong>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- ✅ Pesan error khusus jika user sudah membayar bulan ini --}}
      @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show">
          {{ session('error') }}
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      @endif

      <div class="card">
        <div class="card-header">
          <h4>Input Pembayaran Baru</h4>
        </div>

        <div class="card-body">
          <form action="{{ route('payment.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Ambil kamar dari user login --}}
            @php
                $room = \App\Models\Room::where('user_id', auth()->id())->first();
            @endphp

            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            @if(isset($payment))
              <input type="hidden" name="payment_id" value="{{ $payment->id }}">
            @endif

            @if ($room)
                <div class="form-group">
                  <label for="room_display">Kamar</label>
                  <input type="hidden" name="room_id" value="{{ $room->id }}">
                  <input type="text" id="room_display" class="form-control" value="Kamar No. {{ $room->number }}" readonly>
                </div>
            @else
                <div class="alert alert-warning">
                    Kamu belum memiliki kamar yang terdaftar.
                </div>
            @endif

            <div class="form-group">
              <label for="month">Bulan</label>
              <select name="month" id="month" class="form-control">
                @for ($m = 1; $m <= 12; $m++)
                  <option value="{{ $m }}" {{ isset($payment) && $payment->month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endfor
              </select>
            </div>

            <div class="form-group">
              <label for="year">Tahun</label>
              <input type="number" name="year" id="year" class="form-control" value="{{ isset($payment) ? $payment->year : date('Y') }}" required>
            </div>

            <div class="form-group">
              <label for="amount">Jumlah Pembayaran (Rp)</label>
              <input type="number" name="amount" id="amount" class="form-control" value="{{ isset($payment) ? $payment->amount : 800000 }}" readonly>
            </div>

            <div class="form-group">
              <label for="paid_at">Tanggal Pembayaran</label>
              <input type="date" name="paid_at" id="paid_at" class="form-control" value="{{ isset($payment) && $payment->paid_at ? $payment->paid_at->format('Y-m-d') : date('Y-m-d') }}">
            </div>

            <div class="form-group">
              <label for="proof_path">Bukti Pembayaran</label>
              <input type="file" name="proof_path" id="proof_path" class="form-control-file" accept="image/*" required>
              <small class="text-muted">Unggah foto/scan bukti transfer (JPG/PNG, max 2MB).</small>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Pembayaran</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
