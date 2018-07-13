@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Invoice')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')

            <div class="panel">
                <div class="panel-body p-none">
                    <div class="p-20">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-6 p-t-20">
                                    <div class="m-b-5">
                                        <img src="<?php echo asset(app_config('AppLogo')); ?>" alt="Logo">
                                    </div>
                                    <address>
                                        {!!app_config('Address')!!}
                                    </address>

                                    <div class="m-t-20">
                                        <h4 class="panel-title">{{language_data('Invoice To')}}: </h4>
                                        <h4 class="invoice-to-client-name">{{$inv->client_name}}</h4>
                                    </div>

                                    <address>
                                        {{$client->address1}} <br>
                                        {{$client->address2}} <br>
                                        {{$client->state}}, {{$client->city}} - {{$client->postcode}},  {{$client->country}}
                                        <br><br>
                                        {{language_data('Phone')}}: {{$client->phone}}
                                        <br>
                                        {{language_data('Email')}}: {{$client->email}}
                                    </address>

                                </div>

                                <div class="col-lg-6 p-t-20">


                                    <div class="btn-group pull-right" aria-label="...">

                                        @if($inv->status=='Unpaid' || $inv->status=='Partially Paid')
                                            <a href="#" data-toggle="modal" data-target="#pay-invoice" class="btn btn-success  btn-sm pay-invoice"><i class="fa fa-check"></i> {{language_data('Pay')}}</a>
                                        @endif

                                        <a href="{{url('user/invoices/client-iview/'.$inv->id)}}" target="_blank" class="btn btn-danger  btn-sm"><i class="fa fa-paper-plane-o"></i> {{language_data('Preview')}}</a>

                                        <a href="{{url('user/invoices/download-pdf/'.$inv->id)}}" class="btn btn-pdf  btn-sm download-pdf"><i class="fa fa-file-pdf-o"></i> {{language_data('PDF')}}</a>
                                        <a href="{{url('user/invoices/iprint/'.$inv->id)}}" target="_blank" class="btn btn-primary  btn-sm"><i class="fa fa-print"></i> {{language_data('Print')}}</a>
                                        <br>
                                        <br>

                                        <div class="modal fade" id="pay-invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModalLabel">{{language_data('Pay Invoice')}}</h4>
                                                    </div>
                                                    <div class="modal-body">

                                                        <form class="form-some-up" role="form" action="{{url('user/invoices/pay-invoice')}}" method="post">

                                                            <div class="form-group">
                                                                <label>{{language_data('Select Payment Method')}}</label>
                                                                <select class="selectpicker form-control" name="gateway">
                                                                    @foreach($payment_gateways as $pg)
                                                                        <option value="{{$pg->settings}}">{{$pg->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="text-right">
                                                                <input type="hidden" value="{{$inv->id}}" name="cmd">
                                                                <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">{{language_data('Close')}}</button>
                                                                <button type="submit" class="btn btn-success btn-sm">{{language_data('Pay')}}</button>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                            <div class="m-t-20">
                                                <div class="bill-data">
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Invoice No')}}:</span>
                                                        <span class="bill-data-value">#{{$inv->id}}</span>
                                                    </p>
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Invoice Status')}}:</span>
                                                        @if($inv->status=='Unpaid')
                                                            <span class="bill-data-value"><span class="bill-data-status label-warning">{{language_data('Unpaid')}}</span></span>
                                                        @elseif($inv->status=='Paid')
                                                            <span class="bill-data-value"><span class="bill-data-status label-success">{{language_data('Paid')}}</span></span>
                                                        @elseif($inv->status=='Partially Paid')
                                                            <span class="bill-data-value"><span class="bill-data-status label-info">{{language_data('Partially Paid')}}</span></span>
                                                        @else
                                                            <span class="bill-data-value"><span class="bill-data-status label-danger">{{language_data('Cancelled')}}</span></span>
                                                        @endif
                                                    </p>
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Invoice Date')}}:</span>
                                                        <span class="bill-data-value">{{get_date_format($inv->created)}}</span>
                                                    </p>
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Due Date')}}:</span>
                                                        <span class="bill-data-value">{{get_date_format($inv->duedate)}}</span>
                                                    </p>
                                                    @if($inv->status=='Paid')
                                                        <p class="m-b-5">
                                                            <span class="bill-data-title">{{language_data('Paid Date')}}:</span>
                                                            <span class="bill-data-value">{{get_date_format($inv->datepaid)}}</span>
                                                        </p>
                                                    @endif

                                                </div>
                                            </div>

                                    </div>


                                </div>

                            </div>

                            <div class="col-lg-12">
                                <table class="table invoice-items invoice-view">
                                    <thead>
                                    <tr class="h5 text-dark">
                                        <th id="cell-id" class="text-semibold" style="width: 5%;">#</th>
                                        <th id="cell-item" class="text-semibold" style="width: 65%;">{{language_data('Item')}}</th>
                                        <th id="cell-price" class="text-center text-semibold" style="width: 10%;">{{language_data('Price')}}</th>
                                        <th id="cell-qty" class="text-center text-semibold" style="width: 10%;">{{language_data('Quantity')}}</th>
                                        <th id="cell-total" class="text-semibold" style="width: 10%;">{{language_data('Total')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i='1'; ?>
                                    @foreach($inv_items as $it)
                                        <tr>
                                            <td style="width: 5%;"><?php echo $i++; ?></td>
                                            <td class="text-semibold text-dark" style="width: 65%;">{{$it->item}}</td>
                                            <td class="text-center" style="width: 10%;">{{$it->price}}</td>
                                            <td class="text-center" style="width: 10%;">{{$it->qty}}</td>
                                            <td style="width: 10%;">{{$it->subtotal}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-lg-12">
                                <div class="invoice-summary">
                                    <div class="row">
                                        <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
                                            <div class="inv-block">
                                                <h4 class="count-title">{{language_data('Subtotal')}}</h4>
                                                {{$inv->subtotal}}
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                            <div class="inv-block">
                                                <h4 class="count-title">{{language_data('Tax')}}</h4>
                                                {{$tax_sum}}
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                            <div class="inv-block">
                                                <h4 class="count-title">{{language_data('Discount')}}</h4>
                                                {{$dis_sum}}
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 col-lg-offset-2 col-md-offset-1 col-sm-offset-1 text-right">
                                            <div class="inv-block last">
                                                <h4 class="count-title">{{language_data('Grand Total')}}</h4>
                                                {{$inv->total}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="well m-t-5">
									<p>{{language_data('Number2Words')}} {{$total_docchu}}</p>
                                    </div>									
                                </div>


                                @if($inv->note!='')
                                    <div class="well m-t-5"><b>{{language_data('Invoice Note')}}: </b>{{$inv->note}}</div>
                                @endif

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
@endsection