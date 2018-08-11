<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>{{$truyen->title}}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">


	<!-- Font -->

	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">


	<!-- Stylesheets -->

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	{!! Html::style("assets/home/category-sidebar/css/bootstrap.min.css") !!}


	   {!! Html::style("assets/home/common-css/ionicons.css") !!}


	   {!! Html::style("assets/home/single-post-1/css/styles.css") !!}
	   {!! Html::style("assets/home/single-post-1/css/responsive.css") !!}
	{!! Html::style("assets/home/category-sidebar/css/custom.css") !!}
	<link rel="stylesheet" type="text/css" href="http://cdn.truyentranh.net/frontend/css/jquery.mCustomScrollbar.css">

	<style type="text/css">
		.post-image-img{
			text-align: center;
		}
		.imgResize{
			width:150px;
			
		}
		.total-chapter {
    margin-bottom: 15px;
}
.collapse-contain {
    border-bottom: 3px solid #0e977f;
    color: #0e977f;
    margin-bottom: 0;
    font-weight: 700;
}
.content {
    overflow: auto;
    position: relative;
    max-height: 400px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    padding: 15px 0 0 25px;
}
.content p {
    border-bottom: 1px dashed #d2d2d2;
    padding-bottom: 3px;
}
.content a {
    color: #222;
    transition: .3s;
}
.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar {
    background-color: #0e977f !important;
}
.date-release{
	float: right;
	margin-right: 10px;
}


	</style>
	<style type="text/css">
	.post-style-1 .blog-image {
    max-height: 300px;
    float: left;
    padding: 10px;
}
.blog-image img{
    width: 120px;
}
.card-img-top-250{
    text-align: center;
    background: #fff;
    padding: 10px;
}
.card-img-top-250 img{
    width: 176px;
    /*padding: 10px;*/
    border: 1px solid #ddd;
    border-radius: 5px;
}

