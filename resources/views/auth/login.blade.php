@extends('layouts.app') 

@section('content')
<div class="auth-box bg-dark border-top border-secondary">
    
    <div id="loginform">
        <div class="text-center p-t-20 p-b-20">
            <span class="db"><img src="{{ asset('assets/images/logo.png')}}" alt="logo" /></span>
        </div>
        <br>
        <!-- Form -->
        <form method="POST" action="{{ route('login') }}" class="form-horizontal m-t-20" id="loginform">
            @csrf
            <div class="row p-b-30">
                <div class="col-12">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-success text-white" id="basic-addon1"><i class="ti-user"></i></span>
                        </div>

                        <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" aria-label="Email" aria-describedby="basic-addon1" required autocomplete="email" autofocus>

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

                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" aria-label="Password" aria-describedby="basic-addon1">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror    
                    
                    
                    </div>
                    <br>
                </div>
            </div>
            <div class="row border-top border-secondary">
                <div class="col-12">
                    <div class="form-group">
                        <div class="p-t-20">
                             <br>    
                            <button type="submit" class="btn btn-success float-right">
                                {{ __('Login') }}
                            </button>

                            @if (Route::has('password.request'))
                                <a class="btn btn-info" href="{{ route('password.request') }}"><i class="fa fa-lock m-r-5"></i>
                                    Esqueceu sua senha?
                                </a>
                            @endif                           
                            
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
        

</div>
@endsection






