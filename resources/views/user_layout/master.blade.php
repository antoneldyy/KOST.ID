<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>iKost </title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/bootstrap/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/fontawesome/css/all.min.css')}}">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/jqvmap/dist/jqvmap.min.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/summernote/summernote-bs4.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css')}}">

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{asset('stisla/assets/css/style.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/css/components.css')}}">
  
  <!-- Custom CSS -->
  <style>
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      font-size: 10px;
      padding: 2px 5px;
    }
    .dropdown-item.unread {
      background-color: #f8f9fa;
      font-weight: 500;
    }
    .dropdown-item.unread .dropdown-item-desc {
      color: #495057;
    }
  </style>
<!-- Start GA -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-94034622-3');
</script>
<!-- /END GA --></head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      {{-- navbar --}}
      @include('user_layout._navbar')

      {{-- sidebar --}}
      @include('user_layout._sidebar')

      <!-- Main Content -->
      @yield('content')

      <footer class="main-footer">
        <div class="footer-left">
          Copyright &copy; iKostDev 2025</a>
        </div>
        <div class="footer-right">

        </div>
      </footer>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="{{asset('stisla/assets/modules/jquery.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/popper.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/tooltip.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/bootstrap/js/bootstrap.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/nicescroll/jquery.nicescroll.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/moment.min.js')}}"></script>
  <script src="{{asset('stisla/assets/js/stisla.js')}}"></script>

  <!-- JS Libraies -->
  @yield('js')

  <!-- Page Specific JS File -->
  <script src="{{asset('stisla/assets/js/page/index.js')}}"></script>

  <!-- Template JS File -->
  <script src="{{asset('stisla/assets/js/scripts.js')}}"></script>
  <script src="{{asset('stisla/assets/js/custom.js')}}"></script>

  <!-- Notification Scripts -->
  <script>
  function markAllUserNotificationsRead(e) {
    if (e) e.preventDefault();
    // remove badge
    const badge = document.querySelector('.notification-badge');
    if (badge) badge.remove();
    // remove unread styling in dropdown
    document.querySelectorAll('#notif-list .dropdown-item').forEach(item => item.classList.remove('unread'));
    // Optionally, send a request to server to persist (not implemented for payments)
  }
  function markAsRead(notificationId) {
    fetch('/admin/notifications/' + notificationId + '/read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update notification badge
        updateNotificationBadge();
        // Remove unread class
        event.target.closest('.dropdown-item').classList.remove('unread');
      }
    })
    .catch(error => console.error('Error:', error));
  }

  function markAllAsRead() {
    fetch('/admin/notifications/mark-all-read', {
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
      }
    })
    .catch(error => console.error('Error:', error));
  }

  function updateNotificationBadge() {
    fetch('/admin/notifications/unread-count')
      .then(response => response.json())
      .then(data => {
        const badge = document.querySelector('.notification-badge');
        if (data.count > 0) {
          if (badge) {
            badge.textContent = data.count;
          } else {
            const bell = document.querySelector('.notification-toggle');
            bell.innerHTML = '<i class="far fa-bell"></i><span class="badge badge-danger notification-badge">' + data.count + '</span>';
          }
        } else {
          const bell = document.querySelector('.notification-toggle');
          bell.innerHTML = '<i class="far fa-bell"></i>';
        }
      })
      .catch(error => console.error('Error:', error));
  }

  // Update notification badge every 30 seconds
  setInterval(updateNotificationBadge, 30000);
  </script>
</body>
</html>
