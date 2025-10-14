<nav class="navbar navbar-expand-lg main-navbar">
    <ul class="navbar-nav mr-auto">
        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
    </ul>

    <ul class="navbar-nav navbar-right">
        {{-- 🔔 Notifikasi --}}
        <li class="dropdown dropdown-list-toggle">
            <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg">
                <i class="far fa-bell"></i>
                @php
                    use App\Models\Notification;
                    $unreadNotifications = Notification::where('is_read', false)->count();
                @endphp
                @if($unreadNotifications > 0)
                    <span class="badge badge-danger notification-badge">{{ $unreadNotifications }}</span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-list dropdown-menu-right">
                <div class="dropdown-header">Notifikasi</div>
                <div class="dropdown-list-content dropdown-list-icons" id="notif-list">
                    @php
                        $notifications = Notification::orderBy('created_at', 'desc')->limit(5)->get();
                    @endphp
                    @forelse($notifications as $notification)
                        <a href="{{ $notification->type == 'payment_upload' ? '/admin/rooms' : '/admin/tenants' }}"
                           class="dropdown-item {{ $notification->is_read ? '' : 'unread' }}"
                           onclick="markAsRead({{ $notification->id }})">
                            <div class="dropdown-item-icon bg-{{ $notification->type == 'payment_upload' ? 'primary' : 'info' }} text-white">
                                <i class="fas fa-{{ $notification->type == 'payment_upload' ? 'receipt' : 'user' }}"></i>
                            </div>
                            <div class="dropdown-item-desc">
                                {{ $notification->title }}
                                <div class="time text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="dropdown-item text-center text-muted">
                            Tidak ada notifikasi
                        </div>
                    @endforelse

                    @if($notifications->count() > 0)
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-center text-primary" onclick="markAllAsRead()">
                            Tandai semua sudah dibaca
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

            </div>
        </li>
    </ul>
</nav>

@section('js')
<script>
    function markAsRead(notificationId) {
        fetch('/admin/notifications/' + notificationId + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            const badge = document.querySelector('.notification-badge');
            if(badge) {
                let count = parseInt(badge.textContent) - 1;
                if(count <= 0) badge.remove();
                else badge.textContent = count;
            }
        }).catch(err => console.error(err));
    }

    function markAllAsRead() {
        fetch('/admin/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            const badge = document.querySelector('.notification-badge');
            if(badge) badge.remove();
            document.querySelectorAll('.dropdown-item.unread').forEach(item => item.classList.remove('unread'));
        }).catch(err => console.error(err));
    }
</script>
@endsection
