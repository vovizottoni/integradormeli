@extends('layouts.app')

@section('content')
<div class="auth-box bg-dark border-top border-secondary">

    <div id="recoverform" style="display: block !important;">  
        <div class="text-center">
            <span class="text-white">Resetar senha</span>
        </div>
        @if (session('status'))            
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
         
        <br>
        <div class="row m-t-20">
            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}" class="col-12">
                @csrf                             

                <!-- email -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-danger text-white" id="basic-addon1"><i class="ti-email"></i></span>
                    </div>                    

                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="E-mail">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    
                </div>                              
                <br>

                <!-- pwd -->
                <div class="row m-t-20 p-t-20 border-top border-secondary">                    
                    <div class="col-12">
                        <br>
                        <a href="{{ route('login') }}" class="btn btn-success" name="action">Voltar ao login</a>
                                                
                        <button type="submit" class="btn btn-info float-right">Enviar link "resetar senha"</button>  
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
