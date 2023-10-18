@extends('layouts.matrixLayout')

 

@section('content')  
       

    <div class="container-fluid">  
        

        <!-- Flash Messages -->
        @if(Session::has('message'))            
            <div class="alert {{ Session::get('alert-class', 'alert-info') }}" role="alert">{{ Session::get('message') }}</div>
        @endif     


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route("perfil.edita")}}" method="POST" class="form-horizontal" enctype="multipart/form-data">                      
                            @csrf                                                        
                            
                            <div class="row">
                                <div class="col-12 d-flex no-block align-items-center">
                                    <h5 class="card-title m-b-0"><b>Editar Meu Perfil</b></h5>
                                                                                                                    
                                
                                    <div class="ml-auto text-right">                                        
                                        <button type="submit" class="btn btn-info ml-1">Editar</button>      
                                    </div>
                                </div>
                            </div>
                            <br>
                        
                            <div class="form-group m-t-20">
                                <label>Nome <small class="text-muted">(obrigatório)</small></label>
                                <input type="text" name="nome" id="nome" class="form-control {{ $errors->has('nome')?'is-invalid':'' }}" value="{{ old('nome')?old('nome'):Auth::user()->name }}">

                                @if($errors->has('nome'))
                                    <div class="invalid-feedback d-block">{{ $errors->first('nome') }}</div>
                                @endif                                         
                            </div>
                            <div class="form-group">
                                <label>Email <small class="text-muted">(obrigatório) (Este campo será o login)</small></label>
                                <input type="text" name="email" id="email" class="form-control {{ $errors->has('email')?'is-invalid':'' }}" value="{{ old('email')?old('email'):Auth::user()->email }}">

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
                                <label>Tipo: <small class="text-muted"></small></label>                                                                                                        
                                {{Auth::user()->tipo}} 
                            </div>  
                            
                            @if(!Auth::user()->imagem) 

                                <div class="form-group">
                                    <label>Imagem <small class="text-muted">(Extensões permitidas .JPEG .PNG) (Proporção sugerida: 300px/400px)</small></label>                                                                                                               
                                    <div class="input-group mb-3">
                                        <input type="file" name="fotoPerfil" id="fotoPerfil" class="form-control {{ $errors->has('fotoPerfil')?'is-invalid':'' }}">
                                        <label class="input-group-text" for="fotoPerfil">Selecione um arquivo</label>

                                        @if($errors->has('fotoPerfil'))
                                            <div class="invalid-feedback d-block">{{ $errors->first('fotoPerfil') }}</div> 
                                        @endif
                                    </div>

                                    @if($errors->has('imagem'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('imagem') }}</div>
                                    @endif
                                </div>
                            @else    
                                
                                <div class="p-2">   
                                    <img src="{{ url('storage/'.Auth::user()->imagem) }}" alt="usuário" class="rounded-circle" width="67">
                                </div>
                                <a href="{{ route('perfil.exclui.imagem') }}" class="btn btn-danger btn-xs">Excluir Imagem</a>
                                <br>
                                <br>
                                <br>
                            @endif  
                            
                            <div class="form-group m-t-20">
                                <label>Informações adicionais</label><br>
                                
                                <button type="button" class="btn btn-cyan btn-xs margin-5" data-toggle="modal" data-target="#modalInformacoesAdicionais">
                                    Preencher
                                </button>
                                
                            </div>

                            <br>
                            <div class="row">
                                <div class="col-12 d-flex no-block align-items-center">
                                    <div class="ml-auto text-right">                                        
                                        <button type="submit" class="btn btn-info ml-1">Editar</button>      
                                    </div>
                                </div>
                            </div>    
                                
                        </form>
                    </div>
                </div>            
            </div>
        </div>        
        
        <!-- Modais são definidas aqui (por padrão) -> Devem estar fora do <form> principal   -->
                        
            <div class="modal fade" id="modalInformacoesAdicionais" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Informações adicionais</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('perfil.editaInfo') }}" method="POST" class="form-horizontal">                                
                                @csrf
                                
                                <div class="form-group m-t-20">
                                    <label>CPF</label>
                                    <input type="text" name="cpf" id="cpf" class="form-control mask-cpf {{ $errors->has('cpf')?'is-invalid':'' }}" value="{{ old('cpf')?old('cpf'):$userInfo->cpf }}">
                                     
                                    @if($errors->has('cpf'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('cpf') }}</div>
                                    @endif                                         
                                </div>    

                                <div class="form-group m-t-20">
                                    <label>RG</label>
                                    <input type="text" name="rg" id="rg" class="form-control {{ $errors->has('rg')?'is-invalid':'' }}" value="{{ old('rg')?old('rg'):$userInfo->rg }}">
                                     
                                    @if($errors->has('rg'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('rg') }}</div>
                                    @endif                                         
                                </div>

                                <div class="form-group m-t-20">
                                    <label>Data de nascimento</label>
                                    <input type="text" name="data_nascimento" id="data_nascimento" class="form-control mask-data {{ $errors->has('data_nascimento')?'is-invalid':'' }}" value="{{ old('data_nascimento')?old('data_nascimento'):implode('/', array_reverse(explode('-', $userInfo->data_nascimento))) }}">
                                     
                                    @if($errors->has('data_nascimento'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('data_nascimento') }}</div>
                                    @endif                                         
                                </div>      
                                
                                <div class="form-group m-t-20">
                                    <label>Sexo</label>
                                    <select name="sexo" id="sexo" class="form-control {{ $errors->has('sexo')?'is-invalid':'' }}">
                                        <option value="">selecione</option>        
                                        <option value="m" {{ ( ("m" == old('sexo')) || (!old('sexo') && $userInfo->sexo == "m") )?'selected="selected"':'' }} >Masculino</option>
                                        <option value="f" {{ ( ("f" == old('sexo')) || (!old('sexo') && $userInfo->sexo == "f") )?'selected="selected"':'' }} >Feminino</option>
                                    </select>                                  
                                     
                                    @if($errors->has('sexo'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('sexo') }}</div>
                                    @endif                                         
                                </div>    

                                
                                <div class="form-group m-t-20">
                                    <label>Telefone Celular</label>
                                    <input type="text" name="telefoneCelular" id="telefoneCelular" class="form-control mask-celphone {{ $errors->has('telefoneCelular')?'is-invalid':'' }}" value="{{ old('telefoneCelular')?old('telefoneCelular'):$userInfo->telefoneCelular }}">
                                     
                                    @if($errors->has('telefoneCelular'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('telefoneCelular') }}</div>
                                    @endif                                         
                                </div> 
                                
                                <div class="form-group m-t-20">
                                    <label>Telefone Fixo</label>
                                    <input type="text" name="telefoneFixo" id="telefoneFixo" class="form-control mask-phonephone  {{ $errors->has('telefoneFixo')?'is-invalid':'' }}" value="{{ old('telefoneFixo')?old('telefoneFixo'):$userInfo->telefoneFixo }}">
                                     
                                    @if($errors->has('telefoneFixo')) 
                                        <div class="invalid-feedback d-block">{{ $errors->first('telefoneFixo') }}</div>
                                    @endif                                         
                                </div>   

                                <div class="form-group m-t-20">
                                    <label>CEP</label>
                                    <input type="text" name="cep" id="cep" class="form-control mask-cep {{ $errors->has('cep')?'is-invalid':'' }}" value="{{ old('cep')?old('cep'):$userInfo->cep }}">
                                     
                                    @if($errors->has('cep'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('cep') }}</div>
                                    @endif                                         
                                </div>  
                                
                                <div class="form-group m-t-20">
                                    <label>Estado</label>
                                    <select name="estado" id="estado" class="form-control {{ $errors->has('estado')?'is-invalid':'' }}">
                                        <option value="AC" {{ ( ("AC" == old('estado')) || (!old('estado') && $userInfo->estado == "AC") )?'selected="selected"':'' }} >Acre</option>
                                        <option value="AL" {{ ( ("AL" == old('estado')) || (!old('estado') && $userInfo->estado == "AL") )?'selected="selected"':'' }} >Alagoas</option>
                                        <option value="AP" {{ ( ("AP" == old('estado')) || (!old('estado') && $userInfo->estado == "AP") )?'selected="selected"':'' }} >Amapá</option>
                                        <option value="AM" {{ ( ("AM" == old('estado')) || (!old('estado') && $userInfo->estado == "AM") )?'selected="selected"':'' }} >Amazonas</option>
                                        <option value="BA" {{ ( ("BA" == old('estado')) || (!old('estado') && $userInfo->estado == "BA") )?'selected="selected"':'' }} >Bahia</option>
                                        <option value="CE" {{ ( ("CE" == old('estado')) || (!old('estado') && $userInfo->estado == "CE") )?'selected="selected"':'' }} >Ceará</option>
                                        <option value="DF" {{ ( ("DF" == old('estado')) || (!old('estado') && $userInfo->estado == "DF") )?'selected="selected"':'' }} >Distrito Federal</option>
                                        <option value="ES" {{ ( ("ES" == old('estado')) || (!old('estado') && $userInfo->estado == "ES") )?'selected="selected"':'' }} >Espírito Santo</option>
                                        <option value="GO" {{ ( ("GO" == old('estado')) || (!old('estado') && $userInfo->estado == "GO") )?'selected="selected"':'' }} >Goiás</option>
                                        <option value="MA" {{ ( ("MA" == old('estado')) || (!old('estado') && $userInfo->estado == "MA") )?'selected="selected"':'' }} >Maranhão</option>
                                        <option value="MT" {{ ( ("MT" == old('estado')) || (!old('estado') && $userInfo->estado == "MT") )?'selected="selected"':'' }} >Mato Grosso</option>
                                        <option value="MS" {{ ( ("MS" == old('estado')) || (!old('estado') && $userInfo->estado == "MS") )?'selected="selected"':'' }} >Mato Grosso do Sul</option>
                                        <option value="MG" {{ ( ("MG" == old('estado')) || (!old('estado') && $userInfo->estado == "MG") )?'selected="selected"':'' }} >Minas Gerais</option>
                                        <option value="PA" {{ ( ("PA" == old('estado')) || (!old('estado') && $userInfo->estado == "PA") )?'selected="selected"':'' }} >Pará</option>
                                        <option value="PB" {{ ( ("PB" == old('estado')) || (!old('estado') && $userInfo->estado == "PB") )?'selected="selected"':'' }} >Paraíba</option>
                                        <option value="PR" {{ ( ("PR" == old('estado')) || (!old('estado') && $userInfo->estado == "PR") )?'selected="selected"':'' }} >Paraná</option>
                                        <option value="PE" {{ ( ("PE" == old('estado')) || (!old('estado') && $userInfo->estado == "PE") )?'selected="selected"':'' }} >Pernambuco</option>
                                        <option value="PI" {{ ( ("PI" == old('estado')) || (!old('estado') && $userInfo->estado == "PI") )?'selected="selected"':'' }} >Piauí</option>
                                        <option value="RJ" {{ ( ("RJ" == old('estado')) || (!old('estado') && $userInfo->estado == "RJ") )?'selected="selected"':'' }} >Rio de Janeiro</option>
                                        <option value="RN" {{ ( ("RN" == old('estado')) || (!old('estado') && $userInfo->estado == "RN") )?'selected="selected"':'' }} >Rio Grande do Norte</option>
                                        <option value="RS" {{ ( ("RS" == old('estado')) || (!old('estado') && $userInfo->estado == "RS") )?'selected="selected"':'' }} >Rio Grande do Sul</option>
                                        <option value="RO" {{ ( ("RO" == old('estado')) || (!old('estado') && $userInfo->estado == "RO") )?'selected="selected"':'' }} >Rondônia</option>
                                        <option value="RR" {{ ( ("RR" == old('estado')) || (!old('estado') && $userInfo->estado == "RR") )?'selected="selected"':'' }} >Roraima</option>
                                        <option value="SC" {{ ( ("SC" == old('estado')) || (!old('estado') && $userInfo->estado == "SC") )?'selected="selected"':'' }} >Santa Catarina</option>
                                        <option value="SP" {{ ( ("SP" == old('estado')) || (!old('estado') && $userInfo->estado == "SP") )?'selected="selected"':'' }} >São Paulo</option>
                                        <option value="SE" {{ ( ("SE" == old('estado')) || (!old('estado') && $userInfo->estado == "SE") )?'selected="selected"':'' }} >Sergipe</option>
                                        <option value="TO" {{ ( ("TO" == old('estado')) || (!old('estado') && $userInfo->estado == "TO") )?'selected="selected"':'' }} >Tocantins</option>
                                    </select>                                    

                                    @if($errors->has('estado'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('estado') }}</div>
                                    @endif                                         
                                </div>  

                                <div class="form-group m-t-20">
                                    <label>Cidade</label>
                                    <input type="text" name="cidade" id="cidade" class="form-control {{ $errors->has('cidade')?'is-invalid':'' }}" value="{{ old('cidade')?old('cidade'):$userInfo->cidade }}">
                                     
                                    @if($errors->has('cidade'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('cidade') }}</div>
                                    @endif                                         
                                </div>   

                                <div class="form-group m-t-20">
                                    <label>Bairro</label>
                                    <input type="text" name="bairro" id="bairro" class="form-control {{ $errors->has('bairro')?'is-invalid':'' }}" value="{{ old('bairro')?old('bairro'):$userInfo->bairro }}">
                                     
                                    @if($errors->has('bairro'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('bairro') }}</div>
                                    @endif                                         
                                </div>   

                                <div class="form-group m-t-20">
                                    <label>Logradouro</label>
                                    <input type="text" name="logradouro" id="logradouro" class="form-control {{ $errors->has('logradouro')?'is-invalid':'' }}" value="{{ old('logradouro')?old('logradouro'):$userInfo->logradouro }}">
                                     
                                    @if($errors->has('logradouro'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('logradouro') }}</div>
                                    @endif                                         
                                </div> 

                                <div class="form-group m-t-20">
                                    <label>Número</label>
                                    <input type="text" name="numero" id="numero" class="form-control {{ $errors->has('numero')?'is-invalid':'' }}" value="{{ old('numero')?old('numero'):$userInfo->numero }}">
                                     
                                    @if($errors->has('numero'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('numero') }}</div>
                                    @endif                                         
                                </div> 

                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-info btn-primary">Editar</button>
                        </div>
                        </form>                       
                    </div>
                </div>
            </div>
            
        
        <!-- Modais -->

@endsection


@push('scripts') 
    <script type="text/javascript">     
                    
        $( document ).ready(function() {               
            
            
            // Se houve algum erro de validate da modal N, abre modal N automaticamente 
            @if($errors->has('cpf') || $errors->has('rg') || $errors->has('data_nascimento') /* add more... */ )

                $('#modalInformacoesAdicionais').modal('show');     

            @endif
            //Repete este código para outras modais XYZ ...
            

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


    

