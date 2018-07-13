@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30"></div>
        <div class="p-15 p-t-none p-b-none m-l-10 m-r-10">
            @include('notification.notify')
        </div>

        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('Invoices History')}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $invoices_json->render() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('Tickets History')}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $tickets_json->render() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('SMS Success History')}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $sms_status_json->render() !!}
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('SMS History By Date')}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $sms_history->render() !!}
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel-body ">
                        <div class="row">
                            <div class="panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{{language_data('Recent 5 Invoices')}}</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-hover table-ultra-responsive">
                                        <thead>
                                        <tr>
                                            <th style="width: 45px;">{{language_data('SL')}}</th>
                                            <th style="width: 20px;">{{language_data('Amount')}} (VNĐ)</th>
                                            <th style="width: 20px;">{{language_data('Due Date')}}</th>
                                            <th style="width: 15px;">{{language_data('Status')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($recent_five_invoices as $inv)
                                            <tr>
                                                <td data-label="client">
                                                    <p> {{$loop->iteration}} </p>
                                                </td>
                                                <td data-label="Amount"><p><a href="{{url('user/invoices/view/'.$inv->id)}}">{{number_format($inv->total,0,",",".")}}</a> </p>
                                                </td>
                                                <td data-label="Due Date"><p>{{get_date_format($inv->duedate)}}</p></td>
                                                @if($inv->status=='Paid')
                                                    <td data-label="Status"><p class="label label-success label-xs">{{language_data('Paid')}}</p></td>
                                                @elseif($inv->status=='Unpaid')
                                                    <td data-label="Status"><p class="label label-warning label-xs">{{language_data('Unpaid')}}</p></td>
                                                @elseif($inv->status=='Partially Paid')
                                                    <td data-label="Status"><p class="label label-info label-xs">{{language_data('Partially Paid')}}</p></td>
                                                @else
                                                    <td data-label="Status"><p class="label label-danger label-xs">{{language_data('Cancelled')}}</p></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-6 p-none">
                    <div class="panel-body ">
                        <div class="row">
                            <div class="panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{{language_data('Recent 5 Support Tickets')}}</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-hover table-ultra-responsive">
                                        <thead>
                                        <tr>
                                            <th style="width: 30%;">{{language_data('SL')}}</th>
                                            <th style="width: 50%;">{{language_data('Subject')}}</th>
                                            <th style="width: 20%;">{{language_data('Date')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($recent_five_tickets as $rtic)
                                            <tr>
                                                <td data-label="email">
                                                    <p>{{$loop->iteration}}</p>
                                                </td>
                                                <td data-label="subject">
                                                    <p><a href="{{url('user/tickets/view-ticket/'.$rtic->id)}}">{{$rtic->subject}}</a></p>
                                                </td>
                                                <td data-label="date">
                                                    <p>{{get_date_format($rtic->date)}}</p>
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

            </div>
        </div>


    </section>

@endsection


{{--External Style Section--}}
@section('style')
    {!! Html::script("assets/libs/chartjs/chart.js")!!}
@endsection