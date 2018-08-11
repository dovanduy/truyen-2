<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>{{$truyen->title}}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">


	<!-- Font -->

	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<!-- Stylesheets -->

	{!! Html::style("assets/home/category-sidebar/css/bootstrap.min.css") !!}


	{!! Html::style("assets/home/common-css/ionicons.css") !!}

	{!! Html::style("assets/home/single-post-2/css/styles.css") !!}
	   {!! Html::style("assets/home/single-post-2/css/responsive.css") !!}
	   {!! Html::style("assets/home/category-sidebar/css/custom.css") !!}
	<style type="text/css">
		.each-page {
    margin-top: 25px;
    text-align: center;
    width: 100%;
    padding: 20px;
    background: #FFFFFF;
}
.each-page img {
    max-width: 100%;
    margin: 0 auto 15px;
}
img {
    vertical-align: middle;
}
.dropdown-manga {
    height: 31px;
    width: 100%;
    -webkit-appearance: none;
    padding-left: 5px;
    /*background: url('http://cdn.truyentranh.net/frontend/images/toolbar-dropdown-icon.png') no-repeat #fff;*/
    background-position-y: 8px;
    background-position-x: 293px;
    box-shadow: none;
}

select.soflow{
   -webkit-appearance: button;
   -webkit-border-radius: 2px;
   -webkit-box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
   -webkit-padding-end: 20px;
   -webkit-padding-start: 2px;
   -webkit-user-select: none;
   background-image: url(http://i62.tinypic.com/15xvbd5.png), -webkit-linear-gradient(#FAFAFA, #F4F4F4 40%, #E5E5E5);
   background-position: 97% center;
   background-repeat: no-repeat;
   border: 1px solid #AAA;
   color: #555;
   font-size: inherit;
   /*margin: 20px;*/
   /*overflow: hidden;*/
   /*padding: 5px 10px;*/
   padding-left: 5px;
   text-overflow: ellipsis;
   white-space: nowrap;
   height: 31px;
   width: 100%;
   padding-right: 80px;
}
.back-to-top {
    cursor: pointer;
    position: fixed;
    bottom: 20px;
    right: 20px;
    display:none;
}
/* ---------------------------------
8. FOOTER
--------------------------------- */

footer{ padding: 70px 0 30px; text-align: center; background: #fff; }

footer .footer-section{ margin-bottom: 40px; }

footer .footer-section .title{ margin-bottom: 20px; }

footer .footer-section ul > li{ margin: 0 5px; }

footer .copyright{ margin: 10px 0 20px; }

footer .icons > li > a{ height: 40px; width: 40px; border-radius: 40px; line-height: 40px; text-align: center; 
  transition: all .3s; box-shadow: 0px 0px 2px rgba(0,0,0,1); background: #498BF9; color: #fff; }

footer .icons > li > a:hover{ transform: translateY(-2px); box-shadow: 5px 10px 20px rgba(0,0,0,.3); }
.each-page {
    width: 80%;
    margin: 0 auto;
}
	</style>

</head>
<body >

	

	
@include('sub.top_menu')
	<section class="post-area">
		<div class="container">
			<div class="row" style="float:right;margin-top: 10px;margin-bottom: 10px">
                    <div class="col-xs-2 paddfixboth">
                        <a href="http://truyentranh.net/tinh-dieu-vi-lai/Chap-005" class="LeftArrow" title="Tinh Diệu Vị Lai Chap 005">
                            <img src="http://cdn.truyentranh.net/frontend/images/arrowleft.jpg" alt="leftarrow">
                        </a>
                    </div>
                    <div class="col-xs-8 paddselectfix">
                        <select class="soflow" id="chooseChapNumber" data-placeholder="Chọn chương truyện" rel="chap-select">
                        	@foreach($truyenChaps as $item)
                          	 <option value="{{url('view/'.$truyen->slug.'/chap-'.$item->chap_number)}}" @if(Request::path()=='view/'.$truyen->slug.'/chap-'.$item->chap_number) selected="selected" @endif>
  						                {{$truyen->title}} Chap {{$item->chap_number}}
  						              </option>
						              @endforeach
						    

						</select>
                    </div>
                    <div class="col-xs-2 paddfixboth rightalign">
                        <a href="javascript:void(0);" class="RightArrow Off" title="">
                            <img src="http://cdn.truyentranh.net/frontend/images/arrowright.jpg" alt="rightarrow">
                        </a>
                    </div>
                </div>
                <div style="clear:both;"></div>
			<div class="row">
				<div class="col-md-12">

					<div class="main-post">

						<div class="post-top-area">

							<h3 class="title"><a href="#"><b>{{$truyen->title}} Chap {{$chapNumber}}
</b></a></h3>

						</div><!-- post-top-area -->
						<div class="each-page">
								@foreach($listImg as $item)
									<img src="{{URL::asset('files/'.$truyen->folder_name.'/'.$item->folder_name.'/'.$item->chap_img)}}" ><br/>
								@endforeach

						</div>


					</div><!-- main-post -->
				</div><!-- col-lg-8 col-md-12 -->
			</div><!-- row -->
			<div class="row" style="float:right;margin-top: 10px;margin-bottom: 10px">
                    <div class="col-xs-2 paddfixboth">
                        <a href="http://truyentranh.net/tinh-dieu-vi-lai/Chap-005" class="LeftArrow" title="Tinh Diệu Vị Lai Chap 005">
                            <img src="http://cdn.truyentranh.net/frontend/images/arrowleft.jpg" alt="leftarrow">
                        </a>
                    </div>
                    <div class="col-xs-8 paddselectfix">
                        <select class="soflow" id="chooseChapNumberFooter" data-placeholder="Chọn chương truyện" rel="chap-select">
                        @foreach($truyenChaps as $item)
                             <option value="{{url('view/'.$truyen->slug.'/chap-'.$item->chap_number)}}" @if(Request::path()=='view/'.$truyen->slug.'/chap-'.$item->chap_number) selected="selected" @endif>
                              {{$truyen->title}} Chap {{$item->chap_number}}
                            </option>
                          @endforeach

						</select>
                    </div>
                    <div class="col-xs-2 paddfixboth rightalign">
                        <a href="javascript:void(0);" class="RightArrow Off" title="">
                            <img src="http://cdn.truyentranh.net/frontend/images/arrowright.jpg" alt="rightarrow">
                        </a>
                    </div>
                </div>
                <div style="clear:both;"></div>
		</div><!-- container -->
	</section><!-- post-area -->
 <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="Click to return on the top page" data-toggle="tooltip" data-placement="left" style="display: none;"><i class="fa fa-chevron-up" aria-hidden="true"></i></a>
	@include('sub.footer')
<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
<input type="hidden" id="_url" value="{{url('/')}}">
  <!-- SCIPTS -->

  <!-- <script src="common-js/tether.min.js"></script> -->
  <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('input[name="_token"]').val()
        }
    });

    var _url=$('#_url').val();

</script>

	<!-- SCIPTS -->

	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
  <!-- <script src="common-js/scripts.js"></script> -->
  {!! Html::script("assets/js/truyen.js") !!}
	<script type="text/javascript">
		$(document).ready(function(){
     $(window).scroll(function () {
            if ($(this).scrollTop() > 50) {
                $('#back-to-top').fadeIn();
            } else {
                $('#back-to-top').fadeOut();
            }
        });
        // scroll body to 0px on click
        $('#back-to-top').click(function () {
            $('#back-to-top').tooltip('hide');
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        
        //$('#back-to-top').tooltip('show');

});

    $(function(){
      $('#chooseChapNumber,#chooseChapNumberFooter').change(function(){
        location.href=$(this).val();
      });
    })
	</script>
</body>
</html>
