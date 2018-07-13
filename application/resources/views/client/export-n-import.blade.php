@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Export and Import Clients')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Export Clients')}}</h3>
                        </div>
                        <div class="panel-body">
                                <ul class="info-list">
                                    <li>
                                        <span class="info-list-title">Export Clients</span><span class="info-list-des"><a href="{{url('user/export-user')}}" class="btn btn-success btn-xs">{{language_data('Export Clients as CSV')}}</a></span>
                                    </li>
                                    <li>
                                        <span class="info-list-title">{{language_data('Sample File')}}</span><span class="info-list-des"><a href="{{url('user/download-sample-csv')}}" class="btn btn-complete btn-xs">{{language_data('Download Sample File')}}</a> </span>
                                    </li>

                                </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Import Clients')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form class="" role="form" method="post" action="{{url('user/post-new-user-csv')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('Client Group')}}</label>
                                    <select class="selectpicker form-control" name="client_group"  data-live-search="true">
                                        <option value="0">{{language_data('None')}}</option>
                                        @foreach($client_groups as $cg)
                                            <option value="{{$cg->id}}">{{$cg->group_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Import Clients')}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="import_client">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                    <p class="text-uppercase text-complete help">{{language_data('It will take few minutes. Please do not reload the page')}}</p>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-download"></i> {{language_data('Import')}} </button>
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