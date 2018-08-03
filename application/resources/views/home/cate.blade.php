<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>TITLE</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">


	<!-- Font -->

	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">
	<!-- Stylesheets -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    {!! Html::style("assets/home/category-sidebar/css/bootstrap.min.css") !!}
    {!! Html::style("assets/home/common-css/ionicons.css") !!}
    {!! Html::style("assets/home/category-sidebar/css/styles.css") !!}
    {!! Html::style("assets/home/category-sidebar/css/responsive.css") !!}
    {!! Html::style("assets/home/category-sidebar/css/custom.css") !!}
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

.section {
    padding: 12px 0 40px;
}
.mgBottom {
    margin-bottom: 30px;
}
</style>
</head>
<body >
@include('sub.top_menu')

	<section class="blog-area section">
		<div class="container">
			<div class="row">
                <div class="col-md-12"><h3>{{$cate->name}}</h3></div>
				<div class="col-lg-8 col-md-12">
					<div class="row" id="load-data">
                        <?php
                            use App\Models\TruyenChap;
                        ?>
                        @foreach($truyens as $itemt)
                        <?php
                            $truyenChap = TruyenChap::where('truyen_id',$itemt->id)->orderBy('chap_number','desc')->take(8)->get();
                        ?>
						<div class="col-md-6 col-sm-12 mgBottom">
							<div class="card h-100">
								<div class="single-post post-style-1">

									<div class="blog-image"><img src="{{URL::asset('files/'.$itemt->folder_name.'/avatar/'.$itemt->img_avatar)}}" alt="Blog Image"></div>
									<div class="blog-info">

										<h4 class="title"><a href="{{url('detail/'.$itemt->id.'/'.vn_to_str($itemt->title))}}"><b>{{$itemt->title}}</b></a></h4>
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
                        <div class="col-lg-8 col-md-12 offset-5" id="remove-row">
                            <button class="load-more-btn btn btn-info" id="btn-more" href="#" data-id="{{$itemt->id}}">LOAD MORE</button>
                        </div>
					</div><!-- row -->
                    
				</div><!-- col-lg-8 col-md-12 -->

				@include('sub.right_menu')

			</div><!-- row -->

		</div><!-- container -->
	</section><!-- section -->


	@include('sub.footer')

	<!-- SCIPTS -->
    {!! Html::script("assets/home/common-js/jquery-3.1.1.min.js") !!}

	<!-- <script src="common-js/tether.min.js"></script> -->

	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>

	<!-- <script src="common-js/scripts.js"></script> -->
	<script>
   (function($){
	   "use strict";
	   $('.next').click(function(){ $('.carousel').carousel('next');return false; });
	   $('.prev').click(function(){ $('.carousel').carousel('prev');return false; });
   })	
   (jQuery);
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
