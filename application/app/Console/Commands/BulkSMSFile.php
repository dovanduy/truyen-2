<?php

namespace App\Console\Commands;

use App\Jobs\SendBulkSMS;
use App\SMSGateways;
use App\StoreBulkSMS;
use Illuminate\Console\Command;

class BulkSMSFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:sendbulk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Bulk SMS From File';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function renderSMS($msg, $data)
    {
        preg_match_all('~<%(.*?)%>~s', $msg, $datas);
        $Html = $msg;
        foreach ($datas[1] as $value) {
            $Html = str_replace($value, $data[$value], $Html);
        }
        return str_replace(array("<%", "%>"), '', $Html);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bulk_sms = StoreBulkSMS::where('status','0')->get();
        foreach ($bulk_sms as $sms){
            $message = $sms->message;
            $results = $sms->receiver;
            $results = json_decode($results);
            $gateway = SMSGateways::find($sms->use_gateway);
            $sms->status='1';
            $sms->save();
            foreach ($results as $r) {
                $msg_data = array(
                    'Phone Number' => $r->phone_number,
                    'Email Address' => $r->email_address,
                    'User Name' => $r->user_name,
                    'Company' => $r->company,
                    'First Name' => $r->first_name,
                    'Last Name' => $r->last_name,
                );

                if(isset($r->message) && $r->message!=''){
                    $message1=$r->message;
                    $get_message = $this->renderSMS($message1, $msg_data);
                }else {
                    $get_message = $this->renderSMS($message, $msg_data);
                }
                dispatch(new SendBulkSMS($sms->userid,$r->phone_number, $gateway, $sms->sender, $get_message, $sms->amount,'','',$sms->file_send));
            }
            $sms->delete();
        }
    }
}
