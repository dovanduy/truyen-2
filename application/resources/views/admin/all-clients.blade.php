@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('All Clients')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('All Clients')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">{{language_data('SL')}}#</th>
                                    <th style="width: 20%;">{{language_data('Name')}}</th>
                                    <th style="width: 10%;">{{language_data('User name')}}</th>
                                    <th style="width: 10%;">{{language_data('Created')}}</th>
                                    <th style="width: 15%;">{{language_data('Created By')}}</th>
                                    <th style="width: 10%;">{{language_data('Api Access')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 20%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($clients as $cl)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Name"><p>{{$cl->fname}} {{$cl->lname}}</p></td>
                                        <td data-label="Username"><p>{{$cl->username}}</p></td>
                                        <td data-label="Date Created"><p>{{get_date_format($cl->datecreated)}}</p></td>
                                        @if($cl->parent=='0')
                                            <td data-label="Created By"><p>{{language_data('Admin')}}</p></td>
                                        @else
                                            <td data-label="Created By"><p><a href="{{url('clients/view/'.$cl->parent)}}">{{client_info($cl->parent)->fname}}</a> </p></td>
                                        @endif

                                        @if($cl->api_access=='Yes')
                                            <td data-label="Api Access"><p class="btn btn-success btn-xs">{{language_data('Yes')}}</p></td>
                                        @else
                                            <td data-label="Api Access"><p class="btn btn-warning btn-xs">{{language_data('No')}}</p></td>
                                        @endif

                                        @if($cl->status=='Active')
                                            <td data-label="Status"><p class="btn btn-success btn-xs">{{language_data('Active')}}</p></td>
                                        @elseif($cl->status=='Inactive')
                                            <td data-label="Status"><p class="btn btn-warning btn-xs">{{language_data('Inactive')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="btn btn-danger btn-xs">{{language_data('Closed')}}</p></td>
                                        @endif
                                        <td data-label="Actions">
                                            <a class="btn btn-success btn-xs" href="{{url('clients/view/'.$cl->id)}}" ><i class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$cl->id}}"><i class="fa fa-trash"></i> {{language_data('Delete')}}</a>
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


            /*For Delete client*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/clients/delete-client/" + id;
                    }
                });
            });

        });
    </script>
@endsection