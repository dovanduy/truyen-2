<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{app_config('AppTitle')}}</title>
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


</head>



<body class="has-left-bar has-top-bar @if(Auth::user()->menu_open==1) left-bar-open @endif">

<nav id="left-nav" class="left-nav-bar">
    <div class="nav-top-sec">
        <div class="app-logo">
            <a href="{{url('admin/dashboard')}}"><img src="<?php echo asset(app_config('AppLogo')); ?>" alt="logo" class="bar-logo"></a>
        </div>

        <a href="#" id="bar-setting" class="bar-setting"><i class="fa fa-bars"></i></a>
    </div>
    <div class="nav-bottom-sec">
        <ul class="left-navigation" id="left-navigation">

            {{--Dashboard--}}
            <li @if(Request::path()== 'admin/dashboard') class="active" @endif><a href="{{url('admin/dashboard')}}"><span class="menu-text">{{language_data('Dashboard')}}</span> <span class="menu-thumb"><i class="fa fa-dashboard"></i></span></a></li>

                {{--Clients--}}
                <li class="has-sub @if(Request::path()== 'clients/all' OR Request::path()=='clients/add' OR Request::path()=='clients/view/'.view_id() OR Request::path()=='clients/export-n-import') sub-open init-sub-open @endif">
                    <a href="#"><span class="menu-text">{{language_data('Clients')}}</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-user"></i></span></a>
                    <ul class="sub">

                        <li @if(Request::path()== 'clients/all' OR Request::path()=='clients/view/'.view_id()) class="active" @endif><a href={{url('clients/all')}}><span class="menu-text">{{language_data('All Clients')}}</span> <span class="menu-thumb"><i class="fa fa-user"></i></span></a></li>

                        <li @if(Request::path()== 'clients/add') class="active" @endif><a href={{url('clients/add')}}><span class="menu-text">{{language_data('Add New Client')}}</span> <span class="menu-thumb"><i class="fa fa-user-plus"></i></span></a></li>

                        <li @if(Request::path()== 'clients/export-n-import') class="active" @endif><a href={{url('clients/export-n-import')}}><span class="menu-text">{{language_data('Export and Import Clients')}}</span> <span class="menu-thumb"><i class="fa fa-file-excel-o"></i></span></a></li>

                {{--Client Groups--}}
                <li @if(Request::path()== 'clients/groups') class="active" @endif><a href="{{url('clients/groups')}}"><span class="menu-text">{{language_data('Clients Groups')}}</span> <span class="menu-thumb"><i class="fa fa-users"></i></span></a></li>

                    </ul>
                </li>


                {{--Bulk SMS--}}
                <li class="has-sub @if(Request::path()== 'sms/send-sms' OR Request::path()== 'sms/send-single-sms'  OR Request::path()== 'sms/send-single-schedule-sms' OR Request::path()=='sms/send-sms-file' OR Request::path()=='sms/send-schedule-sms' OR Request::path()=='sms/send-schedule-sms-file' OR Request::path()=='sms/history' OR Request::path()=='sms/view-inbox/'.view_id() OR Request::path()=='sms/gateway' OR Request::path()=='sms/gateway-manage/'.view_id() OR Request::path()=='sms/price-plan' OR Request::path()=='sms/add-price-plan' OR Request::path()=='sms/coverage' OR Request::path()=='sms/manage-coverage/'.view_id() OR Request::path()=='sms/sender-id-management'  OR Request::path()=='sms/add-sender-id' OR Request::path()=='sms/view-sender-id/'.view_id() OR Request::path()=='sms/sms-templates' OR Request::path()=='sms/manage-sms-template/'.view_id() OR Request::path()== 'ultimate-sms-api/info'  OR Request::path()== 'sms/add-plan-feature/'.view_id() OR Request::path()== 'sms/manage-price-plan/'.view_id()  OR Request::path()== 'sms/view-plan-feature/'.view_id() OR Request::path()== 'sms/manage-plan-feature/'.view_id() OR Request::path()== 'sms/sms-gateway'  OR Request::path()=='sms/add-sms-gateways' OR Request::path()=='sms/gateway-manage/'.view_id() OR Request::path()=='sms/custom-gateway-manage/'.view_id()  OR Request::path()=='sms/create-sms-template' OR Request::path()== 'sms/update-schedule-sms' OR Request::path()== 'sms/import-phone-number' OR Request::path()== 'sms/send-bulk-birthday-sms' OR Request::path()== 'sms/send-bulk-sms-remainder'  OR Request::path()=='sms/manage-update-schedule-sms/'.view_id() OR Request::path()== 'sms/send-sms-phone-number' OR Request::path()== 'sms/historyFile' OR Request::path()== 'sms/historyFileSchedule' ) sub-open init-sub-open @endif">
                    <a href="#"><span class="menu-text">1.{{language_data('Bulk SMS')}}</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-mobile"></i></span></a>
                    <ul class="sub">

                        {{--Version 1.1--}}
                        <li @if(Request::path()== 'sms/send-single-sms') class="active" @endif><a href={{url('sms/send-single-sms')}}><span class="menu-text"> 1.1 {{language_data('Send Single SMS')}}</span> <span class="menu-thumb"><i class="fa fa-phone-square"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-single-schedule-sms') class="active" @endif><a href={{url('sms/send-single-schedule-sms')}}><span class="menu-text">1.2 {{language_data('Single Schedule SMS')}}</span> <span class="menu-thumb"><i class="fa fa-clock-o"></i></span></a></li>

                        <li @if(Request::path()== 'sms/import-phone-number') class="active" @endif><a href={{url('sms/import-phone-number')}}><span class="menu-text">1.3 {{language_data('Import Phone Number')}}</span> <span class="menu-thumb"><i class="fa fa-file-excel-o"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-sms-phone-number') class="active" @endif><a href={{url('sms/send-sms-phone-number')}}><span class="menu-text">1.4 {{language_data('SMS By Phone Number')}}</span> <span class="menu-thumb"><i class="fa fa-phone-square"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-sms') class="active" @endif><a href={{url('sms/send-sms')}}><span class="menu-text">1.5 Gửi tin cho nhóm khách hàng</span> <span class="menu-thumb"><i class="fa fa-send"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-sms-file') class="active" @endif><a href={{url('sms/send-sms-file')}}><span class="menu-text">1.6 {{language_data('Send SMS From File')}}</span> <span class="menu-thumb"><i class="fa fa-file-text"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-schedule-sms') class="active" @endif><a href={{url('sms/send-schedule-sms')}}><span class="menu-text">1.7 Gửi tin lập lịch cho nhóm khách hàng</span> <span class="menu-thumb"><i class="fa fa-send-o"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-bulk-birthday-sms') class="active" @endif><a href={{url('sms/send-bulk-birthday-sms')}}><span class="menu-text">1.8 {{language_data('Send Bulk Birthday SMS')}}</span> <span class="menu-thumb"><i class="fa fa-birthday-cake"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-bulk-sms-remainder') class="active" @endif><a href={{url('sms/send-bulk-sms-remainder')}}><span class="menu-text">1.9 Gửi tin nhắn nhắc nhở</span> <span class="menu-thumb"><i class="fa fa-bell"></i></span></a></li>

                        <li @if(Request::path()== 'sms/send-schedule-sms-file') class="active" @endif><a href={{url('sms/send-schedule-sms-file')}}><span class="menu-text">1.10 {{language_data('Schedule SMS From File')}}</span> <span class="menu-thumb"><i class="fa fa-file-text-o"></i></span></a></li>

                        <li @if(Request::path()== 'sms/update-schedule-sms' OR Request::path()=='sms/manage-update-schedule-sms/'.view_id()) class="active" @endif><a href={{url('sms/update-schedule-sms')}}><span class="menu-text">1.11 {{language_data('Update Schedule SMS')}}</span> <span class="menu-thumb"><i class="fa fa-edit"></i></span></a></li>

                        <li @if(Request::path()=='sms/history' OR Request::path()=='sms/view-inbox/'.view_id()) class="active" @endif><a href={{url('sms/history')}}><span class="menu-text">1.12 Lịch sử gửi tin theo mobile</span> <span class="menu-thumb"><i class="fa fa-list"></i></span></a></li>

                        <li @if(Request::path()=='sms/historyFileSchedule' OR Request::path()=='sms/view-inbox/'.view_id()) class="active" @endif><a href={{url('sms/historyFileSchedule')}}><span class="menu-text">1.13 Danh sách gửi tin theo lịch</span> <span class="menu-thumb"><i class="fa fa-list"></i></span></a></li>


                        <li @if(Request::path()=='sms/historyFile' OR Request::path()=='sms/view-inbox/'.view_id()) class="active" @endif><a href={{url('sms/historyFile')}}><span class="menu-text">1.14 Lịch sử gửi tin</span> <span class="menu-thumb"><i class="fa fa-list"></i></span></a></li>

                        <li @if(Request::path()=='sms/gateway' OR Request::path()=='sms/add-sms-gateways' OR Request::path()=='sms/gateway-manage/'.view_id() OR Request::path()=='sms/custom-gateway-manage/'.view_id()) class="active" @endif><a href={{url('sms/sms-gateway')}}><span class="menu-text">1.15 {{language_data('SMS Gateway')}}</span> <span class="menu-thumb"><i class="fa fa-server"></i></span></a></li>

                        <li @if(Request::path()== 'sms/price-plan' OR Request::path()== 'sms/add-plan-feature/'.view_id() OR Request::path()== 'sms/manage-price-plan/'.view_id() OR Request::path()== 'sms/view-plan-feature/'.view_id()  OR Request::path()== 'sms/manage-plan-feature/'.view_id()) class="active" @endif><a href={{url('sms/price-plan')}}><span class="menu-text">1.16 {{language_data('SMS Price Plan')}}</span> <span class="menu-thumb"><i class="fa fa-money"></i></span></a></li>

                        <li @if(Request::path()== 'sms/add-price-plan') class="active" @endif><a href={{url('sms/add-price-plan')}}><span class="menu-text">1.17 {{language_data('Add Price Plan')}}</span> <span class="menu-thumb"><i class="fa fa-plus"></i></span></a></li>

                        <li @if(Request::path()=='sms/coverage' OR Request::path()=='sms/manage-coverage/'.view_id()) class="active" @endif><a href={{url('sms/coverage')}}><span class="menu-text">1.18 {{language_data('Coverage')}}</span> <span class="menu-thumb"><i class="fa fa-wifi"></i></span></a></li>

                        <li @if(Request::path()== 'sms/sender-id-management' OR Request::path()=='sms/add-sender-id' OR Request::path()=='sms/view-sender-id/'.view_id()) class="active" @endif><a href={{url('sms/sender-id-management')}}><span class="menu-text">1.19 {{language_data('Sender ID Management')}}</span> <span class="menu-thumb"><i class="fa fa-user-secret"></i></span></a></li>

                        <li @if(Request::path()=='sms/sms-templates' OR Request::path()=='sms/create-sms-template' OR Request::path()=='sms/manage-sms-template/'.view_id()) class="active" @endif><a href={{url('sms/sms-templates')}}><span class="menu-text">1.20 {{language_data('SMS Templates')}}</span> <span class="menu-thumb"><i class="fa fa-file-code-o"></i></span></a></li>

                        <li @if(Request::path()== 'ultimate-sms-api/info') class="active" @endif><a href={{url('ultimate-sms-api/info')}}><span class="menu-text">1.21 {{language_data('SMS Api')}}</span> <span class="menu-thumb"><i class="fa fa-plug"></i></span></a></li>


                    </ul>
                </li>


                {{--Invoices--}}
                <li class="has-sub @if(Request::path()== 'invoices/all' OR Request::path()=='invoices/add' OR Request::path()=='invoices/recurring' OR Request::path()=='invoices/view/'.view_id() OR Request::path()=='invoices/edit/'.view_id()) sub-open init-sub-open @endif">
                    <a href="#"><span class="menu-text">{{language_data('Invoices')}}</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-credit-card"></i></span></a>
                    <ul class="sub">

                        <li @if(Request::path()== 'invoices/all'  OR Request::path()=='invoices/view/'.view_id() OR Request::path()=='invoices/edit/'.view_id()) class="active" @endif><a href={{url('invoices/all')}}><span class="menu-text">{{language_data('All Invoices')}}</span> <span class="menu-thumb"><i class="fa fa-list"></i></span></a></li>

                        <li @if(Request::path()== 'invoices/recurring') class="active" @endif><a href={{url('invoices/recurring')}}><span class="menu-text">{{language_data('Recurring Invoices')}}</span> <span class="menu-thumb"><i class="fa fa-list"></i></span></a></li>

                        <li @if(Request::path()== 'invoices/add') class="active" @endif><a href={{url('invoices/add')}}><span class="menu-text">{{language_data('Add New Invoice')}}</span> <span class="menu-thumb"><i class="fa fa-plus"></i></span></a></li>

                    </ul>
                </li>

                {{--Support Ticket--}}
                <li class="has-sub @if(Request::path()== 'support-tickets/all' OR Request::path()=='support-tickets/create-new' OR Request::path()=='support-tickets/department' OR Request::path()=='support-tickets/view-department/'.view_id() OR Request::path()=='support-tickets/view-ticket/'.view_id()) sub-open init-sub-open @endif">
                    <a href="#"><span class="menu-text">{{language_data('Support Tickets')}}</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-envelope"></i></span></a>
                    <ul class="sub">
                        <li @if(Request::path()== 'support-tickets/all'  OR Request::path()=='support-tickets/view-ticket/'.view_id()) class="active" @endif><a href={{url('support-tickets/all')}}><span class="menu-text">{{language_data('All Support Tickets')}}</span> <span class="menu-thumb"><i class="fa fa-list"></i></span></a></li>

                        <li @if(Request::path()== 'support-tickets/create-new') class="active" @endif><a href={{url('support-tickets/create-new')}}><span class="menu-text">{{language_data('Create New Ticket')}}</span> <span class="menu-thumb"><i class="fa fa-plus"></i></span></a></li>

                        <li @if(Request::path()== 'support-tickets/department') class="active" @endif><a href={{url('support-tickets/department')}}><span class="menu-text">{{language_data('Support Department')}}</span> <span class="menu-thumb"><i class="fa fa-support"></i></span></a></li>

                    </ul>
                </li>




                {{--Administrators--}}
                <li class="has-sub @if(Request::path()== 'administrators/all' OR Request::path()=='administrators/manage/'.view_id() OR Request::path()=='administrators/role' OR Request::path()=='administrators/set-role/'.view_id()) sub-open init-sub-open @endif">
                    <a href="#"><span class="menu-text">{{language_data('Administrators')}}</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-user"></i></span></a>
                    <ul class="sub">
                        <li @if(Request::path()== 'administrators/all'  OR Request::path()=='administrators/manage/'.view_id()) class="active" @endif><a href={{url('administrators/all')}}><span class="menu-text">{{language_data('Administrators')}}</span> <span class="menu-thumb"><i class="fa fa-user"></i></span></a></li>

                        <li @if(Request::path()=='administrators/role' OR Request::path()=='administrators/set-role/'.view_id()) class="active" @endif><a href={{url('administrators/role')}}><span class="menu-text">{{language_data('Administrator Roles')}}</span> <span class="menu-thumb"><i class="fa fa-user-secret"></i></span></a></li>

                    </ul>
                </li>



                {{--Setting--}}
                <li class="has-sub @if(Request::path()== 'settings/general' OR Request::path()=='settings/localization'  OR Request::path()=='settings/email-templates'  OR Request::path()=='settings/email-template-manage/'.view_id() OR Request::path()=='settings/language-settings' OR Request::path()=='settings/language-settings-translate/'.view_id() OR Request::path()=='settings/language-settings-manage/'.view_id()  OR Request::path()=='settings/payment-gateways' OR Request::path()=='settings/payment-gateway-manage/'.view_id()) sub-open init-sub-open @endif">
                    <a href="#"><span class="menu-text">{{language_data('Settings')}}</span> <span class="arrow"></span><span class="menu-thumb"><i class="fa fa-cogs"></i></span></a>
                    <ul class="sub">

                        <li @if(Request::path()== 'settings/general') class="active" @endif><a href={{url('settings/general')}}><span class="menu-text">{{language_data('System Settings')}}</span> <span class="menu-thumb"><i class="fa fa-cog"></i></span></a></li>

                        <li @if(Request::path()== 'settings/localization') class="active" @endif><a href={{url('settings/localization')}}><span class="menu-text">{{language_data('Localization')}}</span> <span class="menu-thumb"><i class="fa fa-globe"></i></span></a></li>

                        <li @if(Request::path()== 'settings/email-templates' OR Request::path()=='settings/email-template-manage/'.view_id()) class="active" @endif><a href={{url('settings/email-templates')}}><span class="menu-text">{{language_data('Email Templates')}}</span> <span class="menu-thumb"><i class="fa fa-envelope"></i></span></a></li>

                        <li @if(Request::path()== 'settings/language-settings' OR Request::path()=='settings/language-settings-manage/'.view_id() OR Request::path()=='settings/language-settings-translate/'.view_id()) class="active" @endif><a href={{url('settings/language-settings')}}><span class="menu-text">{{language_data('Language Settings')}}</span> <span class="menu-thumb"><i class="fa fa-language"></i></span></a></li>

                        <li @if(Request::path()=='settings/payment-gateways' OR Request::path()=='settings/payment-gateway-manage/'.view_id()) class="active" @endif><a href={{url('settings/payment-gateways')}}><span class="menu-text">{{language_data('Payment Gateways')}}</span> <span class="menu-thumb"><i class="fa fa-paypal"></i></span></a></li>

                    </ul>
                </li>


            {{--Logout--}}
            <li @if(Request::path()== 'admin/logout') class="active" @endif><a href="{{url('admin/logout')}}"><span class="menu-text">{{language_data('Logout')}}</span> <span class="menu-thumb"><i class="fa fa-power-off"></i></span></a></li>

        </ul>
    </div>
