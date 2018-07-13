@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30 clearfix">
            <h2 class="page-title inline-block">{{language_data('SMS Gateway')}}</h2>

            <a href="{{url('sms/add-sms-gateways')}}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add Gateway')}}</a>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS Gateway')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL')}}#</th>
                                    <th style="width: 25%;">{{language_data('Gateway Name')}}</th>
                                    <th style="width: 20%;">{{language_data('Schedule SMS')}}</th>
                                    <th style="width: 15%;">{{language_data('Two Way')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 20%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($gateways as $g)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Gateway Name"><p>{{$g->name}}</p></td>

                                        @if($g->schedule=='Yes')
                                            <td data-label="Schedule SMS"><p class="label label-success">{{language_data('Yes')}}</p></td>
                                        @else
                                            <td data-label="Schedule SMS"><p class="label label-danger">{{language_data('No')}}</p></td>
                                        @endif

                                        @if($g->two_way=='Yes')
                                            <td data-label="Two Way"><p class="label label-success">{{language_data('Yes')}}</p></td>
                                        @else
                                            <td data-label="Two Way"><p class="label label-danger">{{language_data('No')}}</p></td>
                                        @endif

                                        @if($g->status=='Active')
                                            <td data-label="Status"><p class="label label-success">{{language_data('Active')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="label label-danger">{{language_data('Inactive')}}</p></td>
                                        @endif
                                        <td data-label="Actions">
                                            @if($g->custom=='Yes')
                                                <a class="btn btn-success btn-xs" href="{{url('sms/custom-gateway-manage/'.$g->id)}}"><i class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                                <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$g->id}}"><i class="fa fa-trash"></i> {{language_data('Delete')}}</a>
                                            @else
                                                <a class="btn btn-success btn-xs" href="{{url('sms/gateway-manage/'.$g->id)}}"><i class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
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
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function(){
            $('.data-table').DataTable();

            /*For Delete Gateway*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-sms-gateway/" + id;
                    }
                });
            });

        });
    </script>
@endsection