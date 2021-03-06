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
  /*white-space: nowrap;*/
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
                Danh sách truyện
            </h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                             <div class="row" style="margin:0px"><h3 class="panel-title pull-left">Danh sách truyện</h3>
                             <a href="{{url('client/them-truyen')}}"><button class="btn btn-success pull-right"><i class="fa fa-plus"></i> Thêm </button></a></div>

                        </div>
                        <div class="panel-body p-none">

                            <table class="table data-table table-hover table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width:10%">#</th>
                                    <th>Tên</th>
                                    <th>Nguồn</th>
                                    <th>Thể loại</th>
                                    <th>Tổng số chap</th>
									<th>Ngày tạo</th>
									<th style="width:15%">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- @foreach($truyens as $item)
                                    <tr>
                                       <td><img src="{{URL::asset('files/'.$item->folder_name.'/avatar/'.$item->img_avatar)}}" width="100" height="100"></td>
                                       <td>{{$item->title}}</td>
                                       <td>
                                           <?php
                                            $website = \App\Models\Website::where('id',$item->website_id)->first();
                                            echo $website->name;
                                           ?>
                                       </td>
                                       <td>
                                           <?php
                                            $cate = \App\Models\Cate::where('id',$item->cate_id)->first();
                                            echo $cate->name;
                                           ?>
                                       </td>
                                       <td>{{$item->total_chap}}</td>
                                       <td>{{ date('d-m-Y',strtotime($item->created_date))}}</td>
                                        <td>
                                            <a class="btn btn-success btn-xs" href="{{url('client/sua-truyen/'.$item->id)}}"><i class="fa fa-edit"></i>Sửa</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$item->id}}"><i class="fa fa-trash"></i> Xoá</a>
                                        </td>
                                    </tr>
                                @endforeach -->
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
                                        ordering: true,
                                        "searching": true,
                                        //"bAutoWidth": false,
                                        //"autoWidth": true,
                                        //"scrollX": true,
                                        "scrollX": false,
                                        "bAutoWidth": false,
                                        "processing": true,
                                        "serverSide": true,
                                        "ajax":{
                                         "url": "{{ url('client/pagingTruyen') }}",
                                         "dataType": "json",
                                         "type": "GET"
                                       },
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
                                        },
                                        "columns": [
                                            { "data": "img","bSortable": false },
                                            { "data": "title"},
                                            { "data": "website_id" },
                                            { "data": "cate_id" },
                                            { "data": "total_chap" },
                                            { "data": "created_date" },
                                            { "data": "action","bSortable": false },
                                        ]  
                                      });

</script>
<script>
    $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("Are you sure?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/client/delete-truyen/" + id;
                    }
                });
            });
</script>
@endsection