</nav>

<main id="wrapper" class="wrapper">

    <div class="top-bar clearfix">
        <ul class="top-info-bar">

            <li class="dropdown bar-notification @if(count(latest_five_invoices(0))>0) active @endif">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-shopping-cart"></i></a>
                <ul class="dropdown-menu arrow" role="menu">
                    <li class="title">{{language_data('Recent 5 Unpaid Invoices')}}</li>
                    @foreach(latest_five_invoices(0) as $in)
                        <li>
                            <a href="{{url('invoices/view/'.$in->id)}}">{{language_data('Amount')}} : {{$in->total}}</a>
                        </li>
                    @endforeach
                    <li class="footer"><a href="{{url('invoices/all')}}">{{language_data('See All Invoices')}}</a></li>
                </ul>
            </li>

            <li class="dropdown bar-notification @if(count(latest_five_tickets(0))>0) active @endif">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-envelope"></i></a>
                <ul class="dropdown-menu arrow message-dropdown" role="menu">
                    <li class="title">{{language_data('Recent 5 Pending Tickets')}}</li>
                    @foreach(latest_five_tickets(0) as $st)
                        <li>
                            <a href="{{url('support-tickets/view-ticket/'.$st->id)}}">
                                <div class="name">{{$st->name}} <span>{{$st->date}}</span></div>
                                <div class="message">{{$st->subject}}</div>
                            </a>
                        </li>
                    @endforeach

                    <li class="footer"><a href="{{url('support-tickets/all')}}">{{language_data('See All Tickets')}}</a></li>
                </ul>
            </li>
        </ul>


        <div class="navbar-right">

            <div class="clearfix">
                <div class="dropdown user-profile pull-right">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <span class="user-info">{{Auth::user()->fname}} {{Auth::user()->lname}}</span>

                        @if(Auth::user()->image!='')
                            <img class="user-image" src="<?php echo asset('assets/admin_pic/'.Auth::user()->image); ?>" alt="{{Auth::user()->fname}} {{Auth::user()->lname}}">
                        @else
                            <img class="user-image" src="<?php echo asset('assets/admin_pic/profile.jpg'); ?>" alt="{{Auth::user()->fname}} {{Auth::user()->lname}}">
                        @endif

                    </a>
                    <ul class="dropdown-menu arrow right-arrow" role="menu">
                        <li><a href="{{url('admin/edit-profile')}}"><i class="fa fa-edit"></i> {{language_data('Update Profile')}}</a></li>
                        <li><a href="{{url('admin/change-password')}}"><i class="fa fa-lock"></i> {{language_data('Change Password')}}</a></li>
                        <li class="bg-dark">
                            <a href="{{url('admin/logout')}}" class="clearfix">
                                <span class="pull-left">{{language_data('Logout')}}</span>
                                <span class="pull-right"><i class="fa fa-power-off"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="top-info-bar m-r-10">

                    <div class="dropdown pull-right bar-notification">
                        <a href="#" class="dropdown-toggle text-success" data-toggle="dropdown" role="button" aria-expanded="false">
                            <img src="<?php echo asset('assets/country_flag/'.\App\Language::find(app_config('Language'))->icon); ?>" alt="Language">
                        </a>
                        <ul class="dropdown-menu lang-dropdown arrow right-arrow" role="menu">
                            @foreach(get_language() as $lan)
                                <li>
                                    <a href="{{url('language/change/'.$lan->id)}}" @if($lan->id==app_config('Language')) class="text-complete" @endif>
                                        <img class="user-thumb" src="<?php echo asset('assets/country_flag/'.$lan->icon); ?>" alt="user thumb">
                                        <div class="user-name">{{$lan->language}}</div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
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
        $.post(_url+'/admin/menu-open-status');
    });
</script>

{{--Custom JavaScript Start--}}

@yield('script')

{{--Custom JavaScript End Here--}}
</body>

</html>