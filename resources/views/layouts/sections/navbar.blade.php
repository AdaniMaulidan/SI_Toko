<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">

  <!-- Menu toggle for mobile -->
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
        <i class="icon-base bx bx-search icon-md"></i>
        <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2"
          placeholder="Cari..." aria-label="Cari..." />
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- Notifikasi Stok -->
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
          data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <span class="position-relative">
            <i class="icon-base bx bx-bell icon-md"></i>
            {{-- Badge jumlah notifikasi stok --}}
            <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h5 class="text-body mb-0 me-auto">Notifikasi</h5>
              <a href="javascript:void(0);" class="dropdown-notifications-all text-body"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Tandai semua dibaca">
                <i class="icon-base bx bx-envelope-open icon-md"></i>
              </a>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush">
              <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <span class="badge bg-label-warning rounded-pill p-2">
                      <i class="icon-base bx bx-package icon-sm"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-0">Stok Menipis</h6>
                    <small class="text-muted">5 produk stok di bawah minimum</small>
                    <small class="text-muted d-block">Hari ini</small>
                  </div>
                </div>
              </li>
              <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <span class="badge bg-label-danger rounded-pill p-2">
                      <i class="icon-base bx bx-error icon-sm"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-0">Stok Habis</h6>
                    <small class="text-muted">2 produk stok habis</small>
                    <small class="text-muted d-block">Hari ini</small>
                  </div>
                </div>
              </li>
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top">
            <a href="{{ route('stock.index') }}" class="dropdown-item d-flex justify-content-center text-primary p-3">
              Lihat Semua Notifikasi
            </a>
          </li>
        </ul>
      </li>
      <!-- /Notifikasi -->

      <!-- User Dropdown -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="User Avatar"
              class="w-px-40 h-auto rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="javascript:void(0);">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                      class="w-px-40 h-auto rounded-circle" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">Admin</h6>
                  <small class="text-muted">Administrator</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('settings.index') }}">
              <i class="icon-base bx bx-cog icon-md me-3"></i>
              <span>Pengaturan</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('auth.logout') }}"
              onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="icon-base bx bx-power-off icon-md me-3"></i>
              <span>Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" class="d-none">
              @csrf
            </form>
          </li>
        </ul>
      </li>
      <!-- /User -->

    </ul>
  </div>
</nav>
