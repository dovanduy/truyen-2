<?php

namespace App\Http\Controllers;

use App\Client;
use App\CustomSMSGateways;
use App\Jobs\SendBulkSMS;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use Illuminate\Http\Request;
use libphonenumber\PhoneNumberUtil;

class PublicAccessController extends Controller
{

    //======================================================================
    // ultimateSMSApi Function Start Here
    //======================================================================
    public function ultimateSMSApi(Request $request)
    {

        $action = $request->input('action');
        $api_key = $request->input('api_key');
        $to = $request->input('to');
        $from = $request->input('from');
        $sms = $request->input('sms');

        $msgcount = strlen($sms);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        if ($action == '' && $api_key == '') {
            return response()->json([
                'code' => '100',
                'message' => 'Bad gateway requested'
            ]);
        }

        switch ($action) {
            case 'send-sms':

                if ($to == '' && $from == '' && $sms == ''){
                    return response()->json([
                        'code' => '100',
                        'message' => 'Bad gateway requested'
                    ]);
                }


                $isValid=PhoneNumberUtil::isViablePhoneNumber($to);

                if (!$isValid){
                    return response()->json([
                        'code' => '103',
                        'message' => 'Invalid Phone Number'
                    ]);
                }


                if (app_config('api_key') == $api_key) {
                    $gateway = SMSGateways::find(app_config('sms_api_gateway'));
                    if ($gateway->custom == 'Yes') {
                        $cg_info = CustomSMSGateways::where('gateway_id', app_config('sms_api_gateway'))->first();
                    } else {
                        $cg_info = '';
                    }

                    $user_id='0';
                } else {
                    $client = Client::where('api_key', $api_key)->first();
                    if ($client) {
                        $user_id=$client->id;
                        $gateway = SMSGateways::find($client->sms_gateway);
                        if ($gateway->custom == 'Yes') {
                            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
                        } else {
                            $cg_info = '';
                        }

                    } else {
                        return response()->json([
                            'code' => '102',
                            'message' => 'Authentication Failed'
                        ]);
                    }
                }
                $this->dispatch(new SendBulkSMS($user_id,$to, $gateway, $from, $sms, $msgcount, $cg_info,$api_key));

                return response()->json([
                    'code'=>'ok',
                    'message'=>'Successfully Send'
                ]);

                break;

            case 'get-inbox':
                $all_messages=SMSHistory::where('api_key',$api_key)->select('id','sender','receiver')->get();
                $return_data=[];
                $all_message=[];
                foreach ($all_messages as $msg){
                    $return_data['id']=$msg->id;
                    $return_data['from']=$msg->sender;
                    $return_data['phone']=$msg->receiver;
                    $return_data['sms']=SMSInbox::where('id',$msg->id)->select('original_msg as message','amount as part','status')->get()->toArray();
                    array_push($all_message,$return_data);
                }

                return response()->json($all_message);

                break;

            default:
                return response()->json([
                    'code' => '101',
                    'message' => 'Wrong action'
                ]);
                break;
        }

    }


    //======================================================================
    // insertSMS Function Start Here
    //======================================================================
    public function insertSMS($number,$msg_count,$body)
    {
        $get_info=SMSHistory::where('receiver',$number)->orderBy('id', 'desc')->first();

        SMSInbox::create([
            'msg_id'=>$get_info->id,
            'amount'=>$msg_count,
            'original_msg' => $body,
            'encrypt_msg' => base64_encode($body),
            'status' => 'Received from '.$number,
            'ip' => request()->ip(),
            'send_by' => 'receiver',
        ]);
    }

    //======================================================================
    // replyTwilio Function Start Here
    //======================================================================
    public function replyTwilio(Request $request){
        $number=$request->input('From');
        $body=$request->input('Body');

        if ($number=='' && $body==''){
            return 0;
        }

        $msgcount=strlen($body);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $this->insertSMS($number,$msgcount,$body);

        return 0;
    }

    //======================================================================
    // replyTxtLocal Function Start Here
    //======================================================================
    public function replyTxtLocal(Request $request){
        $number = $request->input('inNumber');
        $sender = $request->input('sender');
        $body = $request->input('content');


        if ($number=='' && $body==''){
            return 0;
        }

        $msgcount=strlen($body);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $this->insertSMS($number,$msgcount,$body);

        return 0;
    }


    //======================================================================
    // replySmsGlobal Function Start Here
    //======================================================================
    public function replySmsGlobal(Request $request){
        $number = $request->input('to');
        $sender = $request->input('from');
        $body = $request->input('msg');


        if ($number=='' && $body==''){
            return 0;
        }

        $msgcount=strlen($body);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $this->insertSMS($number,$msgcount,$body);

        return 0;
    }


    //======================================================================
    // replyBulkSMS Function Start Here
    //======================================================================
    public function replyBulkSMS(Request $request){
        $number = $request->input('msisdn');
        $sender = $request->input('sender');
        $body = $request->input('message');


        if ($number=='' && $body==''){
            return 0;
        }

        $msgcount=strlen($body);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $this->insertSMS($number,$msgcount,$body);

        return 0;
    }


    //======================================================================
    // replyNexmo Function Start Here
    //======================================================================
    public function replyNexmo(Request $request){

        $number = $request->input('msisdn');
        $request = array_merge($_GET, $_POST);

// check that request is inbound message
        if (!isset($request['to']) OR !isset($request['msisdn']) OR !isset($request['text'])) {
            return;
        }

//Deal with concatenated messages
        $message = false;
        if (isset($request['concat']) AND $request['concat'] == true ) {

            //generally this would be a database
            session_start();
            session_id($request['concat-ref']);

            if (!isset($_SESSION['messages'])) {
                $_SESSION['messages'] = array();
            }

            $_SESSION['messages'][] = $request;

            if (count($_SESSION['messages']) == $request['concat-total']) {
                //order messages
                usort(
                    $_SESSION['messages'], function ($a , $b) {
                    return $a['concat-part'] > $b['concat-part'];
                }
                );

                $message = array_reduce(
                    $_SESSION['messages'], function ($carry, $item) {
                    return $carry . $item['text'];
                }
                );
            }
        }

        $msgcount=strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $this->insertSMS($number,$msgcount,$message);

        return 0;
    }

    //======================================================================
    // replyPlivo Function Start Here
    //======================================================================
    public function replyPlivo(Request $request){
        $number=$request->input('From');
        $sender=$request->input('To');
        $message=$request->input('Text');

        $msgcount=strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $this->insertSMS($number,$msgcount,$message);

        return 0;

    }


}
