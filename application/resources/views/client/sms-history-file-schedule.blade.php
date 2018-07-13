@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
    <style type="text/css">
        .twitter-typeahead { width: 100%; } 
        .tt-query, /* UPDATE: newer versions use tt-input instead of tt-query */
.tt-hint {
    width: 396px;
    height: 30px;
    padding: 8px 12px;
    font-size: 24px;
    line-height: 30px;
    border: 2px solid #ccc;
    border-radius: 8px;
    outline: none;
}

.tt-query { /* UPDATE: newer versions use tt-input instead of tt-query */
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
}

.tt-hint {
    color: #999;
}

.tt-menu { /* UPDATE: newer versions use tt-menu instead of tt-dropdown-menu */
    width: 422px;
    margin-top: 12px;
    padding: 8px 0;
    background-color: #fff;
    border: 1px solid #ccc;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    box-shadow: 0 5px 10px rgba(0,0,0,.2);
}

.tt-suggestion {
    padding: 3px 20px;
    font-size: 18px;
    line-height: 24px;
}

.tt-suggestion.tt-is-under-cursor { /* UPDATE: newer versions use .tt-suggestion.tt-cursor */
    color: #fff;
    background-color: #0097cf;

}
    </style>
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">Danh sách gửi tin theo lịch</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                 <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Find')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="form-horizontal" role="form" method="get" action="{{url('user/sms/historyFileSchedule')}}" enctype="multipart/form-data">

                                <div class="form-group">
                                    <label class="control-label col-xs-2">{{language_data('Mobile')}}</label>
                                    <div class="col-xs-6">
                                    <input type="text" class="form-control search-input" placeholder="Nhập số điện thoại" value="<?php echo isset($_GET['mobile'])?$_GET['mobile']:''?>" name="mobile" >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-2">Ngày gửi</label>
                                    <div class="col-xs-6">
                                    <input type="text" class="form-control submit_time" value="<?php echo isset($_GET['submit_time'])?$_GET['submit_time']:''?>" name="submit_time" placeholder="Nhập ngày gửi">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-xs-2">Gửi 1 tin lập lịch</label>
                                    <div class="col-xs-6">
                                    <select class="selectpicker form-control" name="gui_mot_tin_lap_lich" data-live-search="true">
                                            <option value="">Chọn</option>
                                            <?php
                                                foreach ($gui_mot_tin_lap_lich as $item) {
                                                    $selected="";
                                                    if(isset($_GET['gui_mot_tin_lap_lich']) && $item->file_send==$_GET['gui_mot_tin_lap_lich'])
                                                        $selected="selected";
                                                    echo "<option value='".$item->file_send."'".$selected
                                                    .">".$item->file_send."</option>";
                                                }
                                            ?>
                                    </select>
                                </div>
                                </div>
								<div class="form-group">
                                    <label class="control-label col-xs-2">Gửi tin lập lịch cho nhóm khách hàng</label>
                                    <div class="col-xs-6">
                                    <select class="selectpicker form-control" name="gui_tin_lap_lich_cho_nhom_kh" data-live-search="true">
                                            <option value="">Chọn</option>
                                            <?php
                                                foreach ($gui_tin_lap_lich_cho_nhom_kh as $item) {
                                                    $selected="";
                                                    if(isset($_GET['gui_tin_lap_lich_cho_nhom_kh']) && $item->file_send==$_GET['gui_tin_lap_lich_cho_nhom_kh'])
                                                        $selected="selected";
                                                    echo "<option value='".$item->file_send."'".$selected
                                                    .">".$item->file_send."</option>";
                                                }
                                            ?>
                                    </select>
                                </div>
                                </div>
								<div class="form-group">
                                    <label class="control-label col-xs-2">Gửi tin sinh nhật</label>
                                    <div class="col-xs-6">
                                    <select class="selectpicker form-control" name="gui_tin_sinh_nhat" data-live-search="true">
                                            <option value="">Chọn</option>
                                            <?php
                                                foreach ($gui_tin_sinh_nhat as $item) {
                                                    $selected="";
                                                    if(isset($_GET['gui_tin_sinh_nhat']) && $item->file_send==$_GET['gui_tin_sinh_nhat'])
                                                        $selected="selected";
                                                    echo "<option value='".$item->file_send."'".$selected
                                                    .">".$item->file_send."</option>";
                                                }
                                            ?>
                                    </select>
                                </div>
                                </div>
								<div class="form-group">
                                    <label class="control-label col-xs-2">Gửi tin nhắc nhở</label>
                                    <div class="col-xs-6">
                                    <select class="selectpicker form-control" name="gui_tin_nhac_nho" data-live-search="true">
                                            <option value="">Chọn</option>
                                            <?php
                                                foreach ($gui_tin_nhac_nho as $item) {
                                                    $selected="";
                                                    if(isset($_GET['gui_tin_nhac_nho']) && $item->file_send==$_GET['gui_tin_nhac_nho'])
                                                        $selected="selected";
                                                    echo "<option value='".$item->file_send."'".$selected
                                                    .">".$item->file_send."</option>";
                                                }
                                            ?>
                                    </select>
                                </div>
                                </div>
								
								<div class="form-group">
                                    <label class="control-label col-xs-2">Lập lịch gửi từ file</label>
                                    <div class="col-xs-6">
                                    <select class="selectpicker form-control" name="lap_lich_gui_tu_file" data-live-search="true">
                                            <option value="">Chọn</option>
                                            <?php
                                                foreach ($lap_lich_gui_tu_file as $item) {
                                                    $selected="";
                                                    if(isset($_GET['lap_lich_gui_tu_file']) && $item->file_send==$_GET['lap_lich_gui_tu_file'])
                                                        $selected="selected";
                                                    echo "<option value='".$item->file_send."'".$selected
                                                    .">".$item->file_send."</option>";
                                                }
                                            ?>
                                    </select>
                                </div>
                                </div>
								
                                <div class="col-xs-offset-2 col-xs-10">   
                                 <button type="submit" style="margin-right: 10px;" class="btn btn-success"><i class="fa fa-plus"></i> {{language_data('Find')}} </button>
                                <a href="{{url('user/sms/historyFileSchedule')}}"><button type="button" class="btn btn-danger"><i class="fa fa-plus"></i> Reset </button></a>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Danh sách gửi tin theo lịch</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 5%;">{{language_data('SL')}}#</th>
                                    <th style="width: 10%;">{{language_data('Sender')}}</th>
                                    <th style="width: 15%;">{{language_data('Receiver')}}</th>
                                    <th style="width: 25%;">{{language_data('Message')}}</th>
                                    <th style="width: 20%;">File</th>
                                    <th style="width: 15%;">Ngày gửi</th>
                                    <th style="width: 10%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sms_history as $h)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Sender"><p>{{$h->sender}}</p></td>
                                        <td data-label="Receiver"><p>{{$h->receiver}}</p></td>
                                        <td>{{$h->original_msg}}</td>
                                        <td>
                                           {{$h->file_send}}
                                        </td>
                                        <td data-label="Date"><p>{{get_datetime_format($h->submit_time)}}</p></td>

                                        <td data-label="Actions">
                                           <a class="btn btn-success btn-xs" href="{{url('user/sms/manage-update-schedule-sms-file/'.$h->id)}}"><i class="fa fa-edit"></i> {{language_data('Edit')}}</a>
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
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}
    {!! Html::script("assets/libs/typeahead.bundle.js")!!}
        <script>
            jQuery(document).ready(function($) {
                var engine = new Bloodhound({
                    remote: {
                        url: _url+'/user/findMobileSchedule?q=%QUERY%',
                        wildcard: '%QUERY%'
                    },
                    datumTokenizer: Bloodhound.tokenizers.whitespace('q'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace
                });

                $(".search-input").typeahead({
                    hint: true,
                    highlight: true,
                    minLength: 2
                }, {
                    source: engine.ttAdapter(),
                    name: 'mobileList',
                    display: function(item){ 
                            return item.receiver
                        },
                    templates: {
                        empty: [
                            '<div class="list-group search-results-dropdown"><div class="list-group-item">Không có kết quả phù hợp.</div></div>'
                        ],
                        header: [
                            '<div class="list-group search-results-dropdown">'
                        ],
                        suggestion: function (data) {
                            return '<div class="list-group-item">' + data.receiver + '</div>'
                        }
                    }
                });
            });
        </script>
    <script>
        $(document).ready(function(){
            $('.data-table').DataTable();
            $('.submit_time').datetimepicker({
                keepOpen: false,
                format: 'DD-MM-YYYY'
            });
            /*For Delete Group*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/user/sms/delete-schedule-sms-file/" + id;
                    }
                });
            });
        });
    </script>
@endsection