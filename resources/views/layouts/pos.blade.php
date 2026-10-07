@php
$appName = config('app.name', 'SI Kasir');
@endphp
<!DOCTYPE html>
<html
  lang="id"
  class="layout-menu-fixed layout-compact"
  data-assets-path="{{ asset('assets') }}/"
  dir="ltr"
  data-skin="default"
  data-base-url="{{ url('/') }}"
  data-framework="laravel"
  data-bs-theme="light"
  data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>@yield('title', 'POS Kasir') | {{ $appName }}</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

  <!-- Fonts Icons -->
  @vite(['resources/assets/vendor/fonts/iconify/iconify.css'])

  <!-- Core CSS -->
  @vite(['resources/assets/vendor/scss/core.scss'])

  <!-- Vendor Styles -->
  @vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss'])

  @yield('vendor-style')

  <!-- Page Styles -->
  @yield('page-style')
  
  <style>
    /* Specific styles for full screen POS */
    body {
        overflow: hidden; /* Prevent scrolling on the main body */
    }
    .pos-layout {
        height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .pos-header {
        height: 60px;
        background: #fff;
        border-bottom: 1px solid #e1e3ea;
        display: flex;
        align-items: center;
        padding: 0 1.5rem;
        justify-content: space-between;
    }
    .pos-content {
        flex: 1;
        overflow: hidden; /* Let inner components handle scrolling */
        background: #f5f5f9;
        padding: 1rem;
    }
  </style>

  <!-- App CSS -->
  @vite(['resources/css/app.css'])

  <!-- Config & Helpers -->
  @vite(['resources/assets/vendor/js/helpers.js'])
  @vite(['resources/assets/js/config.js'])
</head>

<body>
  <div class="pos-layout">
    <!-- POS Topbar -->
    <header class="pos-header">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-icon btn-outline-secondary me-3" title="Kembali ke Dashboard">
                <i class="bx bx-arrow-back"></i>
            </a>
            <div class="app-brand-link gap-2">
                <span class="app-brand-logo demo">@include('_partials.macros', ['width' => '20'])</span>
                <span class="app-brand-text demo text-heading fw-bold fs-5 mb-0">POS - {{ $appName }}</span>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="d-none d-md-flex flex-column text-end me-2">
                <span class="fw-medium text-heading">Admin Kasir</span>
                <small class="text-muted" id="clock-display">00:00:00</small>
            </div>
            <div class="avatar avatar-online">
                <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pos-content">
        @yield('content')
    </main>
  </div>

  <!-- Vendor JS -->
  @vite([
    'resources/assets/vendor/libs/jquery/jquery.js',
    'resources/assets/vendor/libs/popper/popper.js',
    'resources/assets/vendor/js/bootstrap.js',
    'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
  ])

  @yield('vendor-script')

  <!-- Theme JS -->
  @vite(['resources/assets/js/main.js'])

  <!-- Page JS -->
  @yield('page-script')

  <script>
    // Simple Clock function
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const clockEl = document.getElementById('clock-display');
        if(clockEl) clockEl.innerText = time;
    }
    setInterval(updateClock, 1000);
    updateClock();
  </script>

  @stack('scripts')
</body>
</html>
