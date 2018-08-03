<nav id="topNav" class="navbar navbar-expand-md navbar-light bg-faded" style="background: #fff;">
    <a class="navbar-brand" href="#first"><img src="{{URL::asset('assets/img/home.png')}}" alt="Logo Image"></a>
    <button class="navbar-toggler hidden-md-up pull-right" type="button" data-toggle="collapse" data-target="#collapsingNavbar">
        ☰
    </button>
    <div class="collapse navbar-collapse" id="collapsingNavbar">
        <ul class="nav navbar-nav">
        	<li class="nav-item active">
	        	<a class="nav-link" href="#">Trang chủ <span class="sr-only">(current)</span></a>
	      	</li>
            <li class="nav-item">
                <a class="nav-link" href="#">Giới thiệu<span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item dropdown megamenu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Thể loại<span class="caret"></span></a>
                <div class="dropdown-menu p-3">
                    <div class="row">
                            <?php $i=0;?>
                            @foreach($cates as $item)
                            <?php
                                if($i==0){
                            ?>
                            <ul class="col-sm-2 list-unstyled">
                            <?php }?>
                                <li class="">
                                    <a href="{{url('cate/'.$item->id.'/'.vn_to_str($item->name))}}" title="">
                                       {{$item->name}}
                                    </a>
                                </li>
                            <?php $i++;?>
                            <?php if($i==10){?>
                            </ul>
                        <?php 
                        $i=0;
                            }
                        ?>
                            @endforeach
                    </div>
                </div>
            </li>
            
        </ul>
    </div>
</nav>