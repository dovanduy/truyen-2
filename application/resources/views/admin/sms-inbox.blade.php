@extends('admin')
{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/css/get_inbox.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View')}} {{language_data('Inbox')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                        </div>
                        <div class="panel-body">

                            <div class="chat">
                                <div class="chat-history">
                                    <ul class="chat-ul">
                                        @foreach($sms_inbox as $sms)

                                            @if($sms->send_by=='sender')
                                                <li>
                                                    <div class="message-data">
                                                        <span class="message-data-name"> <i class="fa fa-circle you"></i> {{$inbox_info->sender}} </span>
                                                        <span class="message-data-time msg-part-you"> <i class="fa fa-clock-o you"></i> {{get_datetime_format($sms->created_at)}} </span>
                                                        <span class="msg-part-you text-success"><i class="fa fa-inbox"></i> {{$sms->amount}} {{language_data('Message')}}(s) </span>
                                                    </div>
                                                    <div class="message you-message">{{$sms->original_msg}}</div>
                                                    <div class="msg-info text-success">
                                                        <span class="msg-status">{{$sms->status}}</span>
                                                    </div>
                                                </li>
                                            @endif

                                            @if($sms->send_by=='receiver')
                                                <li class="clearfix">
                                                    <div class="message-data align-right">
                                                        <span class="msg-part-me text-success"> {{$sms->amount}} {{language_data('Message')}}(s) <i class="fa fa-inbox"></i></span>
                                                        <span class="message-data-time msg-part-me"> {{get_datetime_format($sms->created_at)}} <i class="fa fa-clock-o you"></i></span>
                                                        <span class="message-data-name"> {{$inbox_info->receiver}} </span> <i class="fa fa-circle me"></i>
                                                    </div>

                                                    <div class="message me-message float-right"> {{$sms->original_msg}} </div>

                                                    <div class="msg-info float-right me">
                                                        <span class="msg-status">{{$sms->status}}</span>
                                                    </div>
                                                </li>
                                            @endif
                                       @endforeach
                                    </ul>

                                </div> <!-- end chat-history -->

                            </div> <!-- end chat -->
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
