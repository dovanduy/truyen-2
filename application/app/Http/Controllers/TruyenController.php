<?php

namespace App\Http\Controllers;

use App\Models\Cate;
use App\Models\Truyen;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use URL;

include base_path() . '/vendor/simplehtmldom/simple_html_dom.php';
class TruyenController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }

    public function listTruyen()
    {
        $truyens = Truyen::where('is_delete', 0)->orderBy('id', 'desc')->get();
        return view('truyen.list_truyen', compact('truyens'));
    }

    public function themTruyen(Request $request)
    {
        $websites = Website::all();
        $cates    = Cate::all();
        return view('truyen.them_truyen', compact('websites', 'cates'));
    }

    public function postThemTruyen(Request $request)
    {
        $messages = array(
            'title.required'      => 'Chưa nhập tên truyện',
            'title.unique'        => 'Tên truyện đã tồn tại',
            'url.required'        => 'Chưa nhập url',
            'website_id.required' => 'Chưa chọn nguồn',
            'summary.required'    => 'Chưa nhập mô tả',
            'linkFile.required'   => 'Chưa get hình đại diện',
            'total_chap.required' => 'Chưa get tổng số chap',
            'cate_id.required'    => 'Chưa chọn thể loại',
        );
        $v = \Validator::make($request->all(), [
            'title'      => 'required|unique:truyen,title',
            'url'        => 'required',
            'website_id' => 'required',
            'summary'    => 'required',
            'linkFile'   => 'required',
            'total_chap' => 'required',
            'cate_id'    => 'required',
        ], $messages);

        if ($v->fails()) {
            return redirect('client/them-truyen')->withErrors($v->errors())->withInput();
        }
        $truyen               = new Truyen();
        $truyen->title        = strtolower(trim($request->title));
        $truyen->folder_name  = vn_to_str(strtolower(trim($request->title)));
        $truyen->slug         = vn_to_str(trim($request->title));
        $truyen->url          = trim($request->url);
        $truyen->cate_id      = $request->cate_id;
        $truyen->img_avatar   = $request->linkFile;
        $truyen->website_id   = $request->website_id;
        $truyen->summary      = trim($request->summary);
        $truyen->total_chap   = $request->total_chap;
        $truyen->user_id      = Auth::guard('client')->user()->id;
        $truyen->created_date = date('Y-m-d');
        if ($request->has('is_slideshow')) {
            $truyen->is_slideshow = 1;
        }
        $truyen->save();
        return redirect('client/danh-sach-truyen')->with([
            'message' => 'Thêm truyện mới thành công',
        ]);
    }

    public function getImgAvatar(Request $request)
    {
        $title     = vn_to_str(trim($request->title));
        $url       = trim($request->url);
        $websiteId = $request->websiteId;
        switch ($websiteId) {
            case 1: //blogtruyen.com
                $divParent = '.thumbnail';
                break;
            case 2: //truyentranh.net
                $divParent = '.cover-detail';
                break;
            case 3: //mangak.info
                $divParent = '.truyen_info_left';
                break;

        }
        $website = Website::where('id', $websiteId)->first();
        $pos     = strpos($url, $website->name);
        if ($pos === false) {
            return array('info' => 'failed', 'statusCode' => 1, 'message' => 'Chọn nguồn không đúng');
        }
        $rootPath = dirname(base_path());
        //$url        = 'https://blogtruyen.com/15010/cuu-tinh-thien-than-quyet';
        $context   = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response  = file_get_contents($url, false, $context);
        $html      = str_get_html($response);
        $nameManga = $title;
        $result    = [];
        foreach ($html->find($divParent) as $div) {
            foreach ($div->find('img') as $element) {
                $imgSrc       = trim($element->src);
                $url          = $imgSrc;
                $fileName     = explode('/', $imgSrc);
                $fileName     = array_reverse($fileName);
                $typeFileName = explode('.', $fileName[0]);
                $typeFileName = $typeFileName[1];
                $fileName     = $nameManga . '.' . $typeFileName;
                $ch           = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($ch, CURLOPT_SSLVERSION, 3);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data  = curl_exec($ch);
                $error = curl_error($ch);
                curl_close($ch);
                $subFolder = $rootPath . '/files/' . $nameManga . '/avatar';
                if (!file_exists($subFolder)) {
                    mkdir($subFolder, 0755, true);
                }
                $destination = $subFolder . '/' . $fileName;
                $file        = fopen($destination, "w+");
                fputs($file, $data);
                fclose($file);
                $result['imgUrl']   = URL::to('/') . '/files/' . $nameManga . '/avatar/' . $fileName;
                $result['linkFile'] = $fileName;
            }
        }
        $response = array('info' => 'success', 'statusCode' => 0, 'data' => $result);
        return $response;
    }

    public function getTotalChap(Request $request)
    {
        $url       = trim($request->url);
        $websiteId = $request->websiteId;
        $website   = Website::where('id', $websiteId)->first();
        $pos       = strpos($url, $website->name);
        if ($pos === false) {
            return array('info' => 'failed', 'statusCode' => 1, 'message' => 'Chọn nguồn không đúng');
        }
        $context   = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response  = file_get_contents($url, false, $context);
        $html      = str_get_html($response);
        $totalChap = 0;
        switch ($websiteId) {
            case 1: //blogtruyen.com
                $ret        = $html->find('#list-chapters a', 0);
                $lastNumber = trim($ret->plaintext);
                $lastNumber = explode(' ', $lastNumber);
                $lastNumber = array_reverse($lastNumber);
                $totalChap  = $lastNumber[0];
                break;
            case 2: //truyentranh.net
                $ret                                      = $html->find('#examples a', -1);
                $ret->find('.date-release', 0)->outertext = '';
                $lastNumber                               = trim($ret->plaintext);
                $lastNumber                               = explode(' ', $lastNumber);
                $lastNumber                               = array_reverse($lastNumber);
                $totalChap                                = $lastNumber[1];
                break;
            case 3: //mangak.info
                $ret        = $html->find('.chapter-list a', 0);
                $lastNumber = trim($ret->plaintext);
                $lastNumber = explode(' ', $lastNumber);
                $lastNumber = array_reverse($lastNumber);
                $totalChap  = $lastNumber[0];
                break;
        }
        $response = array('info' => 'success', 'statusCode' => 0, 'totalChap' => $totalChap);
        return $response;

    }

    public function editTruyen(Request $request)
    {
        $id       = $request->id;
        $websites = Website::all();
        $truyen   = Truyen::find($id);
        $cates    = Cate::all();
        return view('truyen.edit_truyen', compact('truyen', 'websites', 'cates'));

    }

    public function postSuaTruyen(Request $request)
    {
        $id       = $request->id;
        $messages = array(
            'title.required'      => 'Chưa nhập tên truyện',
            'title.unique'        => 'Tên truyện đã tồn tại',
            'url.required'        => 'Chưa nhập url',
            'website_id.required' => 'Chưa chọn nguồn',
            'summary.required'    => 'Chưa nhập mô tả',
            'total_chap.required' => 'Chưa get tổng số chap',
        );
        $v = \Validator::make($request->all(), [
            'title'      => 'required|unique:truyen,title,' . $id,
            'url'        => 'required',
            'website_id' => 'required',
            'summary'    => 'required',
            'total_chap' => 'required',
        ], $messages);

        if ($v->fails()) {
            return redirect('client/sua-truyen/' . $id)->withErrors($v->errors())->withInput();
        }
        $truyen = Truyen::find($id);
        //rename folder truyen
        $titleNew      = strtolower(trim($request->title));
        $titleOld      = strtolower(trim($truyen->title));
        $rootPath      = dirname(base_path());
        $oldFolderName = $rootPath . '/files/' . vn_to_str($titleOld);
        $newFolderName = $rootPath . '/files/' . vn_to_str($titleNew);
        rename($oldFolderName, $newFolderName);

        $truyen->title       = strtolower(trim($request->title));
        $truyen->folder_name = vn_to_str(strtolower(trim($request->title)));
        $truyen->slug        = vn_to_str(strtolower(trim($request->title)));
        $truyen->url         = trim($request->url);
        if ($request->has('linkFile')) {
            $truyen->img_avatar = $request->linkFile;
        }
        $truyen->cate_id      = $request->cate_id;
        $truyen->website_id   = $request->website_id;
        $truyen->summary      = trim($request->summary);
        $truyen->total_chap   = $request->total_chap;
        $truyen->user_id      = Auth::guard('client')->user()->id;
        $truyen->created_date = date('Y-m-d');
        $is_slideshow         = 0;
        if ($request->has('is_slideshow')) {
            $is_slideshow = 1;
        }
        $truyen->is_slideshow = $is_slideshow;
        $truyen->save();
        return redirect('client/danh-sach-truyen')->with([
            'message' => 'Sửa truyện thành công',
        ]);
    }

    public function deleteTruyen(Request $request)
    {
        $id                = $request->id;
        $truyen            = Truyen::find($id);
        $truyen->is_delete = 1;
        $truyen->save();
        return redirect('client/danh-sach-truyen')->with([
            'message' => 'Xóa truyện thành công',
        ]);
    }

    public function pagingTruyen(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'title',
            2 => 'website_id',
            3 => 'cate_id',
            4 => 'total_chap',
            5 => 'created_date',
            6 => 'action'
        );

        $totalData = Truyen::count();

        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $posts = Truyen::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $posts = Truyen::where('id', 'LIKE', "%{$search}%")
                ->orWhere('title', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Truyen::where('id', 'LIKE', "%{$search}%")
                ->orWhere('title', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = array();
        if (!empty($posts)) {
            foreach ($posts as $item) {
                $website = Website::where('id',$item->website_id)->first();
                $websiteName=$website->name;
                $cate = Cate::where('id',$item->cate_id)->first();
                $cateName=$cate->name;
                $action='<a class="btn btn-success btn-xs" href="'.url('client/sua-truyen/'.$item->id).'"><i class="fa fa-edit"></i>Sửa</a>
                <a href="#" class="btn btn-danger btn-xs cdelete" id="'.$item->id.'"><i class="fa fa-trash"></i> Xoá</a>';
                $data[] = array(
                    'img'=>'<img width="100" height="100" src="'.URL::asset('files/'.$item->folder_name.'/avatar/'.$item->img_avatar).'"/>',
                    'title'=>$item->title,
                    'website_id'=>$websiteName,
                    'cate_id'=>$cateName,
                    'total_chap'=>$item->total_chap,
                    'created_date'=>date('d-m-Y',strtotime($item->created_date)),
                    'action'=>$action
                );

            }
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
        );

        echo json_encode($json_data);
    }

}
