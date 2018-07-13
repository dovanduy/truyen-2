@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS Gateway Manage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS Gateway Manage')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-manage-sms-gateway')}}">
                                {{ csrf_field() }}

                                @if($gateway->custom=='Yes')
                                    <div class="form-group">
                                        <label>{{language_data('Gateway Name')}}</label>
                                        <input type="text" class="form-control" required name="gateway_name" value="{{$gateway->name}}">
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label>{{language_data('Gateway Name')}}</label>
                                        <input type="text" class="form-control" value="{{$gateway->name}}" disabled>
                                    </div>
                                @endif

                                @if($gateway->name!='Twilio' && $gateway->name!='Zang' && $gateway->name!='Plivo')
                                    <div class="form-group">
                                        <label>{{language_data('Gateway API Link')}}</label>
                                        <input type="text" class="form-control" required name="gateway_link" value="{{$gateway->api_link}}">
                                    </div>
                                @endif


                                <div class="form-group">
                                    <label>
                                        @if($gateway->name=='Telenorcsms')
                                            {{language_data('Msisdn')}}
                                        @elseif($gateway->name=='Twilio' || $gateway->name=='Zang')
                                            {{language_data('Account Sid')}}
                                        @elseif($gateway->name=='Plivo')
                                            {{language_data('Auth ID')}}
                                        @elseif($gateway->name=='Wavecell')
                                            Account ID
                                        @else
                                            {{language_data('SMS Api User name')}}
                                        @endif
                                    </label>
                                    <input type="text" class="form-control" name="gateway_user_name" value="{{$gateway->username}}">
                                </div>

                                <div class="form-group">
                                    <label>
                                        @if($gateway->name=='Twilio' || $gateway->name=='Zang' || $gateway->name=='Plivo')
                                            {{language_data('Auth Token')}}
                                        @elseif($gateway->name=='SMSKaufen' || $gateway->name=='NibsSMS')
                                            {{language_data('SMS Api key')}}
                                        @else
                                            {{language_data('SMS Api Password')}}
                                        @endif
                                    </label>
                                    <input type="text" class="form-control" name="gateway_password" value="{{$gateway->password}}">
                                </div>

                                @if($gateway->name=='Clickatell' || $gateway->custom=='Yes' || $gateway->name=='Wavecell' || $gateway->name=='SmsGatewayMe'  || $gateway->name=='Asterisk' )
                                <div class="form-group">
                                    @if($gateway->name=='Wavecell')
                                        <label>Sub Account ID</label>
                                   @elseif($gateway->name=='SmsGatewayMe')
                                        <label>Device ID</label>
                                    @elseif($gateway->name=='Asterisk')
                                        <label>Port</label>
                                    @else
                                        <label>{{language_data('Extra Value')}}</label>
                                    @endif
                                    <input type="text" class="form-control" name="extra_value" value="{{$gateway->api_id}}">
                                </div>
                                @endif

                                @if($gateway->name=='Asterisk' )
                                <div class="form-group">
                                    <label>Device Name</label>
                                    <input type="text" class="form-control" name="device_name" value="{{env('SC_DEVICE')}}">
                                </div>
                                @endif

                                <div class="form-group">
                                    <label>{{language_data('Schedule SMS')}}</label>
                                    <select class="selectpicker form-control" name="schedule">
                                        <option value="Yes" @if($gateway->schedule=='Yes') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="No" @if($gateway->schedule=='No') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Active"  @if($gateway->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                        <option value="Inactive"  @if($gateway->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" value="{{$gateway->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>
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
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
@endsection