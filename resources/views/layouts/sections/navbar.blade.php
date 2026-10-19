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
      <form action="{{ route('products.index') }}" method="GET" class="nav-item d-flex align-items-center mb-0">
        <i class="icon-base bx bx-search icon-md text-muted"></i>
        <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2"
          placeholder="Cari produk / barcode... (Enter)" aria-label="Cari..." value="{{ request('search') }}" />
      </form>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- Notifikasi Stok Dinamis -->
      @php
        $totalAlerts  = $navbarTotalAlerts ?? 0;
        $lowCount     = $navbarLowStockCount ?? 0;
        $outCount     = $navbarOutOfStockCount ?? 0;
        $criticalList = $navbarCriticalProducts ?? collect();
      @endphp
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
          data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Peringatan Stok Toko">
          <span class="position-relative">
            <i class="icon-base bx bx-bell icon-md"></i>
            @if($totalAlerts > 0)
              <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
            @endif
          </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0 shadow-lg" style="min-width: 320px;">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="text-body mb-0 me-auto fw-bold">Peringatan Stok</h6>
              @if($totalAlerts > 0)
                <span class="badge bg-label-danger rounded-pill">{{ $totalAlerts }} Perlu Perhatian</span>
              @else
                <span class="badge bg-label-success rounded-pill">Stok Aman</span>
              @endif
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush">
              @if($outCount > 0)
              <li class="list-group-item list-group-item-action dropdown-notifications-item py-2">
                <a href="{{ route('stock.index', ['status' => 'empty']) }}" class="d-flex align-items-center text-decoration-none">
                  <div class="flex-shrink-0 me-3">
                    <span class="badge bg-label-danger rounded-pill p-2">
                      <i class="icon-base bx bx-x-circle icon-sm"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-0 text-danger fw-bold">Stok Habis (0 Item)</h6>
                    <small class="text-muted">{{ $outCount }} produk perlu restock segera</small>
                  </div>
                </a>
              </li>
              @endif

              @if($lowCount > 0)
              <li class="list-group-item list-group-item-action dropdown-notifications-item py-2">
                <a href="{{ route('stock.index', ['status' => 'low']) }}" class="d-flex align-items-center text-decoration-none">
                  <div class="flex-shrink-0 me-3">
                    <span class="badge bg-label-warning rounded-pill p-2">
                      <i class="icon-base bx bx-error icon-sm"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-0 text-warning fw-bold">Stok Menipis</h6>
                    <small class="text-muted">{{ $lowCount }} produk di bawah batas aman</small>
                  </div>
                </a>
              </li>
              @endif

              @forelse($criticalList as $crit)
              <li class="list-group-item list-group-item-action dropdown-notifications-item py-2">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="text-truncate me-2" style="max-width: 190px;" title="{{ $crit->name }}">
                    <span class="small fw-semibold d-block text-truncate">{{ $crit->name }}</span>
                    <small class="text-muted font-monospace">{{ $crit->barcode }}</small>
                  </div>
                  @if($crit->stock <= 0)
                    <span class="badge bg-danger">Habis</span>
                  @else
                    <span class="badge bg-warning text-dark">{{ $crit->stock }} {{ $crit->unit?->symbol ?? 'Pcs' }}</span>
                  @endif
                </div>
              </li>
              @empty
                @if($totalAlerts == 0)
                <li class="list-group-item text-center py-4 text-muted">
                  <i class="bx bx-check-circle text-success fs-3 d-block mb-1"></i>
                  <small>Semua stok produk berada di batas aman.</small>
                </li>
                @endif
              @endforelse
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top">
            <a href="{{ route('stock.index') }}" class="dropdown-item d-flex justify-content-center text-primary p-3 fw-semibold">
              Lihat Manajemen Stok <i class="bx bx-right-arrow-alt ms-1"></i>
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
            <img src="{{ auth()->user()?->image_url ?? asset('assets/img/avatars/1.png') }}" alt="User Avatar"
              class="w-px-40 h-px-40 rounded-circle object-fit-cover border" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li>
            <a class="dropdown-item" href="{{ route('profile.index') }}">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ auth()->user()?->image_url ?? asset('assets/img/avatars/1.png') }}" alt="User Avatar"
                      class="w-px-40 h-px-40 rounded-circle object-fit-cover border" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0 fw-semibold">{{ auth()->user()->name ?? "Pengguna" }}</h6>
                  <small class="text-muted">{{ ucfirst(auth()->user()->role ?? "admin") }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('profile.index') }}">
              <i class="icon-base bx bx-user icon-md me-3"></i>
              <span>Profil Saya</span>
            </a>
          </li>
          @if(auth()->user()?->isAdmin())
          <li>
            <a class="dropdown-item" href="{{ route('settings.index') }}">
              <i class="icon-base bx bx-cog icon-md me-3"></i>
              <span>Pengaturan Toko</span>
            </a>
          </li>
          @endif
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
