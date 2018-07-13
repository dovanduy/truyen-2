@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage Payment Gateway')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage Payment Gateway')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('settings/post-payment-gateway-manage')}}">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('Gateway Name')}}</label>
                                    <input type="text" class="form-control" disabled value="{{$pg->name}}">
                                </div>


                                <div class="form-group">
                                    @if($pg->settings=='paypal')
                                        <label>{{language_data('User Name')}}</label>
                                    @elseif($pg->settings=='payu')
                                        <label>{{language_data('Client ID')}}</label>
                                    @elseif($pg->settings=='stripe')
                                        <label>{{language_data('Publishable Key')}}</label>
                                    @elseif($pg->settings=='manualpayment')
                                        <label>{{language_data('Bank Details')}}</label>
                                    @elseif($pg->settings=='authorize_net')
                                        <label>{{language_data('Api Login ID')}}</label>
                                    @elseif($pg->settings=='slydepay')
                                        <label>Merchant Email</label>
                                    @else
                                        <label>{{language_data('Value')}}</label>
                                    @endif
                                    <input type="text" class="form-control" name="pg_value" value="{{$pg->value}}">
                                </div>


                                @if($pg->settings=='paypal')
                                <div class="form-group">
                                    <label>{{language_data('Password')}}</label>
                                    <input type="text" class="form-control" name="pg_password" value="{{$pg->password}}">
                                </div>
                                @endif


                                @if($pg->settings=='stripe' || $pg->settings=='authorize_net' ||  $pg->settings=='slydepay' || $pg->settings=='paypal' || $pg->settings=='payu')
                                <div class="form-group">
                                    @if($pg->settings=='stripe' || $pg->settings=='paypal')
                                        <label>{{language_data('Secret_Key_Signature')}}</label>
                                    @elseif($pg->settings=='authorize_net')
                                        <label>{{language_data('Transaction Key')}}</label>
                                    @elseif($pg->settings=='payu')
                                        <label>{{language_data('Client Secret')}}</label>
                                    @elseif($pg->settings=='slydepay')
                                        <label>Merchant Secret</label>
                                    @else
                                        <label>{{language_data('Extra Value')}}</label>
                                    @endif
                                    <input type="text" class="form-control" name="pg_extra_value" value="{{$pg->extra_value}}">
                                </div>
                                @endif

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Active" @if($pg->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                        <option value="Inactive"  @if($pg->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" value="{{$pg->id}}" name="cmd">
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