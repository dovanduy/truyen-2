@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('All Invoices')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('All Invoices')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">#</th>
                                    <th style="width: 10%;">{{language_data('Amount')}} (VNĐ)</th>
                                    <th style="width: 15%;">{{language_data('Invoice Date')}}</th>
                                    <th style="width: 15%;">{{language_data('Due Date')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 15%;">{{language_data('Type')}}</th>
                                    <th style="width: 30%;">{{language_data('Manage')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoices as $in)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{number_format($in->total,0,",",".")}}</td>
                                        <td>{{get_date_format($in->created)}}</td>
                                        <td>{{get_date_format($in->duedate)}}</td>
                                        <td>
                                            @if($in->status=='Unpaid')
                                                <span class="label label-warning">{{language_data('Unpaid')}}</span>
                                            @elseif($in->status=='Paid')
                                                <span class="label label-success">{{language_data('Paid')}}</span>
                                            @elseif($in->status=='Cancelled')
                                                <span class="label label-danger">{{language_data('Cancelled')}}</span>
                                            @else
                                                <span class="label label-info">{{language_data('Partially Paid')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($in->recurring=='0')
                                                <span class="label label-success"> {{language_data('Onetime')}}</span>
                                            @else
                                                <span class="label label-info"> {{language_data('Recurring')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{url('user/invoices/view/'.$in->id)}}" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> {{language_data('View')}}</a>
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

    <script>
        $(document).ready(function(){
            $('.data-table').DataTable();
        });
    </script>
@endsection