<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Chôm truyện</title>
    <link rel="icon" type="image/x-icon"  href="<?php echo asset(app_config('AppFav')); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{--Global StyleSheet Start--}}
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
    {!! Html::style("assets/libs/bootstrap/css/bootstrap.min.css") !!}
    {!! Html::style("assets/libs/bootstrap-toggle/css/bootstrap-toggle.min.css") !!}
    {!! Html::style("assets/libs/font-awesome/css/font-awesome.min.css") !!}
    {!! Html::style("assets/libs/alertify/css/alertify.css") !!}
    {!! Html::style("assets/libs/alertify/css/alertify-bootstrap-3.css") !!}
    {!! Html::style("assets/libs/bootstrap-select/css/bootstrap-select.min.css") !!}

    {{--Custom StyleSheet Start--}}

    @yield('style')

    {{--Global StyleSheet End--}}

    {!! Html::style("assets/css/style.css") !!}
    {!! Html::style("assets/css/admin.css") !!}
    {!! Html::style("assets/css/responsive.css") !!}
    {!! Html::script("assets/ckeditor/ckeditor.js") !!}
</head>
<body class="has-left-bar has-top-bar @if(Auth::guard('client')->user()->menu_open==1) left-bar-open @endif">

<nav id="left-nav" class="left-nav-bar">
    <div class="nav-top-sec">
        <div class="app-logo">
            <a href="{{url('/dashboard')}}"><img src="<?php echo asset(app_config('AppLogo')); ?>" alt="logo" class="bar-logo"></a>
        </div>

        <a href="#" id="bar-setting" class="bar-setting"><i class="fa fa-bars"></i></a>
    </div>
    <div class="nav-bottom-sec">
        <ul class="left-navigation" id="left-navigation">

            {{--Dashboard--}}
            <li @if(Request::path()== 'dashboard') class="active" @endif><a href="{{url('dashboard')}}"><span class="menu-text">{{language_data('Dashboard')}}</span> <span class="menu-thumb"><i class="fa fa-dashboard"></i></span></a></li>

            <li class="has-sub @if(Request::path()== 'client/danh-sach-truyen' OR Request::path()=='client/them-truyen') sub-open init-sub-open @endif">
                <a href="#"><span class="menu-text">Truyện</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-envelope"></i></span></a>
                <ul class="sub">
                    <li @if(Request::path()== 'client/danh-sach-truyen') class="active" @endif><a href={{url('client/danh-sach-truyen')}}><span class="menu-text">Danh sách truyện</span> <span class="menu-thumb"><i class="fa fa-phone-square"></i></span></a></li>
                    <li @if(Request::path()== 'client/them-truyen') class="active" @endif><a href={{url('client/them-truyen')}}><span class="menu-text">Thêm truyện</span> <span class="menu-thumb"><i class="fa fa-phone-square"></i></span></a></li>
                </ul>
            </li>


            {{--Logout--}}
            <li @if(Request::path()== 'logout') class="active" @endif><a href="{{url('logout')}}"><span class="menu-text">{{language_data('Logout')}}</span> <span class="menu-thumb"><i class="fa fa-power-off"></i></span></a></li>

        </ul>
    </div>
</nav>

