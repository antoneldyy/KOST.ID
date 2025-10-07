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
              <a href="/dashboard" class="nav-link" {{ (request()->is('dashboard')) ? 'active' : '' }}>
                <span>Dashboard</span>
            </a>
            </li>
            <li class="dropdown">
              <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><span>Layout</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="layout-default.html">Default Layout</a></li>
                <li><a class="nav-link" href="layout-transparent.html">Transparent Sidebar</a></li>
                <li><a class="nav-link" href="layout-top-navigation.html">Top Navigation</a></li>
              </ul>
            </li>
            <li><a class="nav-link" href="blank.html"> <span>Blank Page</span></a></li>
            <li><a class="nav-link" href="credits.html"></i> <span>Credits</span></a></li>
          </ul>

          <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="/logout" class="btn btn-danger btn-lg btn-block btn-icon-split">
                Logout
            </a>
          </div>        </aside>
      </div>
