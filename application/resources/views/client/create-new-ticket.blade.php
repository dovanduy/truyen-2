@extends('client')


{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.css") !!}
	{!! Html::script("assets/ckeditor/ckeditor.js") !!}
	
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Create New Ticket')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-7">
                    <div class="panel">

                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Create New Ticket')}}</h3>
                        </div>

                        <div class="panel-body">
                            <form method="POST" action="{{ url('user/tickets/post-ticket') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <label for="subject">{{language_data('Subject')}}</label>
                                    <input type="text" class="form-control" id="subject" name="subject">
                                </div>

                                <div class="form-group">
                                    <label for="message">{{language_data('Message')}}</label>
                                    <textarea class="form-control" name="message"></textarea>
									<script>
										// Replace the <textarea id="editor1"> with a CKEditor
										// instance, using default configuration.
										//var message = CKEDITOR.instances.messageArea.getData();
									   CKEDITOR.replace( 'message', {
										height: '300px',
										enterMode: CKEDITOR.ENTER_BR, 
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

                                <div class="form-group">
                                    <label for="did">{{language_data('Department')}}</label>
                                    <select name="did" class="selectpicker form-control" data-live-search="true">
                                        @foreach($sd as $d)
                                            <option value="{{$d->id}}">{{$d->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit" name="add" class="btn btn-success"><i class="fa fa-plus"></i> {{language_data('Create Ticket')}}</button>
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
    {!! Html::script("assets/libs/wysihtml5x/wysihtml5x-toolbar.min.js")!!}
    {!! Html::script("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.js")!!}
	
@endsection
