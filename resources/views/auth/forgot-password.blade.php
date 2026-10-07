@extends('layouts.auth')

@section('title', 'Lupa Password')

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

          <h4 class="mb-1">Lupa Password? 🔒</h4>
          <p class="mb-6">Masukkan email Anda dan kami akan mengirimkan instruksi untuk mereset password</p>

          {{-- Alert success --}}
          @if(session('status'))
          <div class="alert alert-success alert-dismissible mb-4" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          {{-- Alert error --}}
          @if(session('error'))
          <div class="alert alert-danger alert-dismissible mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <form id="formAuthentication" class="mb-6" action="{{ route('auth.forgot-password.submit') }}" method="POST">
            @csrf

            <div class="mb-6">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email Anda"
                autofocus />
              @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button class="btn btn-primary d-grid w-100" type="submit">
              <span class="d-flex align-items-center justify-content-center gap-2">
                <i class="bx bx-mail-send"></i>
                Kirim Link Reset
              </span>
            </button>
          </form>

          <div class="text-center">
            <a href="{{ route('auth.login') }}" class="d-flex align-items-center justify-content-center gap-1">
              <i class="icon-base bx bx-chevron-left"></i>
              Kembali ke Login
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
