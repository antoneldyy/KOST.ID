<nav class="navbar navbar-expand-lg main-navbar sticky-top">
  <ul class="navbar-nav mr-auto">
    <li>
      <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
        <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>

  <ul class="navbar-nav navbar-right">
    {{-- 🔔 Notifikasi Pembayaran --}}
    <li class="dropdown dropdown-list-toggle">
      <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg">
        <i class="far fa-bell"></i>

        @php
          use App\Models\Payment;

          $userId = auth()->id();

          // Ambil 5 pembayaran terbaru user
          $notifications = Payment::where('user_id', $userId)
              ->orderBy('created_at', 'desc')
              ->limit(5)
              ->get();

          // Hitung pembayaran yang belum disetujui (menunggu acc)
          $pendingCount = $notifications->whereNull('approved_at')->count();
        @endphp

        @if($pendingCount > 0)
          <span class="badge badge-danger notification-badge">{{ $pendingCount }}</span>
        @endif
      </a>

      <div class="dropdown-menu dropdown-list dropdown-menu-right">
        <div class="dropdown-header">Notifikasi</div>
        <div class="dropdown-list-content dropdown-list-icons" id="notif-list">
          @php
            $monthNames = [
              1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
              7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
          @endphp

          @forelse($notifications as $payment)
            @php
              $monthName = $monthNames[$payment->month] ?? $payment->month;
              $isApproved = !is_null($payment->approved_at);
            @endphp

            <a href="{{ route('payment.index') }}"
               class="dropdown-item">
              <div class="dropdown-item-icon bg-{{ $isApproved ? 'success' : 'warning' }} text-white">
                <i class="fas fa-receipt"></i>
              </div>
              <div class="dropdown-item-desc">
                @if(!$isApproved)
                  Pembayaran bulan {{ $monthName }} {{ $payment->year }} menunggu konfirmasi admin.
                @else
                  Pembayaran bulan {{ $monthName }} {{ $payment->year }} telah disetujui.
                @endif
                <div class="time text-muted small">{{ $payment->created_at->diffForHumans() }}</div>
              </div>
            </a>
          @empty
            <div class="dropdown-item text-center text-muted">
              Tidak ada notifikasi
            </div>
          @endforelse

          @if($notifications->count() > 0)
            <div class="dropdown-divider"></div>
            <a href="{{ route('payment.index') }}" class="dropdown-item text-center text-primary">
              Lihat semua pembayaran
            </a>
          @endif
        </div>
      </div>
    </li>

    {{-- 👤 Menu user --}}
    <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
        <img alt="image" src="{{ asset('stisla/assets/img/avatar/avatar-1.png') }}" class="rounded-circle mr-1">
        <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <div class="dropdown-title">Logged in</div>
        <a href="{{ route('profile.edit') }}" class="dropdown-item has-icon">
          <i class="far fa-user"></i> Profile
        </a>
        <a href="{{ route('activities.index') }}" class="dropdown-item has-icon">
          <i class="fas fa-bolt"></i> Activities
        </a>
        <div class="dropdown-divider"></div>

      </div>
    </li>
  </ul>
</nav>
