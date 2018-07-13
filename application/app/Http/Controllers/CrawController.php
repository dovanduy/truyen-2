<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use ReCaptcha\ReCaptcha;

include base_path() . '/vendor/simplehtmldom/simple_html_dom.php';
class CrawController extends Controller
{

    public function crawBlogTruyen(Request $request)
    {
        /*$href      = 'http://1.bp.blogspot.com/-jU0JSpK2dg8/WyTTGWCmLXI/AAAAAAAFerc/Gn0EUXWHOr064q8uwCFQkhAz3hIS2eAgwCHMYCw/TruyentranhLH001.jpg?imgmax=16383';
        $title     = "test";
        $titleFile = explode('?', basename($href));
        $titleFile = $titleFile[0];
        $fileName  = vn_to_str($titleFile);
        $url       = $href;
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        $subFolder = 'files/' . $title;
        if (!file_exists($subFolder)) {
        mkdir($subFolder, 0755, true);
        }
        $destination = $subFolder . '/' . $fileName;
        $file        = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);
        die;*/
        $url        = 'http://blogtruyen.com/7627/boku-no-hero-academia';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $arrayTitle = [];
        $arrayHref  = [];
        $nameManga  = vn_to_str('boku-no-hero-academia');
        foreach ($html->find('#list-chapters') as $div) {
            foreach ($div->find('a') as $row1) {
                $title     = $row1->plaintext;
                $href      = 'http://blogtruyen.com' . $row1->href;
                $urlChild  = 'http://blogtruyen.com' . $row1->href;
                $context   = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
                $response  = file_get_contents($urlChild, false, $context);
                $htmlChild = str_get_html($response);
                foreach ($htmlChild->find('#content') as $divChild) {
                    foreach ($divChild->find('img') as $rowChild) {
                        $srcImage  = $rowChild->src;
                        $href      = $srcImage;
                        $title     = $title;
                        $titleFile = explode('?', basename($href));
                        $titleFile = $titleFile[0];
                        $fileName  = vn_to_str($titleFile);
                        $url       = $href;
                        $ch        = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        //curl_setopt($ch, CURLOPT_SSLVERSION, 3);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $data  = curl_exec($ch);
                        $error = curl_error($ch);
                        curl_close($ch);
                        $subFolder = 'files/' . $nameManga . '/' . $title;
                        if (!file_exists($subFolder)) {
                            mkdir($subFolder, 0755, true);
                        }
                        $destination = $subFolder . '/' . $fileName;
                        $file        = fopen($destination, "w+");
                        fputs($file, $data);
                        fclose($file);
                    }
                }
                //die;
            }
        }
    }

    public function crawAvatarBlogTruyen()
    {
        $url        = 'https://blogtruyen.com/15010/cuu-tinh-thien-than-quyet';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $arrayTitle = [];
        $arrayHref  = [];
        $nameManga  = 'test';
        foreach ($html->find('.thumbnail') as $div) {
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
                $subFolder = 'files/' . $nameManga . '/avatar';
                if (!file_exists($subFolder)) {
                    mkdir($subFolder, 0755, true);
                }
                $destination = $subFolder . '/' . $fileName;
                $file        = fopen($destination, "w+");
                fputs($file, $data);
                fclose($file);
            }
        }
    }

    public function crawAvatarTruyenTranh()
    {
        $url        = 'http://truyentranh.net/This-Man';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $arrayTitle = [];
        $arrayHref  = [];
        $nameManga  = 'test';
        foreach ($html->find('.cover-detail') as $div) {
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
                $subFolder = 'files/' . $nameManga . '/avatar';
                if (!file_exists($subFolder)) {
                    mkdir($subFolder, 0755, true);
                }
                $destination = $subFolder . '/' . $fileName;
                $file        = fopen($destination, "w+");
                fputs($file, $data);
                fclose($file);
            }
        }
    }
    

    public function crawTotalChapBlogTruyen(){
        $url        = 'https://blogtruyen.com/15010/cuu-tinh-thien-than-quyet';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $arrayTitle = [];
        $arrayHref  = [];
        $nameManga  = 'test';
        foreach ($html->find('#list-chapters') as $div) {
            foreach ($div->find('a') as $element) {
                $title= $element->plaintext;
                $title=explode(' ',$title);
                $title = array_reverse($title);
                $totalChap = $title[0];
                break;
            }
        }
    }

    public function crawTotalChapTruyenTranh(){
        $url        = 'http://truyentranh.net/Thinh-The-De-Vuong-Phi';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $arrayTitle = [];
        $arrayHref  = [];
        $nameManga  = 'test';
        foreach ($html->find('.chapter-list') as $div) {
            foreach ($div->find('a') as $element) {
                $element->find('.date-release',0)->outertext='';
                $title= trim($element->innertext);
                $title=explode(' ',$title);
                $title = array_reverse($title);
                $totalChap = $title[0];
                echo $totalChap;
                break;
            }
        }
    }

}
