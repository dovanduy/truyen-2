@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30 clearfix">
            <h2 class="page-title inline-block">{{language_data('All')}} {{language_data('Sender ID')}}</h2>
            <button class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#request-new-sender"><i class="fa fa-plus"></i> {{language_data('Request New Sender ID')}}</button>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('All')}} {{language_data('Sender ID')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 20%;">{{language_data('SL')}}#</th>
                                    <th style="width: 60%;">{{language_data('Sender ID')}}</th>
                                    <th style="width: 20%;">{{language_data('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sender_id as $si)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Sender ID"><p>{{$si->sender_id}}</p></td>

                                        @if($si->status=='unblock')
                                            <td data-label="Status"><p class="label label-success">{{language_data('Unblock')}}</p></td>
                                        @elseif($si->status=='block')
                                            <td data-label="Status"><p class="label label-danger">{{language_data('Block')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="label label-warning">{{language_data('Pending')}}</p></td>
                                        @endif

                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal -->
            <div class="modal fade" id="request-new-sender" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">{{language_data('Request New Sender ID')}}</h4>
                        </div>
                        <form class="form-some-up" role="form" method="post" action="{{url('user/sms/post-sender-id')}}">

                            <div class="modal-body">
                                <div class="form-group">
                                    <label>{{language_data('Sender ID')}}</label>
                                    <input type="text" class="form-control" required="" name="sender_id">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                <button type="button" class="btn btn-default" data-dismiss="modal"> {{language_data('Close')}} </button>
                                <button type="submit" class="btn btn-primary"> {{language_data('Send')}} </button>
                            </div>
                        </form>
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

    <script>
        $(document).ready(function(){
            $('.data-table').DataTable();
        });
    </script>
@endsection