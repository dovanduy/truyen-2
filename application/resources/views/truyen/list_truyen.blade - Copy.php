@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
    <style>
     .data-table th{
    white-space: nowrap;
}

.data-table td{
  white-space: nowrap;
}
.table tbody tr td {
    font-size:12px;
}


   </style>
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">
               Người dùng
            </h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Danh sách phòng</h3>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12" id="modulegrouptree">
                                <ul style="list-style: none;">
                                    @foreach($rooms as $item)
                                    <li>
                                        <a href="{{url('client/show-user-spt-by-catetory/'.$item->id)}}">{{$item->name}}</a>
                                        <span>(
                                        <?php
                                            $sql="select count(*) as total from sys_clients where is_delete=0 and room_id='".$item->id."'";
                                            $result = DB::selectOne($sql);
                                            echo $result->total;
                                        ?>
                                        )
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div id="chart_div"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                             <div class="row" style="margin:0px"><h3 class="panel-title pull-left">Danh sách người dùng</h3>
                             <a href="{{url('client/showInsertUserSpt/'.Request::route('cateIdSub'))}}"><button class="btn btn-success pull-right"><i class="fa fa-plus"></i> Thêm </button></a></div>

                        </div>
                        <div class="panel-body p-none">

                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <!-- <th>{{language_data('SL')}}#</th> -->
                                    <th>Tên</th>
                                    <th>Phòng</th>
                                    <th>Email</th>
									<th>Điện thoại</th>
                                    <th>Last login </th>
									<th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($clients as $item)
                                    <tr>
                                        <td>{{$item->name}}</td>
                                        <td>
                                            <?php
                                            $room = \App\Models\Room::find($item->room_id)->first();
                                            echo $room->name;
                                            ?>
                                        </td>
                                        <td>{{$item->email}}</td>
                                        <td>{{$item->mobile}}</td>
                                        <td>@if(!empty($item->lastlogin)) {{ date('d-m-Y H:i:s',strtotime($item->lastlogin))}}@endif</td>
                                        <td>
                                            <a class="btn btn-success btn-xs" href="{{url('client/edit-user-spt/'.$item->id)}}"><i class="fa fa-edit"></i>Sửa</a>
                                            <a class="btn btn-success btn-xs" href="{{url('client/show-list-permission/'.$item->id)}}"><i class="fa fa-edit"></i>Phân quyền</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$item->id}}"><i class="fa fa-trash"></i> Xoá</a>
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
    <script type="text/javascript">
  var table = $('.data-table').DataTable( {
                                        rowId: '',
                                        "bDestroy": true,
                                        ordering: false,
                                        "searching": true,
                                        //"bAutoWidth": false,
                                        //"autoWidth": true,
                                        //"scrollX": true,
                                        "scrollX": true,
                                        "bAutoWidth": true,
                                        "oLanguage": {
                                            "sLengthMenu": "Hiện _MENU_ Dòng",
                                            "sSearch": "",
                                            "sEmptyTable": "Không có dữ liệu",
                                            "sProcessing": "Đang xử lý...",
                                            "sZeroRecords": "Không tìm thấy dòng nào phù hợp",
                                            "sInfo": "Đang xem _START_ đến _END_ trong tổng số _TOTAL_ mục",
                                            "sInfoEmpty": "Đang xem 0 đến 0 trong tổng số 0 mục",
                                            "sInfoFiltered": "(được lọc từ _MAX_ mục)",
                                            "sInfoPostFix": "",
                                            "sUrl": ""
                                        }
                                      });
                                        
                                        

</script>
<script>
    $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/client/delete-user-spt/" + id;
                    }
                });
            });
</script>
@endsection
