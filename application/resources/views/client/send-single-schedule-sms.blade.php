@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">  Gửi 1 tin lập lịch</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">  Gửi 1 tin lập lịch</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('user/sms/post-single-schedule-sms')}}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Phone Number')}}</label>
                                    <input type="text" class="form-control" name="phone_number" id="phone_number">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Schedule Time')}}</label>
                                    <input type="text" class="form-control datePickerNew" name="schedule_time">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Sender ID')}}</label>
<!-- huyhh                                    <input type="text" class="form-control" name="sender_id" id="sender_id"> -->
                                    <select class="selectpicker form-control" name="sender_id"  data-live-search="true">
                                        @foreach($sender_ids as $si)
                                            <option value="{{$si->sender_id}}">{{$si->sender_id}}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Message')}}</label>
                                    <textarea class="form-control" name="message" rows="5" id="message"></textarea>
                                    <span class="help text-uppercase"
                                          id="remaining">160 {{language_data('characters remaining')}}</span>
                                    <span class="help text-success" id="messages">1 {{language_data('message')}}
                                        (s)</span>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm pull-right"><i
                                            class="fa fa-send"></i> {{language_data('Send')}} </button>
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
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}

    <script>
        $(document).ready(function () {

            var $get_msg = $("#message");
            var $remaining = $('#remaining'),
                $messages = $remaining.next();

            $get_msg.keyup(function () {
                var chars = this.value.length,
                    messages = Math.ceil(chars / 160),
                    remaining = messages * 160 - (chars % (messages * 160) || messages * 160);

                $remaining.text(remaining + ' characters remaining');
                $messages.text(messages + ' message(s)');
            });
			
			$('.datePickerNew').datetimepicker({
				format: 'DD-MM-YYYY h:mm A',
				keepOpen: false,
				sideBySide:true
				
			});

        });
    </script>
<script type="text/javascript">
    $(".dateTimePicker").datetimepicker({format: 'yyyy-mm-dd hh:ii'});
</script>  	
@endsection