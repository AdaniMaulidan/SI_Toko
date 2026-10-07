@extends('layouts.auth')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">

      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">@include('_partials.macros')</span>
              <span class="app-brand-text demo text-heading fw-bold">SI Kasir</span>
            </a>
          </div>
          <!-- /Logo -->

          <h4 class="mb-1">Selamat Datang! 👋</h4>
          <p class="mb-6">Silakan masuk ke akun Anda untuk mulai bertransaksi</p>

          {{-- Alert error / success --}}
          @if(session('error'))
          <div class="alert alert-danger alert-dismissible mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          @if(session('success'))
          <div class="alert alert-success alert-dismissible mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <form id="formAuthentication" class="mb-6" action="{{ route('auth.login.submit') }}" method="POST">
            @csrf

            <div class="mb-6">
              <label for="email" class="form-label">Email atau Username</label>
              <input
                type="text"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email atau username"
                autofocus />
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer" id="togglePassword">
                  <i class="icon-base bx bx-hide"></i>
                </span>
              </div>
              @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-8">
              <div class="d-flex justify-content-between align-items-center">
                <div class="form-check mb-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="remember-me"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }} />
                  <label class="form-check-label" for="remember-me">Ingat Saya</label>
                </div>
                <a href="{{ route('auth.forgot-password') }}">
                  <span>Lupa Password?</span>
                </a>
              </div>
            </div>

            <div class="mb-6">
              <button class="btn btn-primary d-grid w-100" type="submit">
                <span class="d-flex align-items-center justify-content-center gap-2">
                  <i class="bx bx-log-in"></i>
                  Masuk
                </span>
              </button>
            </div>
          </form>

          <div class="divider my-6">
            <div class="divider-text">Sistem Informasi Kasir Toko</div>
          </div>

          <p class="text-center mb-0">
            <small class="text-muted">Hubungi administrator jika belum memiliki akun</small>
          </p>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  // Toggle password visibility
  document.getElementById('togglePassword')?.addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      icon.classList.remove('bx-hide');
      icon.classList.add('bx-show');
    } else {
      passwordInput.type = 'password';
      icon.classList.remove('bx-show');
      icon.classList.add('bx-hide');
    }
  });
</script>
@endsection
