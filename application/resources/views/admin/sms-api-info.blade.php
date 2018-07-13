@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS API Info')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-5">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS API Info')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('ultimate-sms-api/update-info')}}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('SMS Gateway')}}</label>
                                    <select class="selectpicker form-control" name="sms_gateway"  data-live-search="true">
                                        @foreach($gateways as $sg)
                                            <option value="{{$sg->id}}" @if(app_config('sms_api_gateway')==$sg->id) selected @endif>{{$sg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('SMS API URL')}}</label>
                                    <input type="text" class="form-control" value="{{app_config('api_url')}}" name="api_url">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('SMS Api key')}}</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" id="api-key" name="api_key" value="{{app_config('api_key')}}">
                                        <span class="input-group-addon btn btn-success getNewPass"><i class="fa fa-refresh"></i> {{language_data('Generate New')}}</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS API Details')}}</h3>
                        </div>
                        <div class="panel-body">

                            <div class="form-group">
                                <label>{{language_data('SMS Api Key')}}:</label>
                                <p class="text-sm">{{app_config('api_key')}}</p>
                            </div>

                            <div class="form-group">
                                <label>{{language_data('SMS API URL')}}:</label>
                                <p class="text-sm">{{rtrim(app_config('api_url'),'/').'/ultimate-sms/api?action=send-sms&api_key='.app_config('api_key').'&to=PhoneNumber&from=SenderID&sms=YourMessage&response=json'}}</p>
                            </div>


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
    <script>

        // Generate a password string
        function randString(){
            var chars = "abcdefghijklmnopqrstuvwxyz=ABCDEFGHIJKLMNOP";
            var pass = "";
            for (var x = 0; x < 20; x++) {
                var i = Math.floor(Math.random() * chars.length);
                pass += chars.charAt(i);
            }
           return btoa(pass);

        }
        // Create a new password
        $(".getNewPass").click(function(){
            var field = $(this).closest('div').find('#api-key');
            field.val(randString(field));
        });

    </script>
@endsection