/*.section {
    padding: 12px 0 40px;
}*/
.mgBottom {
    margin-bottom: 30px;
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
.ellipsis {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
</style>
</head>
<body >
@include('sub.top_menu')
	<section class="post-area section" style="margin-top: 10px">
		<div class="container">

			<div class="row">

				<div class="col-lg-8 col-md-12 no-right-padding">

					<div class="main-post">

						<div class="blog-post-inner">
							<h3 class="title"><a href="#"><b>{{$truyen->title}}</b></a></h3>
							<div class="post-image-img"><img class="imgResize" src="{{URL::asset('files/'.$truyen->folder_name.'/avatar/'.$truyen->img_avatar)}}" alt="Blog Image"></div>
							<p class="para">{!! $truyen->summary !!}
							</p>
							<!-- <ul class="tags">
								<li><a href="#">Mnual</a></li>
								<li><a href="#">Liberty</a></li>
								<li><a href="#">Recommendation</a></li>
								<li><a href="#">Inspiration</a></li>
							</ul> -->
						</div><!-- blog-post-inner -->

						<div class="post-icons-area">
							<ul class="post-icons">
								<!-- <li><a href="#"><i class="ion-heart"></i>57</a></li>
								<li><a href="#"><i class="ion-chatbubble"></i>6</a></li> -->
								<li><a href="#"><i class="ion-eye"></i>{{$truyen->total_view}}</a></li>
							</ul>

							<ul class="icons">
								<li>SHARE : </li>
								<li><a href="#"><i class="ion-social-facebook"></i></a></li>
								<!-- <li><a href="#"><i class="ion-social-twitter"></i></a></li>
								<li><a href="#"><i class="ion-social-pinterest"></i></a></li> -->
							</ul>
						</div>
						<div class="total-chapter">
                        <p class="collapse-contain"><span class="text-left">Danh sách chương</span></p>
                        <section id="examples">
					   <div class="content mCustomScrollbar">
					   	@foreach($truyenChaps as $item)
					      <p><a href="{{url('view/'.$truyen->slug.'/chap-'.$item->chap_number)}}" target="_blank">{{$truyen->title}} Chap {{$item->chap_number}} </a>
					      <span class="date-release">22/07/2018</span></p>
					    @endforeach
					   </div>
					</section>
                    </div>
					</div><!-- main-post -->

				</div><!-- col-lg-8 col-md-12 -->

				@include('sub.right_menu')

			</div><!-- row -->

		</div><!-- container -->
	</section><!-- post-area -->
	<?php
                            use App\Models\TruyenChap;
                        ?>
	@if(!$truyens->isEmpty())
	<section class="recomended-area section">
		<div class="container">
			<div class="row">
				
                        
                        @foreach($truyens as $itemt)
                        <?php
                            /*if($itemt->website_id==1){//blogtruyen
                              $truyenChap = TruyenChap::where('truyen_id',$itemt->id)->orderBy('id','asc')->take(8)->get();
                            }else {//truyentranh
                              $truyenChap = TruyenChap::where('truyen_id',$itemt->id)->orderBy('id','desc')->take(8)->get();
                            }*/
                            $truyenChap = TruyenChap::where('truyen_id',$itemt->id)->orderBy('title','desc')->take(8)->get();
                        ?>
						<div class="col-lg-4 col-md-6 mgBottom">
							<div class="card h-100">
								<div class="single-post post-style-1">

									<div class="blog-image"><img src="{{URL::asset('files/'.$itemt->folder_name.'/avatar/'.$itemt->img_avatar)}}" alt="Blog Image"></div>
									<div class="blog-info">

										<h4 class="title ellipsis"><a href="#"><b>{{$itemt->title}}</b></a></h4>
										<div class="row" style="">
                                        <div class="col-xs-6" style="margin-right: 20px;margin-left: 20px">
                                          <div class="hotup-list">
                                            <?php
                                                $chapNumber=0;
                                            ?>
                                            @foreach($truyenChap as $item)
                                            @if($chapNumber<=3)
                                              <a class="latest-chap" href="#" target="_blank">Chap {{$item->chap_number}}</a>
                                              <br/>
                                            @endif
                                            <?php
                                                $chapNumber++;
                                            ?>
                                            @endforeach
                                                  </div>
                                        </div>
                                        <?php
                                                $chapNumber=0;
                                            ?>
                                        <div class="col-xs-6">
                                          <div class="hotup-list">
                                            @foreach($truyenChap as $item)
                                            @if($chapNumber<=7 && $chapNumber>3)
                                              <a class="latest-chap" href="#" target="_blank">Chap {{$item->chap_number}}</a>
                                              <br/>
                                            @endif
                                            <?php
                                                $chapNumber++;
                                            ?>
                                            @endforeach
                                                  </div>
                                        </div>

                                      </div>
										<ul class="post-footer">
											<li><a href="#"><i class="ion-heart"></i>57</a></li>
											<li><a href="#"><i class="ion-chatbubble"></i>6</a></li>
											<li><a href="#"><i class="ion-eye"></i>138</a></li>
										</ul>

									</div><!-- blog-info -->
								</div><!-- single-post -->
							</div><!-- card -->
						</div><!-- col-md-6 col-sm-12 -->
                        @endforeach
			</div><!-- row -->

		</div><!-- container -->
	</section>
	@endif

	@include('sub.footer')
	<input type="hidden" id="_url" value="{{url('/')}}">
  <!-- SCIPTS -->

  <!-- <script src="common-js/tether.min.js"></script> -->
  

	<!-- SCIPTS -->

	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
	<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('input[name="_token"]').val()
        }
    });

    var _url=$('#_url').val();

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
	<script type="text/javascript" src="http://cdn.truyentranh.net/frontend/js/jquery.mCustomScrollbar.concat.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
	<!-- <script src="common-js/scripts.js"></script> -->
  {!! Html::script("assets/js/truyen.js") !!}
	<script type="text/javascript">
		(function ($) {
    $(window).load(function () {

        $(".content").mCustomScrollbar();

    });
})(jQuery);
	</script>
<script>
$(document).ready(function(){
   $(document).on('click','#btn-more',function(){
       var id = $(this).data('id');
       $("#btn-more").html("Loading....");
       $.ajax({
           url : '{{ url("pagingHome") }}',
           method : "GET",
           data : {id:id, _token:"{{csrf_token()}}"},
           dataType : "text",
           success : function (data)
           {
              if(data != '') 
              {
                  $('#remove-row').remove();
                  $('#load-data').append(data);
              }
              else
              {
                  $('#btn-more').html("No Data");
              }
           }
       });
   });  
}); 
</script>
</body>
</html>
