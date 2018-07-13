@extends('client')


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Profile')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')

            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-body p-t-20">
                            <div class="clearfix">
                                <div class="pull-left m-r-30">
                                    <div class="thumbnail m-b-none">

                                        @if($client->image!='')
                                            <img src="<?php echo asset('assets/client_pic/'.$client->image); ?>" alt="Profile Page" width="200px" height="200px">
                                        @else
                                            <img src="<?php echo asset('assets/client_pic/user.png');?>" alt="Profile Page" width="200px" height="200px">
                                        @endif
                                    </div>
                                </div>
                                <div class="pull-left">
                                    <h3 class="bold font-color-1">{{$client->fname}} {{$client->lname}}</h3>
                                    <ul class="info-list">
                                        @if($client->email!='')
                                            <li><span class="info-list-title">{{language_data('Email')}}</span><span class="info-list-des">{{$client->email}}</span></li>
                                        @endif

                                        @if($client->username!='')
                                                <li><span class="info-list-title">{{language_data('User Name')}}</span><span class="info-list-des">{{$client->username}}</span></li>
                                        @endif

                                         <li>
                                             <span class="info-list-title">{{language_data('SMS Balance')}}</span><span class="info-list-des">
                                                {{$client->sms_limit}}
                                             </span>
                                         </li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#personal_details" aria-controls="home" role="tab" data-toggle="tab">{{language_data('Personal Details')}}</a></li>

                        <li role="presentation"><a href="#change-picture" aria-controls="settings" role="tab" data-toggle="tab">{{language_data('Change Image')}}</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content panel p-20">


                        {{--Personal Details--}}

                        <div role="tabpanel" class="tab-pane active" id="personal_details">
                            <form role="form" action="{{url('user/post-personal-info')}}" method="post">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            <label>{{language_data('First Name')}}</label>
                                            <input type="text" class="form-control" required="" name="first_name" value="{{$client->fname}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Last Name')}}</label>
                                            <input type="text" class="form-control" name="last_name"  value="{{$client->lname}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Company')}}</label>
                                            <input type="text" class="form-control" name="company" value="{{$client->company}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Website')}}</label>
                                            <input type="url" class="form-control" name="website" value="{{$client->website}}">
                                        </div>
                                        <div class="form-group">
                                            <label>{{language_data('Email')}}</label>
                                            <span class="help">{{language_data('If you leave this, then you can not reset password or can not maintain email related function')}}</span>
                                            <input type="email" class="form-control" name="email" value="{{$client->email}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Phone')}}</label>
                                            <input type="text" class="form-control" required name="phone" value="{{$client->phone}}">
                                        </div>



                                    </div>
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            <label>{{language_data('Address')}}</label>
                                            <input type="text" class="form-control" name="address" value="{{$client->address1}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('More Address')}}</label>
                                            <input type="text" class="form-control" name="more_address"  value="{{$client->address2}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('State')}}</label>
                                            <input type="text" class="form-control" name="state"  value="{{$client->state}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('City')}}</label>
                                            <input type="text" class="form-control" name="city"  value="{{$client->city}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Postcode')}}</label>
                                            <input type="text" class="form-control" name="postcode"  value="{{$client->postcode}}">
                                        </div>

                                        <div class="form-group">
                                            <label for="Country">{{language_data('Country')}}</label>
                                            <select name="country" class="form-control selectpicker" data-live-search="true">
                                                {!!countries($client->country)!!}
                                            </select>
                                        </div>

                                    </div>

                                    <div class="col-md-12">
                                        <input type="hidden" value="{{$client->id}}" name="cmd">
                                        <input type="submit" value="{{language_data('Update')}}" class="btn btn-primary">
                                    </div>
                                </div>


                            </form>

                        </div>


                        <div role="tabpanel" class="tab-pane" id="change-picture">
                            <form role="form" action="{{url('user/update-avatar')}}" method="post" enctype="multipart/form-data">

                                <div class="row">
                                    <div class="col-md-4">

                                        <div class="form-group input-group input-group-file">
                                                <span class="input-group-btn">
                                                    <span class="btn btn-primary btn-file">
                                                        {{language_data('Browse')}} <input type="file" class="form-control" name="image">
                                                    </span>
                                                </span>
                                            <input type="text" class="form-control" readonly="">
                                        </div>

                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" value="{{language_data('Update')}}" class="btn btn-primary">

                                    </div>

                                </div>

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
