@extends('layouts.app')

@section('title', 'Login - Absensi PTA Papua Barat')

@section('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .sidebar,
        .topbar {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
        }

        .content-wrapper {
            padding: 0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <div class="icon">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h2>Absensi PTA Papua Barat</h2>
                <p>Aplikasi Absensi PTA Papua Barat</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope"
                            style="position: absolute; left: 14px; top: 14px; color: var(--text-secondary);"></i>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                            placeholder="Masukkan email Anda" style="padding-left: 40px;">
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert" style="display: block;">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock"
                            style="position: absolute; left: 14px; top: 14px; color: var(--text-secondary);"></i>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi"
                            style="padding-left: 40px;">
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert" style="display: block;">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember" style="margin-bottom: 0;">
                            Ingat Saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; justify-content: center; padding: 14px; margin-top: 10px;">
                    Masuk <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                </button>
            </form>
        </div>
    </div>
@endsection
