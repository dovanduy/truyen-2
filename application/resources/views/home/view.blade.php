<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>TITLE</title>
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

	<footer>

    <div class="container">
      <div class="row">

        <div class="col-lg-4 col-md-6">
          <div class="footer-section">

            <a class="logo" href="#"><img src="images/logo.png" alt="Logo Image"></a>
            <p class="copyright">Bona @ 2017. All rights reserved.</p>
            <p class="copyright">Designed by <a href="https://colorlib.com" target="_blank">Colorlib</a></p>
            <ul class="icons">
              <li><a href="#"><i class="ion-social-facebook-outline"></i></a></li>
              <li><a href="#"><i class="ion-social-twitter-outline"></i></a></li>
              <li><a href="#"><i class="ion-social-instagram-outline"></i></a></li>
              <li><a href="#"><i class="ion-social-vimeo-outline"></i></a></li>
              <li><a href="#"><i class="ion-social-pinterest-outline"></i></a></li>
            </ul>

          </div><!-- footer-section -->
        </div><!-- col-lg-4 col-md-6 -->

        <div class="col-lg-4 col-md-6">
            <div class="footer-section">
            <h4 class="title"><b>CATAGORIES</b></h4>
            <ul>
              <li><a href="#">BEAUTY</a></li>
              <li><a href="#">HEALTH</a></li>
              <li><a href="#">MUSIC</a></li>
            </ul>
            <ul>
              <li><a href="#">SPORT</a></li>
              <li><a href="#">DESIGN</a></li>
              <li><a href="#">TRAVEL</a></li>
            </ul>
          </div><!-- footer-section -->
        </div><!-- col-lg-4 col-md-6 -->

        <div class="col-lg-4 col-md-6">
          <div class="footer-section">

            <h4 class="title"><b>SUBSCRIBE</b></h4>
            <div class="input-area">
              <form>
                <input class="email-input" type="text" placeholder="Enter your email">
                <button class="submit-btn" type="submit"><i class="icon ion-ios-email-outline"></i></button>
              </form>
            </div>

          </div><!-- footer-section -->
        </div><!-- col-lg-4 col-md-6 -->

      </div><!-- row -->
    </div><!-- container -->
  </footer>



	<!-- SCIPTS -->

	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
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
        
        $('#back-to-top').tooltip('show');

});

    $(function(){
      $('#chooseChapNumber,#chooseChapNumberFooter').change(function(){
        location.href=$(this).val();
      });
    })
	</script>
</body>
</html>
