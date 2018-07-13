@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('System Settings')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('System Settings')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('settings/post-general-setting')}}" method="post" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('Application Name')}}</label>
                                    <input type="text" class="form-control" required name="app_name" value="{{app_config('AppName')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Application Title')}}</label>
                                    <input type="text" class="form-control" name="app_title" required="" value="{{app_config('AppTitle')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Address')}}</label>
                                    <textarea class="form-control textarea-wysihtml5" name="address">{{app_config('Address')}}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('System Email')}}</label>
                                    <span class="help">{{language_data('Remember: All Email Going to the Receiver from this Email')}}</span>
                                    <input type="email" class="form-control" required name="email" value="{{app_config('Email')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Footer Text')}}</label>
                                    <input type="text" class="form-control" required name="footer" value="{{app_config('FooterTxt')}}">
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Application Logo')}}</label>
                                    <div class="input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="app_logo">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Application Favicon')}}</label>
                                    <div class="input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="app_fav">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('API Permission')}}</label>
                                    <select class="selectpicker form-control" name="api_permission">
                                        <option value="1" @if(app_config('sms_api_permission')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('sms_api_permission')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Sender ID Verification')}}</label>
                                    <select class="selectpicker form-control" name="sender_id_verification">
                                        <option value="1" @if(app_config('sender_id_verification')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('sender_id_verification')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Allow Client Registration')}}</label>
                                    <select class="selectpicker form-control" name="client_registration">
                                        <option value="1" @if(app_config('client_registration')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('client_registration')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Client Registration Verification')}}</label>
                                    <select class="selectpicker form-control" name="registration_verification">
                                        <option value="1" @if(app_config('registration_verification')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('registration_verification')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Captcha In Admin Login')}}</label>
                                    <select class="selectpicker form-control" name="captcha_in_admin">
                                        <option value="1" @if(app_config('captcha_in_admin')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('captcha_in_admin')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Captcha In Client Login')}}</label>
                                    <select class="selectpicker form-control" name="captcha_in_client">
                                        <option value="1" @if(app_config('captcha_in_client')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('captcha_in_client')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Captcha In Client Registration')}}</label>
                                    <select class="selectpicker form-control" name="captcha_in_client_registration">
                                        <option value="1" @if(app_config('captcha_in_client_registration')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('captcha_in_client_registration')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="fname">{{language_data('reCAPTCHA Site Key')}}</label>
                                    <input type="text" class="form-control" required="" name="captcha_site_key" value="{{app_config('captcha_site_key')}}">
                                </div>

                                <div class="form-group">
                                    <label for="fname">{{language_data('reCAPTCHA Secret Key')}}</label>
                                    <input type="text" class="form-control" required="" name="captcha_secret_key" value="{{app_config('captcha_secret_key')}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Email Gateway')}}</label>
                                    <select class="selectpicker form-control gateway" name="email_gateway">
                                        <option value="default" @if(app_config('Gateway')=='default') selected @endif>{{language_data('Server Default')}}</option>
                                        <option value="smtp" @if(app_config('Gateway')=='smtp') selected @endif> {{language_data('SMTP')}} </option>
                                    </select>
                                </div>

                                <div class="show-smtp">

                                    <div class="form-group">
                                        <label for="fname">{{language_data('SMTP')}} {{language_data('Host Name')}}</label>
                                        <input type="text" class="form-control" required="" name="host_name" value="{{app_config('SMTPHostName')}}">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">{{language_data('SMTP')}} {{language_data('User Name')}}</label>
                                        <input type="text" class="form-control" required="" name="user_name"  value="{{app_config('SMTPUserName')}}">
                                    </div>

                                    <div class="form-group">
                                        <label for="fname">{{language_data('SMTP')}} {{language_data('Password')}}</label>
                                        <input type="text" class="form-control" required="" name="password"  value="{{app_config('SMTPPassword')}}">
                                    </div>


                                    <div class="form-group">
                                        <label for="fname">{{language_data('SMTP')}} {{language_data('Port')}}</label>
                                        <input type="text" class="form-control" required="" name="port"  value="{{app_config('SMTPPort')}}">
                                    </div>


                                    <div class="form-group">
                                        <label for="Default Gateway">{{language_data('SMTP')}} {{language_data('Secure')}}</label>
                                        <select name="secure" class="selectpicker form-control">
                                            <option value="tls" @if(app_config('SMTPSecure')=='tls')  selected @endif>{{language_data('TLS')}}</option>
                                            <option value="ssl" @if(app_config('SMTPSecure')=='ssl')selected @endif>{{language_data('SSL')}}</option>
                                        </select>
                                    </div>


                                </div>

                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-edit"></i> {{language_data('Update')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

{{--External Style Section--}}
@section('script')

    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/wysihtml5x/wysihtml5x-toolbar.min.js")!!}
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}
    <script>
        $(document).ready(function () {

            var EmailGatewaySV = $('.gateway');
            if (EmailGatewaySV.val() == 'default') {
                $('.show-smtp').hide();
            }

            EmailGatewaySV.on('change', function () {
                var value = $(this).val();
                if (value == 'smtp') {
                    $('.show-smtp').show();
                } else {
                    $('.show-smtp').hide();
                }

            });

        });

    </script>

@endsection