<?php

namespace App\Http\Controllers;

use App\Classes\PhoneNumber;
use App\Client;
use App\ClientGroups;
use App\CustomSMSGateways;
use App\ImportPhoneNumber;
use App\IntCountryCodes;
use App\Jobs\SendBulkSMS;
use App\PaymentGateways;
use App\ScheduleSMS;
use App\SenderIdManage;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSInbox;
use App\SMSPlanFeature;
use App\SMSPricePlan;
use App\StoreBulkSMS;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class UserSMSController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }

    //======================================================================
    // senderIdManagement Function Start Here
    //======================================================================
    public function senderIdManagement()
    {
        $all_sender_id = SenderIdManage::all();

        $all_ids = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_id = SenderIdManage::whereIn('id', $all_ids)->get();

        return view('client.sender-id-management', compact('sender_id'));
    }

    //======================================================================
    // postSenderID Function Start Here
    //======================================================================
    public function postSenderID(Request $request)
    {
        if ($request->sender_id == '') {
            return redirect('user/sms/sender-id-management')->with([
                'message'           => language_data('Sender ID required'),
                'message_important' => true,
            ]);
        }

        $client_id = (string) Auth::guard('client')->user()->id;
        $client_id = (array) $client_id;
        $client_id = json_encode($client_id);

        $sender_id            = new SenderIdManage();
        $sender_id->sender_id = $request->sender_id;
        $sender_id->cl_id     = $client_id;
        $sender_id->status    = 'pending';
        $sender_id->save();

        return redirect('user/sms/sender-id-management')->with([
            'message' => language_data('Request send successfully'),
        ]);
    }

    //======================================================================
    // sendSingleSMS Function Start Here
    //======================================================================
    public function sendSingleSMS()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        return view('client.send-single-sms', compact('sender_ids'));
        //return view('client.send-single-sms');
    }

    //======================================================================
    // postSingleSMS Function Start Here
    //======================================================================
    public function postSingleSMS(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-single-sms')->withErrors($v->errors());
        }

        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client == '') {
            return redirect('user/sms/send-single-sms')->with([
                'message'           => language_data('Client info not found'),
                'message_important' => true,
            ]);
        }

        $gateway = SMSGateways::find($client->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-single-sms')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $phone   = str_replace('+', '', $request->phone_number);
        $c_phone = PhoneNumber::get_code($phone);

        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

        if ($sms_cost) {
            $total_cost = $sms_cost->tariff * $msgcount;
            if ($total_cost == 0) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }

            if ($total_cost > $client->sms_limit) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }
        } else {
            return redirect('user/sms/send-single-sms')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }
        $fileSend = 'gui-mot-tin-sms-' . date('d-m-Y-H-i-s');
        $this->dispatch(new SendBulkSMS($client->id, $request->phone_number, $gateway, $sender_id, $message, $msgcount, $cg_info, '', $fileSend));

        $remain_sms        = $client->sms_limit - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-single-sms')->with([
            'message' => language_data('Please check sms history'),
        ]);
    }

    //======================================================================
    // sendBulkSMS Function Start Here
    //======================================================================
    public function sendBulkSMS()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();

        $client_group = ClientGroups::where('created_by', Auth::guard('client')->user()->id)->get();
        return view('client.send-bulk-sms', compact('client_group', 'sender_ids'));
    }

    //======================================================================
    // postSendBulkSMS Function Start Here
    //======================================================================
    public function postSendBulkSMS(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'client_group' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-sms')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-sms')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_cost              = 0;
        $get_inactive_coverage = [];
        $all_clients           = Client::where('groupid', $request->client_group)->get();

        if ($all_clients->count() <= 0) {
            return redirect('user/sms/send-sms')->with([
                'message'           => language_data('Client Group is empty'),
                'message_important' => true,
            ]);
        }

        foreach ($all_clients as $c) {
            $phone   = str_replace('+', '', $c->phone);
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();
            if ($sms_cost) {
                $sms_charge = $sms_cost->tariff;
                $get_cost += $sms_charge;
            } else {
                array_push($get_inactive_coverage, 'found');
            }
        }

        if (in_array('found', $get_inactive_coverage)) {
            return redirect('user/sms/send-sms')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        $total_cost = $get_cost * $msgcount;

        if ($total_cost == 0) {
            return redirect('user/sms/send-sms')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        if ($total_cost > $sms_count) {
            return redirect('user/sms/send-sms')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }
        $fileSend = "gui-tin-nhom-kh-" . date('d-m-Y-H-i-s');
        Client::where('groupid', $request->client_group)->chunk(30, function ($clients) use ($gateway, $message, $sender_id, $msgcount, $cg_info, $sms_count, $fileSend) {
            foreach ($clients as $i => $c) {
                $this->dispatch(new SendBulkSMS(Auth::guard('client')->user()->id, $c->phone, $gateway, $sender_id, $message, $msgcount, $cg_info, '', $fileSend));
            }
        });

        $remain_sms        = $sms_count - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-sms')->with([
            'message' => language_data('SMS added in queue and will deliver one by one'),
        ]);
    }

    //======================================================================
    // sendSingleScheduleSMS Function Start Here
    //======================================================================
    public function sendSingleScheduleSMS()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        return view('client.send-single-schedule-sms', compact('sender_ids'));
        //return view('client.send-single-schedule-sms');
    }

    //======================================================================
    // postSingleScheduleSMS Function Start Here
    //======================================================================
    public function postSingleScheduleSMS(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-single-schedule-sms')->withErrors($v->errors());
        }

        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client == '') {
            return redirect('user/sms/send-single-schedule-sms')->with([
                'message'           => language_data('Client info not found'),
                'message_important' => true,
            ]);
        }

        $gateway = SMSGateways::find($client->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-single-schedule-sms')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $phone   = str_replace('+', '', $request->phone_number);
        $c_phone = PhoneNumber::get_code($phone);

        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

        if ($sms_cost) {
            $total_cost = $sms_cost->tariff * $msgcount;

            if ($total_cost == 0) {
                return redirect('user/sms/send-single-schedule-sms')->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }

            if ($total_cost > $client->sms_limit) {
                return redirect('user/sms/send-single-schedule-sms')->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }
        } else {
            return redirect('user/sms/send-single-schedule-sms')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-single-schedule-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $fileSend      = 'gui-mot-tin-lap-lich-' . date('d-m-Y-H-i-s');
        ScheduleSMS::create([
            'userid'       => $client->id,
            'sender'       => $sender_id,
            'receiver'     => $request->phone_number,
            'amount'       => $msgcount,
            'original_msg' => $message,
            'encrypt_msg'  => base64_encode($message),
            'submit_time'  => $schedule_time,
            'ip'           => request()->ip(),
            'use_gateway'  => $gateway->id,
            'file_send'    => $fileSend,
        ]);

        $remain_sms        = $client->sms_limit - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-single-schedule-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // purchaseSMSPlan Function Start Here
    //======================================================================
    public function purchaseSMSPlan()
    {
        $price_plan = SMSPricePlan::where('status', 'Active')->get();
        return view('client.sms-price-plan', compact('price_plan'));
    }

    //======================================================================
    // smsPlanFeature Function Start Here
    //======================================================================
    public function smsPlanFeature($id)
    {
        $sms_plan = SMSPricePlan::where('status', 'Active')->find($id);

        if ($sms_plan) {
            $plan_feature     = SMSPlanFeature::where('pid', $id)->get();
            $payment_gateways = PaymentGateways::where('status', 'Active')->get();
            return view('client.sms-plan-feature', compact('sms_plan', 'plan_feature', 'payment_gateways'));
        } else {
            return redirect('user/sms/purchase-sms-plan')->with([
                'message'           => language_data('SMS plan not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // sendSMSFromFile Function Start Here
    //======================================================================
    public function sendSMSFromFile()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        return view('client.send-sms-file', compact('sender_ids'));
        //return view('client.send-sms-file');
    }

    //======================================================================
    // downloadSampleSMSFile Function Start Here
    //======================================================================
    public function downloadSampleSMSFile()
    {
        return response()->download('assets/test_file/sms.csv');
    }

    //======================================================================
    // postSMSFromFile Function Start Here
    //======================================================================
    public function postSMSFromFile(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-sms-file')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get();

        $get_cost              = 0;
        $get_inactive_coverage = [];

        if ($results->count() <= 0) {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('Client Group is empty'),
                'message_important' => true,
            ]);
        }

        foreach ($results as $c) {
            $phone   = str_replace('+', '', $c->phone_number);
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {
                $sms_charge = $sms_cost->tariff;
                $get_cost += $sms_charge;
            } else {
                array_push($get_inactive_coverage, 'found');
            }
        }

        if (in_array('found', $get_inactive_coverage)) {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        $total_cost = $get_cost * $msgcount;

        if ($total_cost == 0) {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        if ($total_cost > $sms_count) {
            return redirect('user/sms/send-sms-file')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        /*foreach ($results->chunk(30) as $clients) {
        foreach ($clients as $i => $c) {
        //huyhh                $this->dispatch(new SendBulkSMS(Auth::guard('client')->user()->id, $c->phone_number, $gateway, $sender_id, $message, $msgcount, $cg_info));
        $this->dispatch(new SendBulkSMS(Auth::guard('client')->user()->id, $c->phone_number, $gateway, $sender_id, $c->message, $msgcount, $cg_info));

        }
        }*/

        $results  = Excel::load($request->import_numbers)->get()->toJson();
        $fileSend = 'gui-hang-loat-tu-file-' . date('d-m-Y-H-i-s');
        StoreBulkSMS::create([
            'userid'      => Auth::guard('client')->user()->id,
            'sender'      => $sender_id,
            'receiver'    => $results,
            'amount'      => $msgcount,
            'message'     => $message,
            'status'      => 0,
            'use_gateway' => $gateway->id,
            'file_send'   => $fileSend,
        ]);

        $remain_sms        = $sms_count - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-sms-file')->with([
            'message' => language_data('SMS added in queue and will deliver one by one'),
        ]);

    }

    //======================================================================
    // sendScheduleSMS Function Start Here
    //======================================================================
    public function sendScheduleSMS()
    {
        $client_group = ClientGroups::where('status', 'Yes')->where('created_by', Auth::guard('client')->user()->id)->get();
        $gateways     = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->find(Auth::guard('client')->user()->sms_gateway);

        if ($gateways == '') {
            return redirect('dashboard')->with([
                'message' => language_data('Schedule feature not supported'),
            ]);
        }
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        //return view('client.send-sms-file', compact('sender_id'));

        return view('client.send-schedule-sms', compact('client_group', 'gateways', 'sender_ids'));
    }

    //======================================================================
    // postScheduleSMS Function Start Here
    //======================================================================
    public function postScheduleSMS(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'client_group' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-schedule-sms')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-schedule-sms')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));

        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_cost              = 0;
        $get_inactive_coverage = [];
        $all_clients           = Client::where('groupid', $request->client_group)->get();

        if ($all_clients->count() <= 0) {
            return redirect('user/sms/send-schedule-sms')->with([
                'message'           => language_data('Client Group is empty'),
                'message_important' => true,
            ]);
        }

        foreach ($all_clients as $c) {
            $phone   = str_replace('+', '', $c->phone);
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {
                $sms_charge = $sms_cost->tariff;
                $get_cost += $sms_charge;
            } else {
                array_push($get_inactive_coverage, 'found');
            }
        }

        if (in_array('found', $get_inactive_coverage)) {
            return redirect('user/sms/send-schedule-sms')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        $total_cost = $get_cost * $msgcount;

        if ($total_cost == 0) {
            return redirect('user/sms/send-schedule-sms')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        if ($total_cost > $sms_count) {
            return redirect('user/sms/send-schedule-sms')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }
        $fileSend = 'gui-tin-lap-lich-nhom-kh-' . date('d-m-Y-H-i-s');
        Client::where('groupid', $request->client_group)->chunk(30, function ($clients) use ($message, $sender_id, $msgcount, $schedule_time, $fileSend) {
            foreach ($clients as $c) {

                ScheduleSMS::create([
                    'userid'       => Auth::guard('client')->user()->id,
                    'sender'       => $sender_id,
                    'receiver'     => $c->phone,
                    'amount'       => $msgcount,
                    'original_msg' => $message,
                    'encrypt_msg'  => base64_encode($message),
                    'submit_time'  => $schedule_time,
                    'ip'           => request()->ip(),
                    'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                    'file_send'    => $fileSend,
                ]);

            }
        });

        $remain_sms        = $sms_count - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-schedule-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);
    }

    //======================================================================
    // sendScheduleSMSFromFile Function Start Here
    //======================================================================
    public function sendScheduleSMSFromFile()
    {

        $gateways = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->find(Auth::guard('client')->user()->sms_gateway);

        if ($gateways == '') {
            return redirect('dashboard')->with([
                'message' => language_data('Schedule feature not supported'),
            ]);
        }
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        //return view('client.send-single-sms', compact('sender_ids'));

        return view('client.send-schedule-sms-file', compact('gateways', 'sender_ids'));
    }

    //======================================================================
    // postScheduleSMSFromFile Function Start Here
    //======================================================================
    public function postScheduleSMSFromFile(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-schedule-sms-file')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-single-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get();

        if ($results->count() <= 0) {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('Client Group is empty'),
                'message_important' => true,
            ]);
        }

        $get_cost              = 0;
        $get_inactive_coverage = [];

        foreach ($results as $c) {
            $phone   = str_replace('+', '', $c->phone_number);
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {
                $sms_charge = $sms_cost->tariff;
                $get_cost += $sms_charge;
            } else {
                array_push($get_inactive_coverage, 'found');
            }
        }

        if (in_array('found', $get_inactive_coverage)) {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        $total_cost = $get_cost * $msgcount;

        if ($total_cost == 0) {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        if ($total_cost > $sms_count) {
            return redirect('user/sms/send-schedule-sms-file')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $fileSend      = 'lap-lich-gui-tu-file-' . date('d-m-Y-H-i-s');
        foreach ($results as $r) {
            if (isset($r->message) && $r->message != '') {
                $message1 = $r->message;
                ScheduleSMS::create([
                    'userid'       => Auth::guard('client')->user()->id,
                    'sender'       => $sender_id,
                    'receiver'     => $r->phone_number,
                    'amount'       => $msgcount,
                    'original_msg' => $message1,
                    'encrypt_msg'  => base64_encode($message1),
                    'submit_time'  => $schedule_time,
                    'ip'           => request()->ip(),
                    'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                    'file_send'    => $fileSend,
                ]);
            } else {
                ScheduleSMS::create([
                    'userid'       => Auth::guard('client')->user()->id,
                    'sender'       => $sender_id,
                    'receiver'     => $r->phone_number,
                    'amount'       => $msgcount,
                    'original_msg' => $message,
                    'encrypt_msg'  => base64_encode($message),
                    'submit_time'  => $schedule_time,
                    'ip'           => request()->ip(),
                    'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                    'file_send'    => $fileSend,
                ]);
            }

        }

        $remain_sms        = $sms_count - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-schedule-sms-file')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // smsHistory Function Start Here
    //======================================================================
    public function smsHistory()
    {

        $sms_history = SMSHistory::orderBy('updated_at', 'desc')->where('userid', Auth::guard('client')->user()->id)->get();
        return view('client.sms-history', compact('sms_history'));
    }

    //======================================================================
    // smsViewInbox Function Start Here
    //======================================================================
    public function smsViewInbox($id)
    {

        $inbox_info = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($inbox_info) {
            $sms_inbox = SMSInbox::where('msg_id', $id)->get();
            return view('client.sms-inbox', compact('sms_inbox', 'inbox_info'));
        } else {
            return redirect('user/sms/history')->with([
                'message'           => language_data('SMS Not Found'),
                'message_important' => true,
            ]);
        }

    }

    //======================================================================
    // deleteSMS Function Start Here
    //======================================================================
    public function deleteSMS($id)
    {

        $inbox_info = SMSHistory::where('userid', Auth::guard('client')->user()->id)->find($id);

        if ($inbox_info) {
            SMSInbox::where('msg_id', $id)->delete();
            $inbox_info->delete();

            return redirect('user/sms/history')->with([
                'message' => language_data('SMS info deleted successfully'),
            ]);
        } else {
            return redirect('sms/history')->with([
                'message'           => language_data('SMS Not Found'),
                'message_important' => true,
            ]);
        }

    }

    //======================================================================
    // apiInfo Function Start Here
    //======================================================================
    public function apiInfo()
    {
        return view('client.sms-api-info');
    }

    //======================================================================
    // updateApiInfo Function Start Here
    //======================================================================
    public function updateApiInfo(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'api_key' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms-api/info')->withErrors($v->errors());
        }

        if ($request->api_key != '') {
            Client::where('id', Auth::guard('client')->user()->id)->where('api_access', 'Yes')->update(['api_key' => $request->api_key]);
        }

        return redirect('user/sms-api/info')->with([
            'message' => language_data('API information updated successfully'),
        ]);

    }

    /*Version 1.1*/

    //======================================================================
    // updateScheduleSMS Function Start Here
    //======================================================================
    public function updateScheduleSMS()
    {
        $sms_history = ScheduleSMS::where('userid', Auth::guard('client')->user()->id)->get();
        return view('client.update-schedule-sms', compact('sms_history'));
    }

    //======================================================================
    // manageUpdateScheduleSMS Function Start Here
    //======================================================================
    public function manageUpdateScheduleSMS($id)
    {
        $sh = ScheduleSMS::find($id);

        if ($sh) {
            return view('client.manage-update-schedule-sms', compact('sh'));
        } else {
            return redirect('user/sms/update-schedule-sms')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    public function manageUpdateScheduleSMSFile($id)
    {
        $sh = ScheduleSMS::find($id);

        if ($sh) {
            return view('client.manage-update-schedule-sms-file', compact('sh'));
        } else {
            return redirect('user/sms/update-schedule-sms')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postUpdateScheduleSMS Function Start Here
    //======================================================================
    public function postUpdateScheduleSMS(Request $request)
    {

        $cmd = $request->cmd;

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client == '') {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                'message'           => language_data('Client info not found'),
                'message_important' => true,
            ]);
        }

        $gateway = SMSGateways::find($client->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $phone   = str_replace('+', '', $request->phone_number);
        $c_phone = PhoneNumber::get_code($phone);

        $sms_info = ScheduleSMS::find($cmd);

        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

        if ($sms_cost) {
            $total_cost = ($sms_cost->tariff * $msgcount);
            if ($total_cost == 0) {
                return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }

            $total_cost -= $sms_info->amount;

            if ($total_cost > $client->sms_limit) {
                return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }
        } else {
            return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/manage-update-schedule-sms/' . $cmd)->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));

        ScheduleSMS::where('id', $request->cmd)->update([
            'sender'       => $sender_id,
            'receiver'     => $request->phone_number,
            'amount'       => $msgcount,
            'original_msg' => $message,
            'submit_time'  => $schedule_time,
        ]);

        $remain_sms        = $client->sms_limit - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/update-schedule-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    public function postUpdateScheduleSMSFile(Request $request)
    {

        $cmd = $request->cmd;

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client == '') {
            return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->with([
                'message'           => language_data('Client info not found'),
                'message_important' => true,
            ]);
        }

        $gateway = SMSGateways::find($client->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $phone   = str_replace('+', '', $request->phone_number);
        $c_phone = PhoneNumber::get_code($phone);

        $sms_info = ScheduleSMS::find($cmd);

        $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

        if ($sms_cost) {
            $total_cost = ($sms_cost->tariff * $msgcount);
            if ($total_cost == 0) {
                return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }

            $total_cost -= $sms_info->amount;

            if ($total_cost > $client->sms_limit) {
                return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }
        } else {
            return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/manage-update-schedule-sms-file/' . $cmd)->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));

        ScheduleSMS::where('id', $request->cmd)->update([
            'sender'       => $sender_id,
            'receiver'     => $request->phone_number,
            'amount'       => $msgcount,
            'original_msg' => $message,
            'submit_time'  => $schedule_time,
        ]);

        $remain_sms        = $client->sms_limit - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/historyFileSchedule')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // deleteScheduleSMS Function Start Here
    //======================================================================
    public function deleteScheduleSMS($id)
    {

        $sh = ScheduleSMS::find($id);
        if ($sh) {
            $client = Client::find($sh->userid);
            $client->sms_limit += $sh->amount;
            $client->save();

            $sh->delete();
            return redirect('user/sms/update-schedule-sms')->with([
                'message' => language_data('SMS info deleted successfully'),
            ]);
        } else {
            return redirect('user/sms/update-schedule-sms')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    public function deleteScheduleSMSFile($id)
    {

        $sh = ScheduleSMS::find($id);
        if ($sh) {
            $client = Client::find($sh->userid);
            $client->sms_limit += $sh->amount;
            $client->save();

            $sh->delete();
            return redirect('user/sms/historyFileSchedule')->with([
                'message' => language_data('SMS info deleted successfully'),
            ]);
        } else {
            return redirect('user/sms/historyFileSchedule')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // Version 1.2
    //======================================================================

    //======================================================================
    // sendBulkBirthdaySMS Function Start Here
    //======================================================================
    public function sendBulkBirthdaySMS()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        //return view('client.send-single-sms', compact('sender_ids'));
        return view('client.send-bulk-birthday-sms', compact('sender_ids'));
    }

    //======================================================================
    // downloadBirthdaySMSFile Function Start Here
    //======================================================================
    public function downloadBirthdaySMSFile()
    {
        return response()->download('assets/test_file/birthday-sms.csv');
    }

    //======================================================================
    // postBirthdaySMS Function Start Here
    //======================================================================
    public function postBirthdaySMS(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-bulk-birthday-sms')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-bulk-birthday-sms')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();
        $supportedExt   = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get();

        if ($results->count() <= 0) {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('Client Group is empty'),
                'message_important' => true,
            ]);
        }

        $get_cost              = 0;
        $get_inactive_coverage = [];

        foreach ($results as $c) {
            $phone   = str_replace('+', '', $c->phone_number);
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {
                $sms_charge = $sms_cost->tariff;
                $get_cost += $sms_charge;
            } else {
                array_push($get_inactive_coverage, 'found');
            }
        }

        if (in_array('found', $get_inactive_coverage)) {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        $total_cost = $get_cost * $msgcount;

        if ($total_cost == 0) {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        if ($total_cost > $sms_count) {
            return redirect('user/sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }
        $fileSend = 'gui-tin-sinh-nhat-' . date('d-m-Y-H-i-s');
        //print_r($results);die;
        foreach ($results as $r) {
            if ($r->birthday != '') {
                //$schedule_time = date('Y-m-d H:i:s', strtotime($r->birthday));
                $orig_date = $r->birthday;
                // split date & time into array
                $date_time = preg_split('/\s+/', $orig_date);
                // date in array formated as ddmmyyyy
                list($d, $m, $y) = preg_split('/\//', $date_time[0]);
                // date re-formated as yyyy-mm-dd for MySQL
                $new_date = sprintf('%4d-%02d-%02d', $y, $m, $d);
                // And add time on again with a space for MySQL datetime format
                $schedule_time = $new_date . ' ' . $date_time[1];
                if (isset($r->message) && $r->message != '') {
                    $message1 = $r->message;
                    ScheduleSMS::create([
                        'userid'       => Auth::guard('client')->user()->id,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $message1,
                        'encrypt_msg'  => base64_encode($message1),
                        'submit_time'  => $schedule_time,
                        'ip'           => request()->ip(),
                        'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                        'file_send'    => $fileSend,
                    ]);
                } else {
                    ScheduleSMS::create([
                        'userid'       => Auth::guard('client')->user()->id,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $message,
                        'encrypt_msg'  => base64_encode($message),
                        'submit_time'  => $schedule_time,
                        'ip'           => request()->ip(),
                        'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                        'file_send'    => $fileSend,
                    ]);
                }

            }
        }
        $remain_sms        = $sms_count - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-bulk-birthday-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // sendBulkSMSRemainder Function Start Here
    //======================================================================
    public function sendBulkSMSRemainder()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        //return view('client.send-single-sms', compact('sender_ids'));
        return view('client.send-bulk-remainder-sms', compact('sender_ids'));
    }

    //======================================================================
    // downloadRemainderSMSFile Function Start Here
    //======================================================================
    public function downloadRemainderSMSFile()
    {
        return response()->download('assets/test_file/remainder-sms.csv');
    }

    //======================================================================
    // postRemainderSMS Function Start Here
    //======================================================================
    public function postRemainderSMS(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-bulk-sms-remainder')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-bulk-sms-remainder')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message  = $request->message;
        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();
        $supportedExt   = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get();

        if ($results->count() <= 0) {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('Client Group is empty'),
                'message_important' => true,
            ]);
        }

        $get_cost              = 0;
        $get_inactive_coverage = [];

        foreach ($results as $c) {
            $phone   = str_replace('+', '', $c->phone_number);
            $c_phone = PhoneNumber::get_code($phone);

            $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

            if ($sms_cost) {
                $sms_charge = $sms_cost->tariff;
                $get_cost += $sms_charge;
            } else {
                array_push($get_inactive_coverage, 'found');
            }
        }

        if (in_array('found', $get_inactive_coverage)) {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('Phone Number Coverage are not active'),
                'message_important' => true,
            ]);
        }

        $total_cost = $get_cost * $msgcount;

        if ($total_cost == 0) {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }

        if ($total_cost > $sms_count) {
            return redirect('user/sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('You do not have enough sms balance'),
                'message_important' => true,
            ]);
        }
        $fileSend = 'gui-tin-nhac-nho-' . date('d-m-Y-H-i-s');
        foreach ($results as $r) {

            if ($r->remainder_date != '') {
                //$schedule_time = date('Y-m-d H:i:s', strtotime($r->remainder_date));
                $orig_date = $r->remainder_date;
                // split date & time into array
                $date_time = preg_split('/\s+/', $orig_date);
                // date in array formated as ddmmyyyy
                list($d, $m, $y) = preg_split('/\//', $date_time[0]);
                // date re-formated as yyyy-mm-dd for MySQL
                $new_date = sprintf('%4d-%02d-%02d', $y, $m, $d);
                // And add time on again with a space for MySQL datetime format
                $schedule_time = $new_date . ' ' . $date_time[1];
                if (isset($r->message) && $r->message != '') {
                    $message1 = $r->message;
                    ScheduleSMS::create([
                        'userid'       => Auth::guard('client')->user()->id,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $message1,
                        'encrypt_msg'  => base64_encode($message1),
                        'submit_time'  => $schedule_time,
                        'ip'           => request()->ip(),
                        'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                        'file_send'    => $fileSend,
                    ]);
                } else {
                    ScheduleSMS::create([
                        'userid'       => Auth::guard('client')->user()->id,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $message,
                        'encrypt_msg'  => base64_encode($message),
                        'submit_time'  => $schedule_time,
                        'ip'           => request()->ip(),
                        'use_gateway'  => Auth::guard('client')->user()->sms_gateway,
                        'file_send'    => $fileSend,
                    ]);
                }

            }
        }

        $remain_sms        = $sms_count - $total_cost;
        $client->sms_limit = $remain_sms;
        $client->save();

        return redirect('user/sms/send-bulk-sms-remainder')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);
    }

    /*Verson 1.2*/

    //======================================================================
    // importPhoneNumber Function Start Here
    //======================================================================
    public function importPhoneNumber()
    {
        $clientGroups = ImportPhoneNumber::where('user_id', Auth::guard('client')->user()->id)->get();
        return view('client.import-phone-number', compact('clientGroups'));
    }

    //======================================================================
    // postImportPhoneNumber Function Start Here
    //======================================================================
    public function postImportPhoneNumber(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/import-phone-number')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'group_name' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/import-phone-number')->withErrors($v->errors());
        }

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('user/sms/import-phone-number')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get()->toArray();
        $results = json_encode($results);

        ImportPhoneNumber::create([
            'user_id'    => Auth::guard('client')->user()->id,
            'group_name' => $request->group_name,
            'numbers'    => $results,
        ]);

        return redirect('user/sms/import-phone-number')->with([
            'message' => 'Tạo danh bạ thành công',
        ]);

    }

    //======================================================================
    // deleteImportPhoneNumber Function Start Here
    //======================================================================
    public function deleteImportPhoneNumber($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/import-phone-number')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $clientGroup = ImportPhoneNumber::find($id);

        if ($clientGroup) {

            $clientGroup->delete();

            return redirect('user/sms/import-phone-number')->with([
                'message' => language_data('Client group deleted successfully'),
            ]);

        } else {
            return redirect('user/sms/import-phone-number')->with([
                'message'           => language_data('Client Group not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // sendSMSByPhoneNumber Function Start Here
    //======================================================================
    public function sendSMSByPhoneNumber()
    {
        //huyhh add 2018.01.04
        $all_sender_id = SenderIdManage::all();
        $all_ids       = [];
        foreach ($all_sender_id as $sid) {
            if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
                array_push($all_ids, $sid->id);
            }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        //return view('client.send-single-sms', compact('sender_ids'));
        $client_group = ImportPhoneNumber::where('user_id', Auth::guard('client')->user()->id)->get();
        return view('client.send-sms-by-phone-number', compact('client_group', 'sender_ids'));

    }

    //======================================================================
    // postSendSMSByPhoneNumber Function Start Here
    //======================================================================
    public function postSendSMSByPhoneNumber(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/sms/send-sms-phone-number')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'client_group' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/sms/send-sms-phone-number')->withErrors($v->errors());
        }

        $client    = Client::find(Auth::guard('client')->user()->id);
        $sms_count = $client->sms_limit;
        $sender_id = $request->sender_id;

        if ($sender_id != '' && app_config('sender_id_verification') == '1') {
            $all_sender_id = SenderIdManage::all();
            $all_ids       = [];

            foreach ($all_sender_id as $sid) {
                $client_array = json_decode($sid->cl_id);

                if (in_array('0', $client_array)) {
                    array_push($all_ids, $sender_id);
                } elseif (in_array(Auth::guard('client')->user()->id, $client_array)) {
                    array_push($all_ids, $sid->sender_id);
                }
            }
            $all_ids = array_unique($all_ids);

            if (!in_array($sender_id, $all_ids)) {
                return redirect('user/sms/send-sms-phone-number')->with([
                    'message'           => language_data('This Sender ID have Blocked By Administrator'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($client->sms_gateway);

        if ($gateway->status != 'Active') {
            return redirect('user/sms/send-sms-phone-number')->with([
                'message'           => language_data('SMS gateway not active.Contact with Provider'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $client->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message  = $request->message;
        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);

        $get_numbers = ImportPhoneNumber::find($request->client_group);

        if ($get_numbers) {
            $results = json_decode($get_numbers->numbers);
            if (count($results) <= 0) {
                return redirect('user/sms/send-sms-phone-number')->with([
                    'message'           => language_data('Client Group is empty'),
                    'message_important' => true,
                ]);
            }

            $get_cost              = 0;
            $get_inactive_coverage = [];

            $fileSend = 'gui-theo-danh-ba-' . date('d-m-Y-H-i-s');
            foreach ($results as $c) {
                $phone   = str_replace('+', '', $c->phone_number);
                $c_phone = PhoneNumber::get_code($phone);

                $sms_cost = IntCountryCodes::where('country_code', $c_phone)->where('active', '1')->first();

                if ($sms_cost) {
                    $sms_charge = $sms_cost->tariff;
                    $get_cost += $sms_charge;
                } else {
                    array_push($get_inactive_coverage, 'found');
                }
            }

            if (in_array('found', $get_inactive_coverage)) {
                return redirect('user/sms/send-sms-phone-number')->with([
                    'message'           => language_data('Phone Number Coverage are not active'),
                    'message_important' => true,
                ]);
            }

            $total_cost = $get_cost * $msgcount;

            if ($total_cost == 0) {
                return redirect('user/sms/send-sms-phone-number')->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }

            if ($total_cost > $sms_count) {
                return redirect('user/sms/send-sms-phone-number')->with([
                    'message'           => language_data('You do not have enough sms balance'),
                    'message_important' => true,
                ]);
            }

            StoreBulkSMS::create([
                'userid'      => Auth::guard('client')->user()->id,
                'sender'      => $sender_id,
                'receiver'    => $get_numbers->numbers,
                'amount'      => $msgcount,
                'message'     => $message,
                'status'      => 0,
                'use_gateway' => $gateway->id,
                'file_send'   => $fileSend,
            ]);

            $remain_sms        = $sms_count - $total_cost;
            $client->sms_limit = $remain_sms;
            $client->save();

            return redirect('user/sms/send-sms-phone-number')->with([
                'message' => language_data('SMS added in queue and will deliver one by one'),
            ]);

        } else {
            return redirect('user/sms/send-sms-phone-number')->with([
                'message'           => language_data('Client Group not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // smsHistoryFile Function Start Here
    //======================================================================
    public function smsHistoryFile(Request $request)
    {

        $sql = "";
        if ($request->has('mobile')) {
            $sql .= " and h.receiver='" . $request->mobile . "'";
        }

        if ($request->has('status') && $request->status > 0) {
            $status = $request->status;
            if ($status == 1) {
                $sql .= " and i.status like '%Success%' ";
            } else if ($status == 2) {
                $sql .= " and i.status like '%Failed%' ";
            }

        }

        if ($request->has('gui_mot_tin_lap_lich')) {
            $sql .= " and i.file_send='" . $request->gui_mot_tin_lap_lich . "'";
        }

        if ($request->has('gui_theo_danh_ba')) {
            $sql .= " and i.file_send='" . $request->gui_theo_danh_ba . "'";
        }

        if ($request->has('gui_tin_cho_nhom_kh')) {
            $sql .= " and i.file_send='" . $request->gui_tin_cho_nhom_kh . "'";
        }

        if ($request->has('gui_hang_loat_tu_file')) {
            $sql .= " and i.file_send='" . $request->gui_hang_loat_tu_file . "'";
        }

        if ($request->has('gui_tin_sinh_nhat')) {
            $sql .= " and i.file_send='" . $request->gui_tin_sinh_nhat . "'";
        }

        if ($request->has('gui_tin_nhac_nho')) {
            $sql .= " and i.file_send='" . $request->gui_tin_nhac_nho . "'";
        }

        if ($request->has('gui_tin_lap_lich_cho_nhom_kh')) {
            $sql .= " and i.file_send='" . $request->gui_tin_lap_lich_cho_nhom_kh . "'";
        }

        if ($request->has('lap_lich_gui_tu_file')) {
            $sql .= " and i.file_send='" . $request->lap_lich_gui_tu_file . "'";
        }

        if ($request->has('created_date')) {
            $createdDate = $request->created_date;
            $createdDate = date('Y-m-d', strtotime($createdDate));
            $sql .= " and i.created_at like '" . $createdDate . "%' ";
        }
        /*$sms_history = DB::table('sys_sms_history')
        ->join('sys_sms_inbox', 'sys_sms_history.id', '=', 'sys_sms_inbox.msg_id')
        ->get();*/
        $sql = "select h.id,h.userid,h.receiver,h.sender,i.original_msg,i.`status`,i.created_at,i.send_by,i.file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and h.userid=" . Auth::guard('client')->user()->id . $sql;
        $sql .= " order by i.id desc";
        $sms_history = DB::select($sql);
        //gui 1 tin lap lich
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-mot-tin-lap-lich-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_mot_tin_lap_lich = DB::select($sql);

        //gui-theo-danh-ba
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-theo-danh-ba-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_theo_danh_ba = DB::select($sql);

        //Gửi tin cho nhóm khách hàng
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-nhom-kh-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_tin_cho_nhom_kh = DB::select($sql);

        //gui-hang-loat-tu-file-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-hang-loat-tu-file-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_hang_loat_tu_file = DB::select($sql);

        //gui-tin-sinh-nhat-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-sinh-nhat-%'and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_tin_sinh_nhat = DB::select($sql);

        //gui tin nhac nho
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-nhac-nho-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_tin_nhac_nho = DB::select($sql);

        //gui-tin-lap-lich-nhom-kh-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-lap-lich-nhom-kh-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $gui_tin_lap_lich_cho_nhom_kh = DB::select($sql);

        //lap-lich-gui-tu-file-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'lap-lich-gui-tu-file-%' and h.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by i.id desc";
        $lap_lich_gui_tu_file = DB::select($sql);
        return view('client.sms-history-file', compact('sms_history', 'fileSendList', 'gui_mot_tin_lap_lich', 'gui_tin_sinh_nhat', 'gui_tin_nhac_nho', 'gui_tin_lap_lich_cho_nhom_kh', 'lap_lich_gui_tu_file', 'gui_theo_danh_ba', 'gui_tin_cho_nhom_kh', 'gui_hang_loat_tu_file'));
    }

    public function findMobile(Request $request)
    {
        $smsHistory = SMSHistory::where('receiver', 'like', '%' . $request->get('q') . '%')->where('userid', Auth::guard('client')->user()->id)->take(5)->get();
        return response()->json($smsHistory);
    }

    public function findMobileSchedule(Request $request)
    {
        $smsHistory = ScheduleSMS::where('receiver', 'like', '%' . $request->get('q') . '%')->where('userid', Auth::guard('client')->user()->id)->take(5)->get();
        return response()->json($smsHistory);
    }

    public function smsHistoryFileSchedule(Request $request)
    {

        $userId = Auth::guard('client')->user()->id;
        $sql    = " ";
        if ($request->has('mobile')) {
            $sql .= " and s.receiver='" . $request->mobile . "'";
        }

        /*if ($request->has('file_send') && strlen($request->file_send) > 0) {
        $sql .= " and s.file_send='" . $request->file_send . "'";
        }*/

        if ($request->has('submit_time')) {
            $submit_time = $request->submit_time;
            $submit_time = date('Y-m-d', strtotime($submit_time));
            $sql .= " and s.submit_time like '" . $submit_time . "%' ";
        }
        //gui 1 tin lap lich
        //gui-mot-tin-lap-lich-
        if ($request->has('gui_mot_tin_lap_lich')) {
            $sql .= " and s.file_send='" . $request->gui_mot_tin_lap_lich . "'";
        }

        if ($request->has('gui_tin_lap_lich_cho_nhom_kh')) {
            $sql .= " and s.file_send='" . $request->gui_tin_lap_lich_cho_nhom_kh . "'";
        }

        if ($request->has('gui_tin_sinh_nhat')) {
            $sql .= " and s.file_send='" . $request->gui_tin_sinh_nhat . "'";
        }

        if ($request->has('gui_tin_nhac_nho')) {
            $sql .= " and s.file_send='" . $request->gui_tin_nhac_nho . "'";
        }

        if ($request->has('lap_lich_gui_tu_file')) {
            $sql .= " and s.file_send='" . $request->lap_lich_gui_tu_file . "'";
        }

        $sql = "select * from sys_schedule_sms as s where s.userid=" . Auth::guard('client')->user()->id . $sql;
        $sql .= " order by id desc";
        //echo $sql;
        $sms_history = DB::select($sql);

        //gui 1 tin lap lich
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-mot-tin-lap-lich-%' and s.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by id desc";
        $gui_mot_tin_lap_lich = DB::select($sql);

        //gui-tin-lap-lich-cho-nhom-khach-hang
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-tin-lap-lich-nhom-kh-%' and s.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by id desc";
        $gui_tin_lap_lich_cho_nhom_kh = DB::select($sql);

        //gui-tin-sinh-nhat-
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-tin-sinh-nhat-%' and s.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by id desc";
        $gui_tin_sinh_nhat = DB::select($sql);

        //gui tin nhac nho
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-tin-nhac-nho-%' and s.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by id desc";
        $gui_tin_nhac_nho = DB::select($sql);

        //lap-lich-gui-tu-file-
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'lap-lich-gui-tu-file-%' and s.userid=" . Auth::guard('client')->user()->id;
        $sql .= " order by id desc";
        $lap_lich_gui_tu_file = DB::select($sql);

        //get all file
        //$sql          = "select DISTINCT file_send from sys_schedule_sms";
        //$fileSendList = DB::select($sql);
        return view('client.sms-history-file-schedule', compact('sms_history', 'gui_mot_tin_lap_lich', 'gui_tin_sinh_nhat', 'gui_tin_nhac_nho', 'gui_tin_lap_lich_cho_nhom_kh', 'lap_lich_gui_tu_file'));
    }
}
