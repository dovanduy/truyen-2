<?php

namespace App\Http\Controllers;

use App\Models\Truyen;
use Illuminate\Http\Request;

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
}
