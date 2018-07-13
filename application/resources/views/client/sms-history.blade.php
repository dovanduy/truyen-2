@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('SMS History')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('SMS History')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL')}}#</th>
                                    <th style="width: 20%;">{{language_data('Sender')}}</th>
                                    <th style="width: 20%;">{{language_data('Receiver')}}</th>
                                    <th style="width: 20%;">{{language_data('Date')}}</th>
                                    <th style="width: 30%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sms_history as $h)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Sender"><p>{{$h->sender}}</p></td>
                                        <td data-label="Receiver"><p>{{$h->receiver}}</p></td>
                                        <td data-label="Date"><p>{{get_datetime_format($h->updated_at)}}</p></td>

                                        <td data-label="Actions">
                                            <a class="btn btn-success btn-xs" href="{{url('user/sms/view-inbox/'.$h->id)}}" ><i class="fa fa-inbox"></i> {{language_data('Inbox')}}</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$h->id}}"><i class="fa fa-trash"></i> {{language_data('Delete')}}</a>
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
                        window.location.href = _url + "/user/sms/delete-sms/" + id;
                    }
                });
            });

        });
    </script>
@endsection