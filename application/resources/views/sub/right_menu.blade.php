<div class="col-lg-4 col-md-12 ">

					<div class="single-post info-area ">

						<div class="about-area">
							<h4 class="title"><b>Thể loại</b></h4>
							<div class="category">
							<div class="row">
								<?php
									use App\Models\Truyen;
								?>
								@foreach($cates as $item)
								<?php
									$count=Truyen::where('cate_id',$item->id)->count();
								?>
								<div class="col-6">
		                        <a href="{{url('cate/'.$item->id.'/'.vn_to_str($item->name))}}">{{$item->name}} <span> ({{$count}})</span></a>
		                   		 </div>
		                   		@endforeach


						</div><!-- info-area -->
					</div>
						</div>
						

					</div><!-- info-area -->

				</div><!-- col-lg-4 col-md-12 -->