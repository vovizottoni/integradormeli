@extends('layouts.matrixLayout')

 

@section('content')  
       

    <div class="container-fluid">    

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route("usuarios.edita")}}" method="POST" class="form-horizontal">
                            @csrf
                            
                            <input type="hidden" name="id" value="{{ $usuario->id }}">       
                            
                                <div class="row">
                                    <div class="col-12 d-flex no-block align-items-center">
                                        <h5 class="card-title m-b-0"><b>Editar Usuário</b></h5>
                                                                                                                        
                                    
                                        <div class="ml-auto text-right">
                                            <a href="{{ route('usuarios') }}" class="btn btn-info ml-1"><i class="fas fa-arrow-left"></i> Voltar</a>                                            
                                            <button type="submit" class="btn btn-info ml-1">Editar</button>      
                                        </div>
                                    </div>
                                </div>
                                <br>
                            
                                <div class="form-group m-t-20">
                                    <label>Nome <small class="text-muted">(obrigatório)</small></label>
                                    <input type="text" name="nome" id="nome" class="form-control {{ $errors->has('nome')?'is-invalid':'' }}" value="{{ old('nome')?old('nome'):$usuario->name }}">

                                    @if($errors->has('nome'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('nome') }}</div>
                                    @endif                                         
                                </div>
                                <div class="form-group">
                                    <label>Email <small class="text-muted">(obrigatório) (Este campo será o login)</small></label>
                                    <input type="text" name="email" id="email" class="form-control {{ $errors->has('email')?'is-invalid':'' }}" value="{{ old('email')?old('email'):$usuario->email }}">

                                    @if($errors->has('email'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>Senha <small class="text-muted">(Preencha apenas caso queira trocar) (Entre 7 e 9 caracteres alfanuméricos)</small></label>
                                    <div class="row mb-3">
                                        <div class="col-lg-10">
                                            <input type="password" name="senha" id="senha" class="form-control {{ $errors->has('senha')?'is-invalid':'' }}" value="">
                                            
                                            @if($errors->has('senha'))
                                                <div class="invalid-feedback d-block">{{ $errors->first('senha') }}</div>
                                            @endif
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="showPassword" id="showPassword"  class="custom-control-input" >
                                                <label class="custom-control-label" for="showPassword">Mostrar senha</label>
                                            </div>                                            
                                        </div>        
                                    </div>    
                                </div>
                                <div class="form-group">
                                    <label>Tipo <small class="text-muted">(obrigatório)</small></label>                                                                        
                                    <select name="tipo" id="tipo" class="form-control select2 {{ $errors->has('tipo')?'is-invalid':'' }}" style="width: 100%; height:36px;">      
                                        <option value="">Selecione</option>  
                                        <option value="1" {{ ( ("1" == old('tipo')) || (!old('tipo') && $usuario->tipo == "1") )?'selected="selected"':'' }} >Administrador</option> 
                                        <option value="2" {{ ( ("2" == old('tipo')) || (!old('tipo') && $usuario->tipo == "2") )?'selected="selected"':'' }} >Auxiliar</option>                                                
                                    </select>  

                                    @if($errors->has('tipo'))  
                                        <div class="invalid-feedback d-block">{{ $errors->first('tipo') }}</div>
                                    @endif
                                </div>  

                                
                                <br>
                                <div class="row">
                                    <div class="col-12 d-flex no-block align-items-center">
                                        <div class="ml-auto text-right">
                                            <a href="{{ route('usuarios') }}" class="btn btn-info ml-1"><i class="fas fa-arrow-left"></i> Voltar</a>                                            
                                            <button type="submit" class="btn btn-info ml-1">Editar</button>      
                                        </div>
                                    </div>
                                </div>    
                                
                        </form>
                    </div>
                </div>            
            </div>
        </div>        
   

@endsection


@push('scripts') 
    <script type="text/javascript">     
                    
        $( document ).ready(function() {  
            
            //mostrar a senha
            $("#showPassword").click(function(){
                if('password' == $('#senha').attr('type')){
                    $('#senha').prop('type', 'text');
                }else{
                    $('#senha').prop('type', 'password');    
                }
            });    

            
        });                        
    </script>
@endpush


    

