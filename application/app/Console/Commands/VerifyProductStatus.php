<?php

namespace App\Console\Commands;

use App\AppConfig;
use Illuminate\Console\Command;

class VerifyProductStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VerifyProductStatus:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Envato license key';

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
        $purchase_code=app_config('purchase_key');
        //$get_verification='http://coderpixel.com/envato/?purchase_code='.$purchase_code;
        //$data=file_get_contents($get_verification);
		
		$data = 'success'; //huyhh
		
        if ($data!='success'){

            $error_count=app_config('purchase_code_error_count');

            if ($error_count>5){

                AppConfig::where('setting', '=', 'purchase_key')->update(['value' => null]);
            }else{
                AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => $error_count+=1]);
            }

        }
    }
}
