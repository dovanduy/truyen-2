<?php

namespace App\Console\Commands;

use App\Models\Truyen;
use App\Models\TruyenChap;
use App\Models\TruyenChapImg;
use App\Models\Website;
use Illuminate\Console\Command;
// $html->clear();
//     unset($html);
class CrawFirst extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truyen:craw_first';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get first data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $truyens = Truyen::where('cron_status', 0)->get();
        foreach ($truyens as $key => $item) {
            $truyenId = $item->id;
            //start cron
            $truyenCron                  = Truyen::find($truyenId);
            $truyenCron->cron_start_time = date('Y-m-d H:i:s');
            $truyenCron->save();
            $url         = $item->url;
            $website     = Website::find($item->website_id);
            $websiteName = $website->name;
            $context     = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
            $response    = file_get_contents($url, false, $context);
            $html        = str_get_html($response);
            $nameManga   = $item->folder_name;
            $rootPath    = dirname(base_path());
            switch ($websiteName) {
                case 'blogtruyen.com':
                    foreach ($html->find('#list-chapters') as $div) {
                        foreach ($div->find('a') as $row1) {
                            $title      = trim($row1->plaintext);
                            $chapNumber = trim($row1->plaintext);
                            $chapNumber = explode(' ', $chapNumber);
                            $chapNumber = array_reverse($chapNumber);
                            //$chapNumber = ltrim($chapNumber[0], '0');
                            $chapNumber = $chapNumber[0];
                            $folderName = str_replace("–", "", $title);
                            $folderName = str_replace("-", "", $folderName);
                            $folderName = vn_to_str($folderName);
                            //insert truyen_chap
                            $truyenChap               = new TruyenChap();
                            $truyenChap->truyen_id    = $truyenId;
                            $truyenChap->title        = $title;
                            $truyenChap->folder_name  = $folderName;
                            $truyenChap->chap_number  = $chapNumber;
                            $truyenChap->user_id      = 0;
                            $truyenChap->created_date = date('Y-m-d');
                            $truyenChap->save();
                            $insertId = $truyenChap->id;

                            $urlChild  = 'http://blogtruyen.com' . $row1->href;
                            $context   = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
                            $response  = file_get_contents($urlChild, false, $context);
                            $htmlChild = str_get_html($response);
                            foreach ($htmlChild->find('#content') as $divChild) {
                                foreach ($divChild->find('img') as $rowChild) {
                                    $srcImage = trim($rowChild->src);
                                    $href     = $srcImage;
                                    $linkImg  = $href;
                                    if (preg_match('/\.(jpeg|jpg|png|gif)/i', $linkImg)) {
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
                                        $subFolder = $rootPath . '/files/' . $nameManga . '/' . $folderName;
                                        if (!file_exists($subFolder)) {
                                            mkdir($subFolder, 0755, true);
                                        }
                                        $destination = $subFolder . '/' . $fileName;
                                        $file        = fopen($destination, "w+");
                                        fputs($file, $data);
                                        fclose($file);
                                        //insert truyen chap img
                                        $truyenChapImg                 = new truyenChapImg();
                                        $truyenChapImg->truyen_chap_id = $insertId;
                                        $truyenChapImg->chap_img       = $fileName;
                                        $truyenChapImg->user_id        = 0;
                                        $truyenChapImg->created_date   = date('Y-m-d');
                                        $truyenChapImg->save();
                                    }

                                }
                            }
                            //die;
                        }
                    }
                    break;
                case 'truyentranh.net':
                    foreach ($html->find('#examples') as $div) {
                        foreach ($div->find('a') as $row1) {
                            $row1->find('.date-release', 0)->outertext = '';
                            $title                                     = trim($row1->innertext);
                            $chapNumber                                = trim($row1->innertext);
                            $chapNumber                                = explode(' ', $chapNumber);
                            $chapNumber                                = array_reverse($chapNumber);
                            //$chapNumber                                = ltrim($chapNumber[0], '0');
                            $chapNumber = $chapNumber[0];
                            $folderName = str_replace("–", "", $title);
                            $folderName = str_replace("-", "", $folderName);
                            $folderName = vn_to_str($folderName);
                            //echo $folderName;die;
                            //insert truyen_chap
                            $truyenChap               = new TruyenChap();
                            $truyenChap->truyen_id    = $truyenId;
                            $truyenChap->title        = $title;
                            $truyenChap->folder_name  = $folderName;
                            $truyenChap->chap_number  = $chapNumber;
                            $truyenChap->user_id      = 0;
                            $truyenChap->created_date = date('Y-m-d');
                            $truyenChap->save();
                            $insertId  = $truyenChap->id;
                            $urlChild  = $row1->href;
                            $context   = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
                            $response  = file_get_contents($urlChild, false, $context);
                            $htmlChild = str_get_html($response);
                             foreach ($htmlChild->find('.OtherText') as $divChild) {
                                foreach ($divChild->find('img') as $rowChild) {
                                    $srcImage = trim($rowChild->src);
                                    $href     = trim($srcImage);
                                    $linkImg  = $href;
                                    if (preg_match('/\.(jpeg|jpg|png|gif)$/i', $linkImg)) {
                                        $titleFile = explode('/', basename($href));
                                        $titleFile = array_reverse($titleFile);
                                        $titleFile = trim($titleFile[0]);
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
                                        $title     = str_replace("–", "", $title);
                                        $title     = str_replace("-", "", $title);
                                        $subFolder = $rootPath . '/files/' . $nameManga . '/' . $folderName;
                                        if (!file_exists($subFolder)) {
                                            mkdir($subFolder, 0755, true);
                                        }
                                        $destination = $subFolder . '/' . $fileName;
                                        $file        = fopen($destination, "w+");
                                        fputs($file, $data);
                                        fclose($file);
                                        //insert truyen chap img
                                        $truyenChapImg                 = new truyenChapImg();
                                        $truyenChapImg->truyen_chap_id = $insertId;
                                        $truyenChapImg->chap_img       = $fileName;
                                        $truyenChapImg->user_id        = 0;
                                        $truyenChapImg->created_date   = date('Y-m-d');
                                        $truyenChapImg->save();
                                    }
                                }
                            }
                            foreach ($htmlChild->find('.each-page') as $divChild) {
                                foreach ($divChild->find('img') as $rowChild) {
                                    $srcImage = trim($rowChild->src);
                                    $href     = trim($srcImage);
                                    $linkImg  = $href;
                                    if (preg_match('/\.(jpeg|jpg|png|gif)$/i', $linkImg)) {
                                        $titleFile = explode('/', basename($href));
                                        $titleFile = array_reverse($titleFile);
                                        $titleFile = trim($titleFile[0]);
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
                                        $title     = str_replace("–", "", $title);
                                        $title     = str_replace("-", "", $title);
                                        $subFolder = $rootPath . '/files/' . $nameManga . '/' . $folderName;
                                        if (!file_exists($subFolder)) {
                                            mkdir($subFolder, 0755, true);
                                        }
                                        $destination = $subFolder . '/' . $fileName;
                                        $file        = fopen($destination, "w+");
                                        fputs($file, $data);
                                        fclose($file);
                                        //insert truyen chap img
                                        $truyenChapImg                 = new truyenChapImg();
                                        $truyenChapImg->truyen_chap_id = $insertId;
                                        $truyenChapImg->chap_img       = $fileName;
                                        $truyenChapImg->user_id        = 0;
                                        $truyenChapImg->created_date   = date('Y-m-d');
                                        $truyenChapImg->save();
                                    }
                                }
                            }
                            //die;
                        }
                    }
                    break;
                case 'mangak.info':
                    foreach ($html->find('.chapter-list') as $div) {
                        foreach ($div->find('a') as $row1) {
                            $title      = trim($row1->plaintext);
                            $chapNumber = trim($row1->plaintext);
                            $chapNumber = explode(' ', $chapNumber);
                            $chapNumber = array_reverse($chapNumber);
                            //$chapNumber = ltrim($chapNumber[0], '0');
                            $chapNumber = $chapNumber[0];
                            $folderName = str_replace("–", "", $title);
                            $folderName = str_replace("-", "", $folderName);
                            $folderName = vn_to_str($folderName);
                            //insert truyen_chap
                            $truyenChap               = new TruyenChap();
                            $truyenChap->truyen_id    = $truyenId;
                            $truyenChap->title        = $title;
                            $truyenChap->folder_name  = $folderName;
                            $truyenChap->chap_number  = $chapNumber;
                            $truyenChap->user_id      = 0;
                            $truyenChap->created_date = date('Y-m-d');
                            $truyenChap->save();
                            $insertId = $truyenChap->id;

                            $urlChild  =$row1->href;
                            $context   = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
                            $response  = file_get_contents($urlChild, false, $context);
                            $htmlChild = str_get_html($response);
                            foreach ($htmlChild->find('.vung_doc') as $divChild) {
                                $script = $divChild->find('script', 0);
                                $list=$script->innertext;
                                $list=rtrim($list,',];');
                                $list=str_replace('"', "", $list);
                                $list=explode('[', $list);
                                $list=explode(',', $list[1]);
                                foreach ($list as $rowChild) {
                                    $srcImage = trim($rowChild);
                                    $href     = $srcImage;
                                    $linkImg  = $href;
                                    if (preg_match('/\.(jpeg|jpg|png|gif)/i', $linkImg)) {
                                        $titleFile = explode('?', basename($href));
                                        $titleFile = $titleFile[0];
                                        $fileName  = vn_to_str($titleFile);
                                        $url       = trim($href);
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
                                        $subFolder = $rootPath . '/files/' . $nameManga . '/' . $folderName;
                                        if (!file_exists($subFolder)) {
                                            mkdir($subFolder, 0755, true);
                                        }
                                        $destination = $subFolder . '/' . $fileName;
                                        $file        = fopen($destination, "w+");
                                        fputs($file, $data);
                                        fclose($file);
                                        //insert truyen chap img
                                        $truyenChapImg                 = new truyenChapImg();
                                        $truyenChapImg->truyen_chap_id = $insertId;
                                        $truyenChapImg->chap_img       = $fileName;
                                        $truyenChapImg->user_id        = 0;
                                        $truyenChapImg->created_date   = date('Y-m-d');
                                        $truyenChapImg->save();
                                    }

                                }
                            }
                            //die;
                        }
                    }
                    break;
            }
            //end cron
            $truyenCron                = Truyen::find($truyenId);
            $truyenCron->cron_status   = 1;
            $truyenCron->cron_end_time = date('Y-m-d H:i:s');
            $truyenCron->save();
        }
    }
}
