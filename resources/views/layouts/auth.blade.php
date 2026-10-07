<!DOCTYPE html>
<html
  lang="id"
  data-assets-path="{{ asset('assets') }}/"
  data-base-url="{{ url('/') }}"
  data-bs-theme="light">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>@yield('title', 'Login') | {{ config('app.name', 'SI Kasir') }}</title>
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

  @yield('vendor-style')

  <!-- Page Styles -->
  @yield('page-style')

  <!-- App CSS -->
  @vite(['resources/css/app.css'])

  <!-- Config & Helpers -->
  @vite(['resources/assets/vendor/js/helpers.js'])
  @vite(['resources/assets/js/config.js'])
</head>

<body>
  @yield('content')

  <!-- Vendor JS -->
  @vite([
    'resources/assets/vendor/libs/jquery/jquery.js',
    'resources/assets/vendor/libs/popper/popper.js',
    'resources/assets/vendor/js/bootstrap.js',
  ])

  @yield('vendor-script')

  <!-- Theme JS -->
  @vite(['resources/assets/js/main.js'])

  <!-- Page JS -->
  @yield('page-script')

  <!-- App JS -->
  @vite(['resources/js/app.js'])
</body>
</html>