<main id="wrapper" class="wrapper">

    <div class="top-bar clearfix">
        <ul class="top-info-bar">
            <li class="dropdown bar-notification @if(count(latest_five_invoices(Auth::guard('client')->user()->id))>0) active @endif">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-shopping-cart"></i></a>
                <ul class="dropdown-menu arrow" role="menu">
                    <li class="title">{{language_data('Recent 5 Unpaid Invoices')}}</li>
                    @foreach(latest_five_invoices(Auth::guard('client')->user()->id) as $in)
                        <li>
                            <a href="{{url('user/invoices/view/'.$in->id)}}">{{language_data('Amount')}} : {{$in->total}}</a>
                        </li>
                    @endforeach
                    <li class="footer"><a href="{{url('user/invoices/all')}}">{{language_data('See All Invoices')}}</a></li>
                </ul>
            </li>

            <li class="dropdown bar-notification @if(count(latest_five_tickets(Auth::guard('client')->user()->id))>0) active @endif">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-envelope"></i></a>
                <ul class="dropdown-menu arrow message-dropdown" role="menu">
                    <li class="title">{{language_data('Recent 5 Pending Tickets')}}</li>
                    @foreach(latest_five_tickets(Auth::guard('client')->user()->id) as $st)
                        <li>
                            <a href="{{url('user/tickets/view-ticket/'.$st->id)}}">
                                <div class="name">{{$st->name}} <span>{{$st->date}}</span></div>
                                <div class="message">{{$st->subject}}</div>
                            </a>
                        </li>
                    @endforeach

                    <li class="footer"><a href="{{url('user/tickets/all')}}">{{language_data('See All Tickets')}}</a></li>
                </ul>
            </li>
        </ul>
        <div class="navbar-right">

            <div class="clearfix">
                <div class="dropdown user-profile pull-right">


                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    <span class="user-info text-complete text-uppercase m-r-30">{{language_data('SMS Balance')}} : {{Auth::guard('client')->user()->sms_limit}}</span>


                        <span class="user-info">{{Auth::guard('client')->user()->fname}} {{Auth::guard('client')->user()->lname}}</span>

                        @if(Auth::guard('client')->user()->image!='')
                            <img class="user-image" src="<?php echo asset('assets/client_pic/'.Auth::guard('client')->user()->image); ?>" alt="{{Auth::guard('client')->user()->fname}} {{Auth::guard('client')->user()->lname}}">
                        @else
                            <img class="user-image" src="<?php echo asset('assets/client_pic/profile.jpg'); ?>" alt="{{Auth::guard('client')->user()->fname}} {{Auth::guard('client')->user()->lname}}">
                        @endif

                    </a>
                    <ul class="dropdown-menu arrow right-arrow" role="menu">
                        <li><a href="{{url('user/edit-profile')}}"><i class="fa fa-edit"></i> {{language_data('Update Profile')}}</a></li>
                        <li><a href="{{url('user/change-password')}}"><i class="fa fa-lock"></i> {{language_data('Change Password')}}</a></li>
                        <li class="bg-dark">
                            <a href="{{url('logout')}}" class="clearfix">
                                <span class="pull-left">{{language_data('Logout')}}</span>
                                <span class="pull-right"><i class="fa fa-power-off"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </div>

    {{--Content File Start Here--}}

    @yield('content')

    {{--Content File End Here--}}

    <input type="hidden" id="_url" value="{{url('/')}}">
</main>

{{--Global JavaScript Start--}}
{!! Html::script("assets/libs/jquery-1.10.2.min.js") !!}
{!! Html::script("assets/libs/jquery.slimscroll.min.js") !!}
{!! Html::script("assets/libs/bootstrap/js/bootstrap.min.js") !!}
{!! Html::script("assets/libs/bootstrap-toggle/js/bootstrap-toggle.min.js") !!}
{!! Html::script("assets/libs/alertify/js/alertify.js") !!}
{!! Html::script("assets/libs/bootstrap-select/js/bootstrap-select.min.js") !!}
{!! Html::script("assets/js/jquery.blockUI.js") !!}
{!! Html::script("assets/js/bootbox.min.js") !!}
{!! Html::script("assets/libs/boostrap-fileupload.js") !!}
{!! Html::script("assets/js/scripts.js") !!}
{{--Global JavaScript End--}}

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('input[name="_token"]').val()
        }
    });

    var _url=$('#_url').val();

    $('#bar-setting').click(function(e){
        e.preventDefault();
        $.post(_url+'/user/menu-open-status');
    });

    $(".alert").delay(5000).slideUp(200, function() {
        $(this).alert('close');
    });
</script>

{{--Custom JavaScript Start--}}

@yield('script')

{{--Custom JavaScript End Here--}}
</body>

</html>