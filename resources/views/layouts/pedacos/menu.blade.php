    <!-- Sidebar navigation--> 
    <nav class="sidebar-nav">
        <ul id="sidebarnav" class="p-t-30">
            <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-account"></i><span class="hide-menu">Usuários </span></a>
                <ul aria-expanded="false" class="collapse  first-level">
                    <li class="sidebar-item"><a href="{{ route('usuarios.cadastrar') }}" class="sidebar-link"><i class="mdi mdi-account-multiple-plus"></i><span class="hide-menu"> Cadastrar Usuário </span></a></li>
                    <li class="sidebar-item"><a href="{{ route('usuarios') }}" class="sidebar-link"><i class="mdi mdi-format-list-bulleted"></i><span class="hide-menu"> Exibir Usuários </span></a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- End Sidebar navigation -->