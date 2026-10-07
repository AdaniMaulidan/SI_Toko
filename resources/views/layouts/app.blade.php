@php
/*
|--------------------------------------------------------------------------
| SI Kasir - Toko App Config
|--------------------------------------------------------------------------
*/
$appName    = config('app.name', 'SI Kasir');
$appVersion = '1.0.0';
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

  <title>@yield('title', 'Dashboard') | {{ $appName }}</title>
  <meta name="description" content="Sistem Informasi Kasir Toko" />

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

  <!-- App CSS -->
  @vite(['resources/css/app.css'])

  <!-- Config & Helpers (in head for early init) -->
  @vite(['resources/assets/vendor/js/helpers.js'])
  @vite(['resources/assets/js/config.js'])
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- Sidebar Menu -->
      @include('layouts.sections.sidebar')

      <!-- Layout page -->
      <div class="layout-page">

        <!-- Navbar -->
        @include('layouts.sections.navbar')

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            @yield('content')
          </div>

          <!-- Footer -->
          @include('layouts.sections.footer')

          <div class="content-backdrop fade"></div>
        </div>
        <!--/ Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    <!-- Drag Target -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- Vendor JS -->
  @vite([
    'resources/assets/vendor/libs/jquery/jquery.js',
    'resources/assets/vendor/libs/popper/popper.js',
    'resources/assets/vendor/js/bootstrap.js',
    'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
    'resources/assets/vendor/js/menu.js',
  ])

  @yield('vendor-script')

  <!-- Theme JS -->
  @vite(['resources/assets/js/main.js'])

  <!-- Page JS -->
  @yield('page-script')

  <!-- App JS -->
  @vite(['resources/js/app.js'])

  @stack('scripts')
</body>
</html>
