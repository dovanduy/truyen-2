@extends('client')
@section('style')
{!! Html::style("assets/css/bootstrap-fileupload.min.css") !!}
@endsection
@section('content')

<section class="wrapper-bottom-sec">
    <div class="p-30">
        <h2 class="page-title">Sửa truyện</h2>
    </div>
    <div class="p-30 p-t-none p-b-none">
        @include('notification.notify')
        <div class="row">
            <div class="col-lg-12">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Sửa truyện</h3>
                    </div>
                    <div class="panel-body" id="showLoading">
                        <form class="form-horizontal" role="form" method="post" action="{{url('client/post-sua-truyen')}}" >
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Tên truyện</label>
                                <div class="col-sm-10">
                                  <input type="text" name="title" id="title" value="{{$truyen->title}}" placeholder="Nhập tên truyện" class="form-control">
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Url</label>
                                <div class="col-sm-10">
                                  <input type="text" name="url" id="url" value="{{$truyen->url}}" placeholder="Nhập url" class="form-control">
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Nguồn</label>
                                <div class="col-sm-10">
                                  <select class="selectpicker form-control" name="website_id" id="website_id"  data-live-search="true">
                                    <option value="">Chọn</option>
                                    @foreach($websites as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Thể loại</label>
                                <div class="col-sm-10">
                                  <select class="cate_id selectpicker form-control" name="cate_id" id="cate_id"  data-live-search="true">
                                    <option value="">Chọn</option>
                                    @foreach($cates as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Hình đại diện</label>
                                <div class="col-sm-10">
                                  <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="fileupload-preview thumbnail" style="width: 150px;">
                                      <img src="{{URL::asset('files/'.$truyen->folder_name.'/avatar/'.$truyen->img_avatar)}}" id="imgAvatar"/>
                                  </div>
                                  <div>
                                    <!-- <span class="btn btn-file btn-success"><span class="fileupload-new">Select image</span> -->
                                    <span class="fileupload-exists">Change</span>
                                    <!-- <input type="file" name="img"/></span> -->
                                    <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                                    <a href="#" class="btn btn-danger" id="btnGetImg">Get image</a>
                                </div>
                            </div>
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Tổng số chap</label>
                                <div class="col-sm-3">
                                    <div class="input-group input-group-lg">
                                        <input type="text" class="form-control" id="total_chap" name="total_chap" placeholder="Tổng số chap" style="font-size: 13px" value="{{$truyen->total_chap}}">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" style="font-size: 13px" id="btnTotalChap">Get</button>
                                        </span>
                                    </div>
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Mô tả</label>
                                <div class="col-sm-10">
                                  <textarea class="form-control" name="summary" rows="10" id="summary">{{$truyen->summary}}</textarea>
                                  <script>
                                        // Replace the <textarea id="editor1"> with a CKEditor
                                        // instance, using default configuration.
                                        //var message = CKEDITOR.instances.messageArea.getData();
                                       CKEDITOR.replace( 'summary', {
                                        height: '300px',
                                        enterMode: CKEDITOR.ENTER_BR, 
                                        entities:false,
                                        basicEntities:false,
                                        htmlEncodeOutput:false,
                                        toolbar:    
                                            [
                                                [,'Preview','Templates'],
                                                           ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
                                                           ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                                                           '/',
                                                           ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
                                                           ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
                                                           ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                                                           ['BidiLtr', 'BidiRtl' ],
                                                           ['Link','Unlink','Anchor'],
                                                           ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'],
                                                           '/',
                                                           ['Styles','Format','Font','FontSize'],
                                                           ['TextColor','BGColor'],
                                                           ['Maximize','ShowBlocks','Syntaxhighlight']
                                         ],
                                         //filebrowserWindowWidth  : 300,
                                         //filebrowserWindowHeight : 300,
                                         filebrowserBrowseUrl : '../../assets/ckfinder/ckfinder.html',
                         
                                         filebrowserImageBrowseUrl : '../../assets/ckfinder/ckfinder.html?type=Images'
                                        });

                                </script>
                              </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-sm-10">
                                  <input type="hidden" name="linkFile" id="linkFile"/>
                                  <input type="hidden" name="id" value="{{$truyen->id}}">
                                    <button type="submit" class="btn btn-success btn-sm pull-left"><i class="fa fa-send"></i> Sửa </button>
                              </div>
                            </div>
                    
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
{!! Html::script("assets/js/form-elements-page.js")!!}
{!! Html::script("application/resources/views/truyen/js/script.js")!!}
<script>
    $(document).ready(function(){
        $('.selectpicker').selectpicker();
        $('.selectpicker').selectpicker('val',{{$truyen->website_id}});
        $('.cate_id').selectpicker();
        $('.cate_id').selectpicker('val',{{$truyen->cate_id}});
        Pos.initGetImg();
        Pos.initGetTotalChap();
    });

</script>
@endsection
