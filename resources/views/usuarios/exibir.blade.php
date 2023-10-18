@extends('layouts.matrixLayout')


    

@section('content')  

    <div class="container-fluid">        


        <!-- Flash Messages -->
        @if(Session::has('message'))            
            <div class="alert {{ Session::get('alert-class', 'alert-info') }}" role="alert">{{ Session::get('message') }}</div>
        @endif     


        <!-- Filtros -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <h5 class="card-title"><b>Filtros</b></h5> 
                        
                        <form action="{{ route('usuarios.filtrar') }}" method="POST" class="form-horizontal">     
                            @csrf
                            <div class="form-group row">
                                <label for="" class="col-sm-1 text-right control-label col-form-label">ID</label>    
                                <div class="col-sm-3"><input type="text" name="id" class="form-control" value="{{isset($filtros['id'])?$filtros['id']:"" }}"></div>                                                               

                                <label for="" class="col-sm-1 text-right control-label col-form-label">Nome</label>
                                <div class="col-sm-3"><input type="text" name="nome" class="form-control" value="{{ isset($filtros['nome'])?$filtros['nome']:"" }}" /></div>
                                
                                <label for="" class="col-sm-1 text-right control-label col-form-label">Email</label>
                                <div class="col-sm-3"><input type="text" name="email" class="form-control" value="{{ isset($filtros['email'])?$filtros['email']:"" }}" /></div>
                            </div> 
                            <a href="{{ route('usuarios.limparfiltro') }}" class="btn btn-sm btn-danger float-right ml-1"><i class="fas fa-eraser"></i> Limpar Filtro</a> 
                            <button type="submit" class="btn btn-sm btn-success float-right"><i class="fas fa-filter"></i> Buscar</button>                                 
                        </form>   
                    </div>                                                             
                </div>
            </div>
        </div>

        <!-- dados -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <!-- Título da grid (esquerda) e  botão (direita) -->
                        <div class="row">
                            <div class="col-12 d-flex no-block align-items-center">
                                <h5 class="card-title m-b-0"><b>Usuários</b></h5> 

                            
                                <div class="ml-auto text-right">
                                    <a href="{{ route('usuarios.cadastrar') }}" class="btn btn-cyan ml-1 btn-sm" ><i class="fas fa-plus"></i> Cadastrar</a>      
                                </div>
                            </div>
                        </div>
                        <br>

                                                  
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col"><strong>ID</strong></th>
                                    <th scope="col"><strong>Nome</strong></th>
                                    <th scope="col"><strong>Email</strong></th>
                                    <th scope="col"><strong>Tipo</strong></th>
                                    <th scope="col"><strong>Ações</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($usuarios)    
                                    @foreach ($usuarios as $item)
                                        <tr>
                                            <th scope="row">{{$item->id}}</th>
                                            <td>{{$item->name}}</td>
                                            <td>{{$item->email}}</td>
                                            <td>{{$item->tipo}}</td>
                                            <td><a href="{{ route('usuarios.editar', ['id' => $item->id] ) }}" class="btn btn-cyan btn-xs">Editar</a>&nbsp;<a href="#" data-href="{{ route('usuarios.excluir', ['id' => $item->id] ) }}" data-toggle="modal" data-target="#confirm-delete" class="btn btn-danger btn-xs">Excluir</a></td>
                                        </tr>
                                    @endforeach
                                @else   
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhum registro encontrado</th>                                     
                                    </tr> 
                                @endif
                            </tbody>                            
                        </table>
                        {{ $usuarios->appends($filtros)->links() }} 
                    </div> 
                    
                   <div class="dataTables_info p-4" id="zero_config_info" role="status" aria-live="polite">Exibindo de <b>{{ $usuarios->firstItem() }}</b> até <b>{{ $usuarios->lastItem() }}</b>&nbsp;  de um total de <b>{{$usuarios->total()}}</b> itens</div>
                </div>
            </div>    
        </div>     
    </div>       
    
    <!-- Modal de confirmação de exclusão -->
    @include('layouts.pedacos.modal_confirma_exclusao')    

@endsection

      
@push('scripts') 
    <script type="text/javascript">                         
        $( document ).ready(function() {              
            
            //script da modal de confirmação de exclusão
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });            

        });                        
    </script>
@endpush