<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

include base_path() . '/vendor/simplehtmldom/simple_html_dom.php';
class TestCraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truyen:testcraw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'craw';

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
        $url        = 'http://blogtruyen.com/7627/boku-no-hero-academia';
        $context    = stream_context_create(array('http' => array('header' => 'User-Agent: Mozilla compatible')));
        $response   = file_get_contents($url, false, $context);
        $html       = str_get_html($response);
        $arrayTitle = [];
        $arrayHref  = [];
        $nameManga  = vn_to_str('boku-no-hero-academia');
        $rootPath=dirname(base_path());
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
                        $subFolder = $rootPath.'/files/' . $nameManga . '/' . $title;
                        if (!file_exists($subFolder)) {
                            mkdir($subFolder, 0755, true);
                        }
                        $destination = $subFolder . '/' . $fileName;
                        $file        = fopen($destination, "w+");
                        fputs($file, $data);
                        fclose($file);
                        die;
                    }
                }
                //die;
            }
        }
    }
}
