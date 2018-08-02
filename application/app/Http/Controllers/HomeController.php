<?php

namespace App\Http\Controllers;

use App\Models\Truyen;
use App\Models\TruyenChap;
use App\Models\TruyenChapImg;
use Illuminate\Http\Request;
use DB;
class HomeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('client');
    }

    public function index()
    {
        $newSlideShow = Truyen::where('is_slideshow', 1)->where('is_delete', 0)->orderBy('created_date', 'desc')->take(8)->get();
        $truyens      = Truyen::orderBy('id', 'desc')->take(2)->get();
        return view('home.home', compact('newSlideShow', 'truyens'));
    }

    public function pagingHome(Request $request)
    {
        $output  = '';
        $id      = $request->id;
        $truyens = Truyen::where('id', '<', $id)->orderBy('id', 'DESC')->limit(2)->get();
        if (!$truyens->isEmpty()) {
            $output = view("home.ajax_paging_home", compact('truyens'))->render();
            echo $output;
        }
    }

    public function detail(Request $request){
        $id = $request->id;
        $truyen=Truyen::find($id);
        $truyens      = Truyen::where('cate_id',$truyen->cate_id)->where('id','!=',$truyen->id)->orderBy('id', 'desc')->take(3)->get();
        $truyenChaps=TruyenChap::where('truyen_id',$id)->orderBy('chap_number','desc')->get();
        return view('home.detail',compact('truyen','truyens','truyenChaps'));
    }

    public function view(Request $request){
       $chapNumber= $request->chapNumber;
       $slug = $request->slug;
       $truyen=Truyen::where('slug',$slug)->first();
       $truyenChaps=TruyenChap::where('truyen_id',$truyen->id)->orderBy('chap_number','desc')->get();
       $sql="SELECT c.title,i.chap_img
            FROM truyen t ,truyen_chap c,truyen_chap_img i
            WHERE 
                c.id=i.truyen_chap_id 
            and t.id=c.truyen_id
            AND c.chap_number='".$chapNumber."'
            And t.slug='".$slug."'";
        $listImg = DB::select($sql);
       return view('home.view',compact('truyen','truyenChaps','listImg','chapNumber'));
    }
}
