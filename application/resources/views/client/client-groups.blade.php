@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Clients Groups')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Group')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('users/add-new-group')}}">
                                <div class="form-group">
                                    <label>{{language_data('Group Name')}}</label>
                                    <input type="text" class="form-control" required name="group_name">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Yes">{{language_data('Active')}}</option>
                                        <option value="No">{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Clients Groups')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL')}}#</th>
                                    <th style="width: 30%;">{{language_data('Group Name')}}</th>
                                    <th style="width: 15%;">{{language_data('Status')}}</th>
                                    <th style="width: 45%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($clientGroups as $cg)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Name"><p>{{$cg->group_name}} </p></td>
                                        @if($cg->status=='Yes')
                                            <td data-label="Status"><p class="btn btn-success btn-xs">{{language_data('Active')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="btn btn-warning btn-xs">{{language_data('Inactive')}}</p></td>
                                        @endif
                                        <td data-label="Actions">
                                            <a href="{{url('users/export-user-group/'.$cg->id)}}" class="btn btn-complete btn-xs"><i class="fa fa-upload"></i> {{language_data('Export Clients')}}</a>
                                            <a class="btn btn-success btn-xs" href="#" data-toggle="modal" data-target=".modal_edit_client_group_{{$cg->id}}"><i class="fa fa-edit"></i> {{language_data('Edit')}}</a>
                                            @include('client.modal-edit-client-group')
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$cg->id}}"><i class="fa fa-trash"></i> {{language_data('Delete')}}</a>
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

            /*For Delete Group*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/users/delete-group/" + id;
                    }
                });
            });

        });
    </script>
@endsection