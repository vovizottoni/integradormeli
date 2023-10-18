@extends('layouts.app')

@section('content')
<div class="auth-box bg-dark border-top border-secondary">

    <div id="recoverform" style="display: block !important;">  
        <div class="text-center">
            <span class="text-white">{{ __('Reset Password') }}</span>
        </div>
        <div class="row m-t-20">
            <!-- Form -->
            <form method="POST" action="{{ route('password.update') }}" class="col-12">
                @csrf                
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- email -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-danger text-white" id="basic-addon1"><i class="ti-email"></i></span>
                    </div>
                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus aria-describedby="basic-addon1" placeholder="{{ __('E-Mail Address') }}">

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror

                    
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-warning text-white" id="basic-addon2"><i class="ti-pencil"></i></span>
                    </div>

                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="{{ __('Password') }}">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-warning text-white" id="basic-addon2"><i class="ti-pencil"></i></span>
                    </div>

                    <input id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm Password') }}">
                                   
                </div>

                <!-- pwd -->
                <div class="row m-t-20 p-t-20 border-top border-secondary">
                    <div class="col-12">
                        <a href="{{ route('login') }}" class="btn btn-success" name="action">Back To Login</a>
                        
                        <button type="submit" class="btn btn-info float-right">{{ __('Reset Password') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
