<div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="index.html">{{ config('app.name') }}</a>
          </div>
          <div class="sidebar-brand sidebar-brand-sm">
            <a href="index.html">St</a>
          </div>
          <ul class="sidebar-menu">
            <li>
              <a href="/user/userpage" class="nav-link" {{ (request()->is('user/userpage')) ? 'active' : '' }}>
                <span>Dashboard</span>
            </a>
            </li>
            <li>
              <a href="/user/payment/create" class="nav-link" {{ (request()->is('user/payment/create')) ? 'active' : '' }}>
                <span>Pembayaran</span>
            </a>
          </ul>

          <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="/logout" class="btn btn-danger btn-lg btn-block btn-icon-split">
                Logout
            </a>
          </div>        </aside>
      </div>
