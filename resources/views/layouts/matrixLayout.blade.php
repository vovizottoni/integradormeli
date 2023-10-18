<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <!-- Favicon icon -->
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.png') }}">    
        <!-- Custom CSS -->
        <link href="{{ asset('assets/css/style.min.css') }}"  rel="stylesheet">  
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]--> 
        
        <title>Admin</title>
    </head>
    <body>

        <!-- ============================================================== -->     
        <!-- Preloader - style you can find in spinners.css -->     
        <!-- ============================================================== -->
        <div class="preloader" style="background-color: rgba(255,255,255,0.5);">   
            <div class="lds-ripple">  
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- Main wrapper - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <div id="main-wrapper">
            <!-- ============================================================== -->
            <!-- Topbar header - style you can find in pages.scss -->
            <!-- ============================================================== -->
            <header class="topbar" data-navbarbg="skin5">
                <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                    <div class="navbar-header" data-logobg="skin5">
                        <!-- This is for the sidebar toggle which is visible on mobile only -->
                        <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                        <!-- ============================================================== -->
                        <!-- Logo -->
                        <!-- ============================================================== -->
                        <a class="navbar-brand" href="index.html">
                            <!-- Logo icon -->
                            <b class="logo-icon p-l-10">
                                <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                                <!-- Dark Logo icon -->
                                <img src="{{ asset('assets/images/logo-icon.png') }}" alt="homepage" class="light-logo" />
                            
                            </b>
                            <!--End Logo icon -->
                            <!-- Logo text -->
                            <span class="logo-text">
                                <!-- dark Logo text -->
                                <img src="{{ asset('assets/images/logo-text.png') }}"  alt="homepage" class="light-logo" />
                                
                            </span>
                            <!-- Logo icon -->
                            <!-- <b class="logo-icon"> -->
                                <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                                <!-- Dark Logo icon -->
                                <!-- <img src="../../assets/images/logo-text.png" alt="homepage" class="light-logo" /> -->
                                
                            <!-- </b> -->
                            <!--End Logo icon -->
                        </a>
                        <!-- ============================================================== -->
                        <!-- End Logo -->
                        <!-- ============================================================== -->
                        <!-- ============================================================== -->
                        <!-- Toggle which is visible on mobile only -->
                        <!-- ============================================================== -->
                        <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    
                    <!-- TOPO do layout -->   
                    @include('layouts.pedacos.topo');

                </nav>
            </header>
            <!-- ============================================================== -->
            <!-- End Topbar header -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Left Sidebar - style you can find in sidebar.scss  -->
            <!-- ============================================================== -->
            <aside class="left-sidebar" data-sidebarbg="skin5">
                <!-- Sidebar scroll-->
                <div class="scroll-sidebar">

                    <!-- Menu lateral Esquerdo -->    
                    @include('layouts.pedacos.menu');       
                   
                </div>
                <!-- End Sidebar scroll-->
            </aside>
            <!-- ============================================================== -->
            <!-- End Left Sidebar - style you can find in sidebar.scss  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Page wrapper  -->
            <!-- ============================================================== -->
            <div class="page-wrapper">       
                @yield('content')
            </div>

        </div>    

        <!-- All Jquery -->
        <!-- ============================================================== -->
        <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script> 
        <!-- Bootstrap tether Core JavaScript -->
        <script src="{{ asset('assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
        <!-- slimscrollbar scrollbar JavaScript -->
        <script src="{{ asset('assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
        <script src="{{ asset('assets/extra-libs/sparkline/sparkline.js') }}"></script>
        <!--Wave Effects -->
        <script src="{{ asset('assets/js/waves.js') }}"></script>
        <!--Menu sidebar -->
        <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script> 
        <!--Custom JavaScript -->
        <script src="{{ asset('assets/js/custom.min.js') }}"></script>
        
        <!-- Jquery UI -->
        <script src="{{ asset('assets/meusPluginsJS/jquery-ui-1.12.1/jquery-ui.js') }}"></script> 
        <link href="{{ asset('assets/meusPluginsJS/jquery-ui-1.12.1/jquery-ui.css') }}"  rel="stylesheet">       

        <!-- Custom select2 -->
        <script src="{{ asset('assets/meusPluginsJS/select2/select2.min.js') }}"></script> 
        <link href="{{ asset('assets/meusPluginsJS/select2/select2.min.css') }}"  rel="stylesheet">   

        <!-- maskMoney e maskedInput -->    
        <script src="{{ asset('assets/meusPluginsJS/JQuery.MaskMoney.js') }}"></script>  
        <script src="{{ asset('assets/meusPluginsJS/JQuery.MaskedInput.js') }}"></script>  

                  


        <!-- Inicializa JS  -->
        <script>
            $(document).ready(function()
            {

                //***********************************//
                // Custom select - select 2
                //***********************************//
                $(".select2").select2();               
                                
                    
                
                //***********************************//
                // Inicializa Máscaras
                //***********************************//    
                $(".mask-money").maskMoney({prefix: "R$ ", affixesStay: false, decimal:",", thousands:"", allowZero: true, allowNegative: true});
                $(".mask-money-nozero").maskMoney({prefix: "R$ ", affixesStay: false, decimal:",", thousands:"", allowZero: false, allowNegative: true});
                $(".mask-money-nozero-nonegative").maskMoney({prefix: "R$ ", affixesStay: false, decimal:",", thousands:"", allowZero: false, allowNegative: false});

                $(".mask-formated-money").maskMoney({prefix: "R$ ", affixesStay: false, decimal:",", thousands:".", allowZero: true, allowNegative: true});
                $(".mask-formated-money-nozero").maskMoney({prefix: "R$ ", affixesStay: false, decimal:",", thousands:".", allowZero: false, allowNegative: true});
                $(".mask-formated-money-nozero-nonegative").maskMoney({prefix: "R$ ", affixesStay: false, decimal:",", thousands:".", allowZero: false, allowNegative: false});

                $(".mask-int").maskMoney({decimal:"", thousands:"", precision: 0, allowZero: true, allowNegative: true});
                $(".mask-int-nozero").maskMoney({decimal:"", thousands:"", precision: 0, allowZero: false, allowNegative: true});
                $(".mask-int-nonegative").maskMoney({decimal:"", thousands:"", precision: 0, allowZero: true, allowNegative: false});
                $(".mask-int-nozero-nonegative").maskMoney({decimal:"", thousands:"", precision: 0, allowZero: false, allowNegative: false});
                
                $(".mask-float").maskMoney({decimal:",", thousands:"", precision: 2, allowZero: true, allowNegative: true});
                $(".mask-float-nozero").maskMoney({decimal:",", thousands:"", precision: 2, allowZero: false, allowNegative: true});
                $(".mask-float-nonegative").maskMoney({decimal:",", thousands:"", precision: 2, allowZero: true, allowNegative: false});
                $(".mask-float-nozero-nonegative").maskMoney({decimal:",", thousands:"", precision: 2, allowZero: false, allowNegative: false});
                
                $(".mask-coordinate").maskMoney({decimal:".", thousands:"", precision: 8, allowZero: true, allowNegative: true});

                $('.mask-hora-completa').mask('99:99:99');
                $('.mask-hora').mask('99:99');
                $('.mask-data').mask('99/99/9999');
                $('.mask-data-hora').mask('99/99/9999 - 99:99');

                           
                $('.mask-datapicker').mask('99/99/9999').datepicker({ beforeShow: function() { setTimeout(function(){ $('.ui-datepicker').css('z-index', 300000); }, 0); } });
                
                $('.mask-ano').mask('9999');
                $('.mask-cep').mask('99999-999');
                $('.mask-cpf').mask('999.999.999-99');
                $('.mask-cnpj').mask('99.999.999/9999-99');

                $('.mask-up').mask('99 99999 999999 9999');

                
                $.mask.definitions['*'] = "[A-Za-z0-9]";
                $.mask.definitions['#'] = "[A-Za-z]";
                $('.mask-placa').mask('###-9*99');
                
                $('.mask-int-1').mask('9');
                $('.mask-int-2').mask('9?9');
                $('.mask-int-3').mask('9?99');


                $('.mask-celphone').mask("(99) 9 9999-9999");        
                $('.mask-phonephone').mask("(99) 9999-9999");          

                //híbrido 
                $('.mask-phone').mask("(99) 9999-9999?9").on('focusout', function(event)
                {
                    var target, phone, element;
                    target = (event.currentTarget) ? event.currentTarget : event.srcElement;
                    phone = target.value.replace(/\D/g, ''); element = $(target); element.unmask();
                    if(phone.length > 10) { element.mask("(99) 99999-999?9"); } else { element.mask("(99) 9999-9999?9"); }
                }); 

                $('.mask-phone-international').mask("+99 (99) 9999-9999?9").on('focusout', function(event)
                {
                    var target, phone, element;
                    target = (event.currentTarget) ? event.currentTarget : event.srcElement;
                    phone = target.value.replace(/\D/g, ''); element = $(target); element.unmask();
                    if(phone.length > 12) { element.mask("+99 (99) 99999-999?9"); } else { element.mask("+99 (99) 9999-9999?9"); }
                });
            });        

            Number.prototype.toMoney = function(decimals, decimal_sep, thousands_sep){ 
                var n = this,
                c = isNaN(decimals) ? 2 : Math.abs(decimals), //if decimal is zero we must take it, it means user does not want to show any decimal
                d = decimal_sep || '.', //if no decimal separator is passed we use the dot as default decimal separator (we MUST use a decimal separator)

                /*
                according to [https://stackoverflow.com/questions/411352/how-best-to-determine-if-an-argument-is-not-sent-to-the-javascript-function]
                the fastest way to check for not defined parameter is to use typeof value === 'undefined' 
                rather than doing value === undefined.
                */   
                t = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, //if you don't want to use a thousands separator you can pass empty string as thousands_sep value

                sign = (n < 0) ? '-' : '',

                //extracting the absolute value of the integer part of the number and converting to string
                i = parseInt(n = Math.abs(n).toFixed(c)) + '', 

                j = ((j = i.length) > 3) ? j % 3 : 0; 
                return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : ''); 
            }
        </script>

        <script> //Loader para forms
            var spinner = $('.preloader');
            $(function() {
                $('form').submit(function(e) { //qualquer formulario submetido, inicia o loader    

                    spinner.show();    

                });
            });
        </script>

        @stack("scripts")            

    </body>
</html>