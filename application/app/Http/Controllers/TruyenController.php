<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Truyen;
use App\Models\Website;
use URL;
include base_path() . '/vendor/simplehtmldom/simple_html_dom.php';
class TruyenController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }

    public function themTruyen(Request $request){
    	$websites=Website::all();
    	return view('truyen.them_truyen',compact('websites'));
    }

    public function postThemTruyen(Request $request){
    	 $messages = array(
                'title.required' => 'Chưa nhập tên truyện',
                'url.required'=>'Chưa nhập url',
                'website_id.required'=>'Chưa chọn nguồn',
                'summary.required'=>'Chưa nhập mô tả',
                'linkFile.required'=>'Chưa get hình đại diện'
            );
        $v = \Validator::make($request->all(), [
            'title' => 'required',
            'url'=>'required',
            'website_id'=>'required',
            'summary'=>'required',
            'linkFile'=>'required',
        ],$messages);
        
        if ($v->fails()) {
            return redirect('client/them-truyen')->withErrors($v->errors())->withInput();
        }
        $truyen = new Truyen();
        $truyen->title=trim($request->title);
        $truyen->url=trim($request->url);
        $truyen->img_avatar=$request->linkFile;
        $truyen->website_id=$request->website_id;
        $truyen->summary=$request->summary;
        $truyen->user_id=Auth::guard('client')->user()->id;
        $truyen->created_date = date('Y-m-d');
        $truyen->save();
        return redirect('client/danh-sach-truyen')->with([
                'message' => 'Thêm truyện mới thành công',
            ]);
    }

    public function listTruyen(){
        $truyens=Truyen::where('is_delete',0)->get();
        return view('truyen.list_truyen',compact('truyens'));
    }

    public function getImgAvatar(Request $request){
        $title=vn_to_str($request->title);
        $url=$request->url;
        $websiteId=$request->websiteId;
        switch ($websiteId) {
            case 1://blogtruyen.com
                $divParent='.thumbnail';
                break;
            case 2://truyentranh.net
                $divParent='.cover-detail';
                break;
        }
        $website=Website::where('id',$websiteId)->first();
        $pos = strpos($url,$website->name);
        if ($pos === false) {
            return array('info' => 'failed', 'statusCode' =>1, 'message' =>'Chọn nguồn không đúng');
        }
        $rootPath=dirname(base_path());
        //$url        = 'https://blogtruyen.com/15010/cuu-tinh-thien-than-quyet';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $nameManga  = $title;
        $result=[];
        foreach ($html->find($divParent) as $div) {
            foreach ($div->find('img') as $element) {
                $imgSrc = $element->src;
                $url    = $imgSrc;
                $fileName   = explode('/', $imgSrc);
                $fileName   = array_reverse($fileName);
                $fileName = $nameManga.'_'.$fileName[0];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($ch, CURLOPT_SSLVERSION, 3);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data  = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);
                $subFolder = $rootPath.'/files/' . $nameManga . '/avatar';
                if (!file_exists($subFolder)) {
                    mkdir($subFolder, 0755, true);
                }
                $destination = $subFolder . '/' . $fileName;
                $file        = fopen($destination, "w+");
                fputs($file, $data);
                fclose($file);
                $result['imgUrl']=URL::to('/').'/files/'.$nameManga . '/avatar/'.$fileName;
                $result['linkFile']=$nameManga . '/avatar/'.$fileName;
            }
        }
        $response = array('info' => 'success', 'statusCode' =>0, 'data' =>$result);
        return $response;
    }
}
