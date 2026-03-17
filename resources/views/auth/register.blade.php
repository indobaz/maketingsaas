@extends('layouts.auth')

@section('content')
    @php($title = 'Register')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card shadow-sm my-5">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="fw-bold text-white" style="font-size: 28px; letter-spacing: .2px;">
                            Pulsify
                        </div>
                        <div class="text-white-50" style="font-size: 14px;">
                            Create your account
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/register') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                required
                            >
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Work Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone (optional)</label>
                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                            >
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                                required
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="company_name" class="form-label">Company Name (optional)</label>
                            <input
                                id="company_name"
                                name="company_name"
                                type="text"
                                class="form-control @error('company_name') is-invalid @enderror"
                                value="{{ old('company_name') }}"
                                placeholder="You can add this later"
                            >
                            @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Create account
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted">Already have an account?</span>
                        <a href="{{ url('/login') }}" class="text-decoration-none">
                            Sign in
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

