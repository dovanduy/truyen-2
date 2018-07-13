<?php

namespace App\Http\Controllers;

use App\AppConfig;
use App\Classes\Permission;
use App\Client;
use App\ClientGroups;
use App\CustomSMSGateways;
use App\ImportPhoneNumber;
use App\IntCountryCodes;
use App\Jobs\SendBulkSMS;
use App\ScheduleSMS;
use App\SenderIdManage;
use App\SMSGateways;
use App\SMSPlanFeature;
use App\SMSPricePlan;
use App\SMSTemplates;
use App\StoreBulkSMS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class SMSController extends Controller
{
    /**
     * SMSController constructor.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // coverage Function Start Here
    //======================================================================
    public function coverage()
    {

        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $country_codes = IntCountryCodes::all();
        return view('admin.coverage', compact('country_codes'));
    }

    //======================================================================
    // manageCoverage Function Start Here
    //======================================================================
    public function manageCoverage($id)
    {
        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $coverage = IntCountryCodes::find($id);
        if ($coverage) {
            return view('admin.manage-coverage', compact('coverage'));
        } else {
            return redirect('sms/coverage')->with([
                'message'           => language_data('Information not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postManageCoverage Function Start Here
    //======================================================================
    public function postManageCoverage(Request $request)
    {
        $cmd = Input::get('cmd');

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/manage-coverage/' . $cmd)->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'coverage';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'tariff' => 'required', 'status' => 'required',
        ]);
        if ($v->fails()) {
            return redirect('sms/manage-coverage/' . $cmd)->withErrors($v->errors());
        }

        $coverage = IntCountryCodes::find($cmd);
        if ($coverage) {

            $coverage->tariff = $request->tariff;
            $coverage->active = $request->status;
            $coverage->save();

            return redirect('sms/manage-coverage/' . $cmd)->with([
                'message' => language_data('Coverage updated successfully'),
            ]);

        } else {
            return redirect('sms/coverage')->with([
                'message'           => language_data('Information not found'),
                'message_important' => true,
            ]);
        }

    }

    //======================================================================
    // senderIdManagement Function Start Here
    //======================================================================
    public function senderIdManagement()
    {

        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $sender_id = SenderIdManage::all();
        return view('admin.sender-id-management', compact('sender_id'));
    }

    //======================================================================
    // addSenderID Function Start Here
    //======================================================================
    public function addSenderID()
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $clients = Client::where('status', 'Active')->get();
        return view('admin.add-sender-id', compact('clients'));
    }

    //======================================================================
    // postNewSenderID Function Start Here
    //======================================================================
    public function postNewSenderID(Request $request)
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'client_id' => 'required', 'status' => 'required', 'sender_id' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/add-sender-id')->withErrors($v->errors());
        }
        $sender_ids = $request->sender_id;
        $clients_id = json_encode($request->client_id);

        if (is_array($sender_ids)) {
            foreach ($sender_ids as $ids) {
                $sender_id            = new SenderIdManage();
                $sender_id->sender_id = $ids;
                $sender_id->cl_id     = $clients_id;
                $sender_id->status    = $request->status;
                $sender_id->save();
            }
        }

        return redirect('sms/sender-id-management')->with([
            'message' => language_data('Sender Id added successfully'),
        ]);

    }

    //======================================================================
    // viewSenderID Function Start Here
    //======================================================================
    public function viewSenderID($id)
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $senderId = SenderIdManage::find($id);
        if ($senderId) {
            $clients           = Client::where('status', 'Active')->get();
            $sender_id_clients = json_decode($senderId->cl_id);
            if (in_array('0', $sender_id_clients)) {
                $selected_all = true;
            } else {
                $selected_all = false;
            }

            return view('admin.manage-sender-id', compact('clients', 'senderId', 'sender_id_clients', 'selected_all'));
        } else {
            return redirect('sms/sender-id-management')->with([
                'message'           => language_data('Sender Id not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postUpdateSenderID Function Start Here
    //======================================================================
    public function postUpdateSenderID(Request $request)
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'client_id' => 'required', 'status' => 'required', 'sender_id' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/view-sender-id/' . $cmd)->withErrors($v->errors());
        }

        $senderId = SenderIdManage::find($cmd);
        if ($senderId) {
            $senderId->sender_id = $request->sender_id;
            $senderId->cl_id     = json_encode($request->client_id);
            $senderId->status    = $request->status;
            $senderId->save();
            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender id updated successfully'),
            ]);
        } else {
            return redirect('sms/sender-id-management')->with([
                'message'           => language_data('Sender Id not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // deleteSenderID Function Start Here
    //======================================================================
    public function deleteSenderID($id)
    {
        $self = 'sender-id-management';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $senderId = SenderIdManage::find($id);
        if ($senderId) {
            $senderId->delete();

            return redirect('sms/sender-id-management')->with([
                'message' => language_data('Sender id deleted successfully'),
            ]);

        } else {
            return redirect('sms/sender-id-management')->with([
                'message'           => language_data('Sender Id not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // pricePlan Function Start Here
    //======================================================================
    public function pricePlan()
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $price_plan = SMSPricePlan::all();
        return view('admin.sms-price-plan', compact('price_plan'));
    }

    //======================================================================
    // addPricePlan Function Start Here
    //======================================================================
    public function addPricePlan()
    {
        $self = 'add-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        return view('admin.add-price-plan');
    }

    //======================================================================
    // postNewPricePlan Function Start Here
    //======================================================================
    public function postNewPricePlan(Request $request)
    {
        $self = 'add-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'plan_name' => 'required', 'price' => 'required', 'show_in_client' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/add-price-plan')->withErrors($v->errors());
        }

        $exist_plan = SMSPricePlan::where('plan_name', $request->plan_name)->first();
        if ($exist_plan) {
            return redirect('sms/add-price-plan')->with([
                'message'           => language_data('Plan already exist'),
                'message_important' => true,
            ]);
        }

        $plan            = new SMSPricePlan();
        $plan->plan_name = $request->plan_name;
        $plan->price     = $request->price;
        $plan->popular   = $request->popular;
        $plan->status    = $request->show_in_client;
        $plan->save();

        return redirect('sms/price-plan')->with([
            'message' => language_data('Plan added successfully'),
        ]);

    }

    //======================================================================
    // managePricePlan Function Start Here
    //======================================================================
    public function managePricePlan($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $price_plan = SMSPricePlan::find($id);
        if ($price_plan) {
            return view('admin.manage-price-plan', compact('price_plan'));
        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postManagePricePlan Function Start Here
    //======================================================================
    public function postManagePricePlan(Request $request)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        $cmd = Input::get('cmd');
        $v   = \Validator::make($request->all(), [
            'plan_name' => 'required', 'price' => 'required', 'show_in_client' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-price-plan/' . $cmd)->withErrors($v->errors());
        }
        $plan = SMSPricePlan::find($cmd);

        if ($plan) {
            if ($plan->plan_name != $request->plan_name) {
                $exist_plan = SMSPricePlan::where('plan_name', $request->plan_name)->first();
                if ($exist_plan) {
                    return redirect('sms/manage-price-plan/' . $cmd)->with([
                        'message'           => language_data('Plan already exist'),
                        'message_important' => true,
                    ]);
                }
            }

            $plan->plan_name = $request->plan_name;
            $plan->price     = $request->price;
            $plan->popular   = $request->popular;
            $plan->status    = $request->show_in_client;
            $plan->save();

            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan updated successfully'),
            ]);
        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan not found'),
                'message_important' => true,
            ]);
        }

    }

    //======================================================================
    // addPlanFeature Function Start Here
    //======================================================================
    public function addPlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $price_plan = SMSPricePlan::find($id);
        if ($price_plan) {
            return view('admin.add-plan-feature', compact('price_plan'));
        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postNewPlanFeature Function Start Here
    //======================================================================
    public function postNewPlanFeature(Request $request)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $cmd = Input::get('cmd');
        $v   = \Validator::make($request->all(), [
            'feature_name' => 'required', 'feature_value' => 'required', 'show_in_client' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/add-plan-feature/' . $cmd)->withErrors($v->errors());
        }

        $price_plan = SMSPricePlan::find($cmd);
        if ($price_plan) {
            $feature_name  = $request->feature_name;
            $feature_value = $request->feature_value;

            foreach ($feature_name as $key => $value) {
                SMSPlanFeature::create([
                    'pid'           => $cmd,
                    'feature_name'  => $value,
                    'feature_value' => $feature_value[$key],
                    'status'        => $request->show_in_client,
                ]);
            }

            return redirect('sms/price-plan')->with([
                'message' => language_data('Plan features added successfully'),
            ]);

        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan not found'),
                'message_important' => true,
            ]);
        }

    }

    //======================================================================
    // viewPlanFeature Function Start Here
    //======================================================================
    public function viewPlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $features = SMSPlanFeature::where('pid', $id)->get();
        return view('admin.view-plan-feature', compact('features'));

    }

    //======================================================================
    // managePlanFeature Function Start Here
    //======================================================================
    public function managePlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $plan_feature = SMSPlanFeature::find($id);
        if ($plan_feature) {
            return view('admin.manage-plan-feature', compact('plan_feature'));
        } else {
            return redirect('sms/view-plan-feature/' . $id)->with([
                'message'           => language_data('Plan feature not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postManagePlanFeature Function Start Here
    //======================================================================
    public function postManagePlanFeature(Request $request)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'feature_name' => 'required', 'feature_value' => 'required', 'show_in_client' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-plan-feature/' . $cmd)->withErrors($v->errors());
        }

        $plan_feature = SMSPlanFeature::find($cmd);
        if ($plan_feature->feature_name != $request->feature_name) {
            $exist = SMSPlanFeature::where('feature_name', $request->feature_name)->where('pid', $plan_feature->pid)->first();
            if ($exist) {
                return redirect('sms/manage-plan-feature/' . $cmd)->with([
                    'message'           => language_data('Feature already exist'),
                    'message_important' => true,
                ]);
            }
        }

        if ($plan_feature) {
            $plan_feature->feature_name  = $request->feature_name;
            $plan_feature->feature_value = $request->feature_value;
            $plan_feature->status        = $request->show_in_client;
            $plan_feature->save();

            return redirect('sms/view-plan-feature/' . $plan_feature->pid)->with([
                'message' => language_data('Feature updated successfully'),
            ]);

        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan feature not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // deletePlanFeature Function Start Here
    //======================================================================
    public function deletePlanFeature($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $plan_feature = SMSPlanFeature::find($id);
        if ($plan_feature) {
            $pid = $plan_feature->pid;
            $plan_feature->delete();
            return redirect('sms/view-plan-feature/' . $pid)->with([
                'message' => language_data('Plan feature deleted successfully'),
            ]);
        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan feature not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // deletePricePlan Function Start Here
    //======================================================================
    public function deletePricePlan($id)
    {
        $self = 'sms-price-plan';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $price_plan = SMSPricePlan::find($id);
        if ($price_plan) {
            SMSPlanFeature::where('pid', $id)->delete();
            $price_plan->delete();
            return redirect('sms/price-plan')->with([
                'message' => language_data('Price Plan deleted successfully'),
            ]);
        } else {
            return redirect('sms/price-plan')->with([
                'message'           => language_data('Plan feature not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // smsGateways Function Start Here
    //======================================================================
    public function smsGateways()
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $gateways = SMSGateways::all();
        return view('admin.sms-gateways', compact('gateways'));
    }

    //======================================================================
    // addSmsGateway Function Start Here
    //======================================================================
    public function addSmsGateway()
    {
        $self = 'add-sms-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        return view('admin.add-sms-gateway');
    }

    //======================================================================
    // postNewSmsGateway Function Start Here
    //======================================================================
    public function postNewSmsGateway(Request $request)
    {
        $self = 'add-sms-gateway';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'gateway_name' => 'required', 'gateway_link' => 'required', 'status' => 'required', 'destination_param' => 'required', 'message_param' => 'required', 'username_param' => 'required', 'username_value' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/add-sms-gateways')->withErrors($v->errors());
        }

        $exist_gateway = SMSGateways::where('name', $request->gateway_name)->first();
        if ($exist_gateway) {
            return redirect('sms/add-sms-gateways')->with([
                'message'           => language_data('Gateway already exist'),
                'message_important' => true,
            ]);
        }

        $gateway           = new SMSGateways();
        $gateway->name     = $request->gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->username = '';
        $gateway->password = '';
        $gateway->api_id   = '';
        $gateway->schedule = 'No';
        $gateway->custom   = 'Yes';
        $gateway->status   = $request->status;
        $gateway->two_way  = 'No';
        $gateway->save();

        $gateway_id = $gateway->id;

        if (is_int($gateway_id)) {
            $cgateway                 = new CustomSMSGateways();
            $cgateway->gateway_id     = $gateway_id;
            $cgateway->username_param = $request->username_param;
            $cgateway->username_value = $request->username_value;

            $cgateway->password_param  = $request->password_param;
            $cgateway->password_value  = $request->password_value;
            $cgateway->password_status = $request->password_status;

            $cgateway->action_param  = $request->action_param;
            $cgateway->action_value  = $request->action_value;
            $cgateway->action_status = $request->action_status;

            $cgateway->source_param  = $request->source_param;
            $cgateway->source_value  = $request->source_value;
            $cgateway->source_status = $request->source_status;

            $cgateway->destination_param = $request->destination_param;
            $cgateway->message_param     = $request->message_param;

            $cgateway->unicode_param  = $request->unicode_param;
            $cgateway->unicode_value  = $request->unicode_value;
            $cgateway->unicode_status = $request->unicode_status;

            $cgateway->route_param  = $request->route_param;
            $cgateway->route_value  = $request->route_value;
            $cgateway->route_status = $request->route_status;

            $cgateway->language_param  = $request->language_param;
            $cgateway->language_value  = $request->language_value;
            $cgateway->language_status = $request->language_status;

            $cgateway->custom_one_param  = $request->custom_one_param;
            $cgateway->custom_one_value  = $request->custom_one_value;
            $cgateway->custom_one_status = $request->custom_one_status;

            $cgateway->custom_two_param  = $request->custom_two_param;
            $cgateway->custom_two_value  = $request->custom_two_value;
            $cgateway->custom_two_status = $request->custom_two_status;

            $cgateway->custom_three_param  = $request->custom_three_param;
            $cgateway->custom_three_value  = $request->custom_three_value;
            $cgateway->custom_three_status = $request->custom_three_status;

            $cgateway->save();

            return redirect('sms/sms-gateway')->with([
                'message' => language_data('Custom gateway added successfully'),
            ]);
        } else {
            SMSGateways::where('id', $gateway_id)->delete();
            return redirect('sms/add-sms-gateways')->with([
                'message'           => language_data('Parameter or Value is empty'),
                'message_important' => true,
            ]);
        }

    }

    //======================================================================
    // customSmsGatewayManage Function Start Here
    //======================================================================
    public function customSmsGatewayManage($id)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($id);
        if ($gateway) {
            $gateway_info = CustomSMSGateways::where('gateway_id', $id)->first();
            return view('admin.manage-custom-sms-gateway', compact('gateway', 'gateway_info'));
        } else {
            return redirect('sms/sms-gateway')->with([
                'message'           => language_data('Gateway information not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postCustomSmsGateway Function Start Here
    //======================================================================
    public function postCustomSmsGateway(Request $request)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'gateway_name' => 'required', 'gateway_link' => 'required', 'status' => 'required', 'destination_param' => 'required', 'message_param' => 'required', 'username_param' => 'required', 'username_value' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/custom-gateway-manage/' . $cmd)->withErrors($v->errors());
        }

        $gateway      = SMSGateways::find($cmd);
        $gateway_name = $request->gateway_name;

        if ($gateway->custom == 'Yes') {
            if ($gateway_name == '') {
                return redirect('sms/custom-gateway-manage/' . $cmd)->with([
                    'message'           => language_data('Gateway name required'),
                    'message_important' => true,
                ]);
            }
        } else {
            $gateway_name = $gateway->name;
        }

        if ($gateway->name != $gateway_name) {
            $exist_gateway = SMSGateways::where('name', $gateway_name)->first();
            if ($exist_gateway) {
                return redirect('sms/custom-gateway-manage/' . $cmd)->with([
                    'message'           => language_data('Gateway already exist'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway->name     = $request->gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->status   = $request->status;
        $gateway->save();

        if ($cmd) {
            $cgateway = CustomSMSGateways::where('gateway_id', $cmd)->first();

            $cgateway->username_param = $request->username_param;
            $cgateway->username_value = $request->username_value;

            $cgateway->password_param  = $request->password_param;
            $cgateway->password_value  = $request->password_value;
            $cgateway->password_status = $request->password_status;

            $cgateway->action_param  = $request->action_param;
            $cgateway->action_value  = $request->action_value;
            $cgateway->action_status = $request->action_status;

            $cgateway->source_param  = $request->source_param;
            $cgateway->source_value  = $request->source_value;
            $cgateway->source_status = $request->source_status;

            $cgateway->destination_param = $request->destination_param;
            $cgateway->message_param     = $request->message_param;

            $cgateway->route_param  = $request->route_param;
            $cgateway->route_value  = $request->route_value;
            $cgateway->route_status = $request->route_status;

            $cgateway->language_param  = $request->language_param;
            $cgateway->language_value  = $request->language_value;
            $cgateway->language_status = $request->language_status;

            $cgateway->custom_one_param  = $request->custom_one_param;
            $cgateway->custom_one_value  = $request->custom_one_value;
            $cgateway->custom_one_status = $request->custom_one_status;

            $cgateway->custom_two_param  = $request->custom_two_param;
            $cgateway->custom_two_value  = $request->custom_two_value;
            $cgateway->custom_two_status = $request->custom_two_status;

            $cgateway->custom_three_param  = $request->custom_three_param;
            $cgateway->custom_three_value  = $request->custom_three_value;
            $cgateway->custom_three_status = $request->custom_three_status;

            $cgateway->save();

            return redirect('sms/sms-gateway')->with([
                'message' => language_data('Custom gateway updated successfully'),
            ]);
        } else {
            return redirect('sms/add-sms-gateways')->with([
                'message'           => language_data('Parameter or Value is empty'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // smsGatewayManage Function Start Here
    //======================================================================
    public function smsGatewayManage($id)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($id);
        if ($gateway) {
            return view('admin.manage-sms-gateway', compact('gateway'));
        } else {
            return redirect('sms/sms-gateway')->with([
                'message'           => language_data('Gateway information not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postManageSmsGateway Function Start Here
    //======================================================================
    public function postManageSmsGateway(Request $request)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $cmd = Input::get('cmd');

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/gateway-manage/' . $cmd)->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $v = \Validator::make($request->all(), [
            'schedule' => 'required', 'status' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/gateway-manage/' . $cmd)->withErrors($v->errors());
        }

        $gateway      = SMSGateways::find($cmd);
        $gateway_name = $gateway->name;

        if ($gateway_name == 'Asterisk') {
            $amiSetting = 'AMI_HOST=' . $request->gateway_link . '
AMI_PORT=' . $request->extra_value . '
AMI_USERNAME=' . $request->gateway_user_name . '
AMI_SECRET=' . $request->gateway_password . '
AMI_DEVICE=' . $request->device_name . '
';
            // @ignoreCodingStandard
            $env        = file_get_contents(base_path('.env'));
            $rows       = explode("\n", $env);
            $unwanted   = "AMI_HOST|AMI_PORT|AMI_USERNAME|AMI_SECRET|AMI_DEVICE";
            $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

            $cleanString = implode("\n", $cleanArray);
            $env         = $cleanString . $amiSetting;

            try {
                file_put_contents(base_path('.env'), $env);
            } catch (\Exception $e) {
                return redirect('sms/gateway-manage/' . $cmd)->with([
                    'message'           => $e->getMessage(),
                    'message_important' => true,
                ]);
            }
        }

        if ($gateway->custom == 'Yes') {
            if ($gateway_name == '') {
                return redirect('sms/gateway-manage/' . $cmd)->with([
                    'message'           => language_data('Gateway name required'),
                    'message_important' => true,
                ]);
            }
        } else {
            $gateway_name = $gateway->name;
        }

        if ($gateway->name != $gateway_name) {
            $exist_gateway = SMSGateways::where('name', $gateway_name)->first();
            if ($exist_gateway) {
                return redirect('sms/gateway-manage/' . $cmd)->with([
                    'message'           => language_data('Gateway already exist'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway->name     = $gateway_name;
        $gateway->api_link = $request->gateway_link;
        $gateway->username = $request->gateway_user_name;
        $gateway->password = $request->gateway_password;
        $gateway->api_id   = $request->extra_value;
        $gateway->schedule = $request->schedule;
        $gateway->status   = $request->status;
        $gateway->save();

        return redirect('sms/sms-gateway')->with([
            'message' => language_data('Custom gateway updated successfully'),
        ]);

    }

    //======================================================================
    // deleteSmsGateway Function Start Here
    //======================================================================
    public function deleteSmsGateway($id)
    {
        $self = 'sms-gateways';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $gateway = SMSGateways::find($id);
        if ($gateway && $gateway->custom == 'Yes') {
            $client = Client::where('sms_gateway', $id)->first();
            if ($client) {
                return redirect('sms/sms-gateway')->with([
                    'message'           => language_data('Client are registered with this gateway'),
                    'message_important' => true,
                ]);
            }

            CustomSMSGateways::where('gateway_id', $id)->delete();
            $gateway->delete();

            return redirect('sms/sms-gateway')->with([
                'message' => language_data('Gateway deleted successfully'),
            ]);

        } else {
            return redirect('sms/sms-gateway')->with([
                'message'           => language_data('Delete option disable for this gateway'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // sendSingleSMS Function Start Here
    //======================================================================
    public function sendSingleSMS()
    {
        $self = 'send-single-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_ids = [];
        if(isset(Auth::guard('client')->user()->id)){
        $all_sender_id = SenderIdManage::all();

        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        }
        if(count($all_ids)>0)
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();
        else    */
        $sender_ids = SenderIdManage::where('status', 'unblock')->get();
        //return view('client.send-sms-file', compact('sender_ids'));

        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-single-sms', compact('gateways', 'sms_templates', 'sender_ids'));

    }

    //======================================================================
    // sendBulkSMS Function Start Here
    //======================================================================
    public function sendBulkSMS()
    {
        $self = 'send-bulk-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $client_group  = ClientGroups::where('status', 'Yes')->get();
        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-bulk-sms', compact('client_group', 'gateways', 'sms_templates', 'sender_ids'));

    }

    //======================================================================
    // postGetTemplateInfo Function Start Here
    //======================================================================
    public function postGetTemplateInfo(Request $request)
    {
        $template = SMSTemplates::find($request->st_id);
        if ($template) {
            return response()->json([
                'from'    => $template->from,
                'message' => $template->message,
            ]);
        }
    }

    //======================================================================
    // renderSMS Start Here
    //======================================================================
    public function renderSMS($msg, $data)
    {
        preg_match_all('~<%(.*?)%>~s', $msg, $datas);
        $Html = $msg;
        foreach ($datas[1] as $value) {
            $Html = str_replace($value, $data[$value], $Html);
        }
        return str_replace(array("<%", "%>"), '', $Html);
    }

    //======================================================================
    // postSendBulkSMS Function Start Here
    //======================================================================
    public function postSendBulkSMS(Request $request)
    {
        $self = 'send-bulk-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'client_group' => 'required', 'sms_gateway' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-sms')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $fileSend = "gui-tin-nhom-kh-" . date('d-m-Y-H-i-s');
        Client::where('groupid', $request->client_group)->chunk(30, function ($clients) use ($gateway, $message, $sender_id, $msgcount, $cg_info, $fileSend) {
            foreach ($clients as $i => $c) {
                $msg_data = array(
                    'Phone Number'  => $c->phone,
                    'Email Address' => $c->email,
                    'User Name'     => $c->username,
                    'Company'       => $c->company,
                    'First Name'    => $c->fname,
                    'Last Name'     => $c->lname,
                );

                $get_message = $this->renderSMS($message, $msg_data);
                $this->dispatch(new SendBulkSMS('0', $c->phone, $gateway, $sender_id, $get_message, $msgcount, $cg_info, '', $fileSend));
            }
        });

        return redirect('sms/send-sms')->with([
            'message' => language_data('SMS added in queue and will deliver one by one'),
        ]);

    }

    //======================================================================
    // postSingleSMS Function Start Here
    //======================================================================
    public function postSingleSMS(Request $request)
    {
        $self = 'send-single-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'sms_gateway' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-single-sms')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-single-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;

        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;
        $fileSend  = 'gui-mot-tin-sms-' . date('d-m-Y-H-i-s');
        $this->dispatch(new SendBulkSMS('0', $request->phone_number, $gateway, $sender_id, $message, $msgcount, $cg_info, '', $fileSend));

        return redirect('sms/send-single-sms')->with([
            'message' => language_data('Please check sms history'),
        ]);

    }

    //======================================================================
    // sendBulkSMSFile Function Start Here
    //======================================================================
    public function sendBulkSMSFile()
    {
        $self = 'send-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-sms-file', compact('gateways', 'sms_templates', 'sender_ids'));

    }

    //======================================================================
    // downloadSampleSMSFile Function Start Here
    //======================================================================
    public function downloadSampleSMSFile()
    {
        return response()->download('assets/test_file/sms.csv');
    }

    //======================================================================
    // downloadBirthdaySMSFile Function Start Here
    //======================================================================
    public function downloadBirthdaySMSFile()
    {

        $self = 'bulk-birthday-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        return response()->download('assets/test_file/birthday-sms.csv');
    }

    //======================================================================
    // downloadRemainderSMSFile Function Start Here
    //======================================================================
    public function downloadRemainderSMSFile()
    {
        $self = 'bulk-sms-remainder';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        return response()->download('assets/test_file/remainder-sms.csv');
    }

    //======================================================================
    // postSMSFromFile Function Start Here
    //======================================================================
    public function postSMSFromFile(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/send-sms-file')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'send-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'sms_gateway' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-sms-file')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-sms-file')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('sms/send-sms-file')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results  = Excel::load($request->import_numbers)->get()->toJson();
        $fileSend = 'gui-hang-loat-tu-file-' . date('d-m-Y-H-i-s');
        StoreBulkSMS::create([
            'userid'      => 0,
            'sender'      => $sender_id,
            'receiver'    => $results,
            'amount'      => $msgcount,
            'message'     => $message,
            'status'      => 0,
            'use_gateway' => $gateway->id,
            'file_send'   => $fileSend,
        ]);

        return redirect('sms/send-sms-file')->with([
            'message' => language_data('SMS added in queue and will deliver one by one'),
        ]);

    }

    //======================================================================
    // postBirthdaySMS Function Start Here
    //======================================================================
    public function postBirthdaySMS(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'bulk-birthday-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'sms_gateway' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-bulk-birthday-sms')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('sms/send-bulk-birthday-sms')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results  = Excel::load($request->import_numbers)->get();
        $fileSend = 'gui-tin-sinh-nhat-' . date('d-m-Y-H-i-s');
        foreach ($results as $r) {
            $msg_data = array(
                'Phone Number'  => $r->phone_number,
                'Email Address' => $r->email_address,
                'User Name'     => $r->user_name,
                'Company'       => $r->company,
                'First Name'    => $r->first_name,
                'Last Name'     => $r->last_name,
            );

            /*if(isset($r->message) && $r->message!=''){
            $message=$r->message;
            }*/

            // $get_message = $this->renderSMS($message, $msg_data);

            if ($r->birthday != '') {

                //$birthday = strtotime($r->birthday);
                //$birthday = date('Y-m-d H:i:s', $birthday);
                $orig_date = $r->birthday;
                // split date & time into array
                $date_time = preg_split('/\s+/', $orig_date);
                // date in array formated as ddmmyyyy
                list($d, $m, $y) = preg_split('/\//', $date_time[0]);
                // date re-formated as yyyy-mm-dd for MySQL
                $new_date = sprintf('%4d-%02d-%02d', $y, $m, $d);
                // And add time on again with a space for MySQL datetime format
                $birthday = $new_date . ' ' . $date_time[1];
                if (isset($r->message) && $r->message != '') {
                    $message1    = $r->message;
                    $get_message = $this->renderSMS($message1, $msg_data);
                    ScheduleSMS::create([
                        'userid'       => 0,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $get_message,
                        'encrypt_msg'  => base64_encode($get_message),
                        'submit_time'  => $birthday,
                        'ip'           => request()->ip(),
                        'use_gateway'  => $gateway->id,
                        'file_send'    => $fileSend,
                    ]);
                } else {
                    $get_message = $this->renderSMS($message, $msg_data);
                    ScheduleSMS::create([
                        'userid'       => 0,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $get_message,
                        'encrypt_msg'  => base64_encode($get_message),
                        'submit_time'  => $birthday,
                        'ip'           => request()->ip(),
                        'use_gateway'  => $gateway->id,
                        'file_send'    => $fileSend,
                    ]);
                }

            }
        }

        return redirect('sms/send-bulk-birthday-sms')->with([
            'message' => language_data('SMS added in queue and will deliver one by one'),
        ]);

    }

    //======================================================================
    // postRemainderSMS Function Start Here
    //======================================================================
    public function postRemainderSMS(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'bulk-sms-remainder';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'sms_gateway' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-bulk-sms-remainder')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message   = $request->message;
        $msgcount  = strlen($message);
        $msgcount  = $msgcount / 160;
        $msgcount  = ceil($msgcount);
        $sender_id = $request->sender_id;

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('sms/send-bulk-sms-remainder')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get();
        //print_r($results);die;
        $fileSend = 'gui-tin-nhac-nho-' . date('d-m-Y-H-i-s');
        foreach ($results as $r) {
            $msg_data = array(
                'Phone Number'  => $r->phone_number,
                'Email Address' => $r->email_address,
                'User Name'     => $r->user_name,
                'Company'       => $r->company,
                'First Name'    => $r->first_name,
                'Last Name'     => $r->last_name,
            );
            // if(isset($r->message) && $r->message!=''){
            //         $message=$r->message;
            //     }
            // $get_message = $this->renderSMS($message, $msg_data);

            if ($r->remainder_date != '') {

                //$remainder_date = strtotime($r->remainder_date);
                //$remainder_date = date('Y-m-d H:i:s', $remainder_date);
                $orig_date = $r->remainder_date;
                // split date & time into array
                $date_time = preg_split('/\s+/', $orig_date);
                // date in array formated as ddmmyyyy
                list($d, $m, $y) = preg_split('/\//', $date_time[0]);
                // date re-formated as yyyy-mm-dd for MySQL
                $new_date = sprintf('%4d-%02d-%02d', $y, $m, $d);
                // And add time on again with a space for MySQL datetime format
                $remainder_date = $new_date . ' ' . $date_time[1];
                if (isset($r->message) && $r->message != '') {
                    $message1    = $r->message;
                    $get_message = $this->renderSMS($message1, $msg_data);
                    ScheduleSMS::create([
                        'userid'       => 0,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $get_message,
                        'encrypt_msg'  => base64_encode($get_message),
                        'submit_time'  => $remainder_date,
                        'ip'           => request()->ip(),
                        'use_gateway'  => $gateway->id,
                        'file_send'    => $fileSend,
                    ]);
                } else {
                    $get_message = $this->renderSMS($message, $msg_data);
                    ScheduleSMS::create([
                        'userid'       => 0,
                        'sender'       => $sender_id,
                        'receiver'     => $r->phone_number,
                        'amount'       => $msgcount,
                        'original_msg' => $get_message,
                        'encrypt_msg'  => base64_encode($get_message),
                        'submit_time'  => $remainder_date,
                        'ip'           => request()->ip(),
                        'use_gateway'  => $gateway->id,
                        'file_send'    => $fileSend,
                    ]);
                }

            }
        }

        return redirect('sms/send-bulk-sms-remainder')->with([
            'message' => language_data('SMS added in queue and will deliver one by one'),
        ]);

    }

    //======================================================================
    // sendSingleScheduleSMS Function Start Here
    //======================================================================
    public function sendSingleScheduleSMS()
    {

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        //        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-single-schedule-sms', compact('gateways', 'sms_templates', 'sender_ids'));
    }

    //======================================================================
    // sendScheduleSMS Function Start Here
    //======================================================================
    public function sendScheduleSMS()
    {

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        //        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $client_group  = ClientGroups::where('status', 'Yes')->get();
        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-schedule-sms', compact('client_group', 'gateways', 'sms_templates', 'sender_ids'));
    }

    //======================================================================
    // postSingleScheduleSMS Function Start Here
    //======================================================================
    public function postSingleScheduleSMS(Request $request)
    {

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'sms_gateway' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-single-schedule-sms')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-single-schedule-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }
        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $message       = $request->message;
        $msgcount      = strlen($message);
        $msgcount      = $msgcount / 160;
        $msgcount      = ceil($msgcount);
        $sender_id     = $request->sender_id;
        $gateway_id    = $gateway->id;
        $fileSend      = 'gui-mot-tin-lap-lich-' . date('d-m-Y-H-i-s');
        ScheduleSMS::create([
            'userid'       => 0,
            'sender'       => $sender_id,
            'receiver'     => $request->phone_number,
            'amount'       => $msgcount,
            'original_msg' => $message,
            'encrypt_msg'  => base64_encode($message),
            'submit_time'  => $schedule_time,
            'ip'           => request()->ip(),
            'use_gateway'  => $gateway_id,
            'file_send'    => $fileSend,
        ]);

        return redirect('sms/send-single-schedule-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }
    //======================================================================
    // postUpdateScheduleSMS Function Start Here
    //======================================================================
    public function postUpdateScheduleSMS(Request $request)
    {

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'sms_gateway' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-update-schedule-sms/' . $request->cmd)->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-single-schedule-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }
        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $message       = $request->message;
        $msgcount      = strlen($message);
        $msgcount      = $msgcount / 160;
        $msgcount      = ceil($msgcount);
        $sender_id     = $request->sender_id;
        $gateway_id    = $gateway->id;

        ScheduleSMS::where('id', $request->cmd)->update([
            'sender'       => $sender_id,
            'receiver'     => $request->phone_number,
            'amount'       => $msgcount,
            'original_msg' => $message,
            'submit_time'  => $schedule_time,
            'use_gateway'  => $gateway_id,
        ]);

        return redirect('sms/update-schedule-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // postUpdateScheduleSMSFile Function Start Here
    //======================================================================
    public function postUpdateScheduleSMSFile(Request $request)
    {

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'phone_number' => 'required', 'sms_gateway' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-update-schedule-sms-file/' . $request->cmd)->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-single-schedule-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }
        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $message       = $request->message;
        $msgcount      = strlen($message);
        $msgcount      = $msgcount / 160;
        $msgcount      = ceil($msgcount);
        $sender_id     = $request->sender_id;
        $gateway_id    = $gateway->id;

        ScheduleSMS::where('id', $request->cmd)->update([
            'sender'       => $sender_id,
            'receiver'     => $request->phone_number,
            'amount'       => $msgcount,
            'original_msg' => $message,
            'submit_time'  => $schedule_time,
            'use_gateway'  => $gateway_id,
        ]);

        return redirect('sms/historyFileSchedule')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // postScheduleSMS Function Start Here
    //======================================================================
    public function postScheduleSMS(Request $request)
    {

        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'client_group' => 'required', 'sms_gateway' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-schedule-sms')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-schedule-sms')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }
        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $message       = $request->message;
        $msgcount      = strlen($message);
        $msgcount      = $msgcount / 160;
        $msgcount      = ceil($msgcount);
        $sender_id     = $request->sender_id;
        $gateway_id    = $gateway->id;
        $fileSend      = 'gui-tin-lap-lich-nhom-kh-' . date('d-m-Y-H-i-s');
        Client::where('groupid', $request->client_group)->chunk(30, function ($clients) use ($gateway_id, $message, $sender_id, $msgcount, $schedule_time, $fileSend) {
            foreach ($clients as $c) {

                $msg_data = array(
                    'Phone Number'  => $c->phone,
                    'Email Address' => $c->email,
                    'User Name'     => $c->username,
                    'Company'       => $c->company,
                    'First Name'    => $c->fname,
                    'Last Name'     => $c->lname,
                );

                $get_message = $this->renderSMS($message, $msg_data);

                ScheduleSMS::create([
                    'userid'       => 0,
                    'sender'       => $sender_id,
                    'receiver'     => $c->phone,
                    'amount'       => $msgcount,
                    'original_msg' => $get_message,
                    'encrypt_msg'  => base64_encode($get_message),
                    'submit_time'  => $schedule_time,
                    'ip'           => request()->ip(),
                    'use_gateway'  => $gateway_id,
                    'file_send'    => $fileSend,
                ]);
            }
        });

        return redirect('sms/send-schedule-sms')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // sendScheduleSMSFile Function Start Here
    //======================================================================
    public function sendScheduleSMSFile()
    {

        $self = 'schedule-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        //        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-schedule-sms-file', compact('gateways', 'sms_templates', 'sender_ids'));
    }

    //======================================================================
    // postScheduleSMSFile Function Start Here
    //======================================================================
    public function postScheduleSMSFile(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/send-schedule-sms-file')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'schedule-sms-from-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'sms_gateway' => 'required', 'message' => 'required', 'schedule_time' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-schedule-sms-file')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-schedule-sms-file')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        $schedule_time = date('Y-m-d H:i:s', strtotime($request->schedule_time));
        $message       = $request->message;
        $msgcount      = strlen($message);
        $msgcount      = $msgcount / 160;
        $msgcount      = ceil($msgcount);
        $sender_id     = $request->sender_id;
        $gateway_id    = $gateway->id;

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('sms/send-schedule-sms-file')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results  = Excel::load($request->import_numbers)->get();
        $fileSend = 'lap-lich-gui-tu-file-' . date('d-m-Y-H-i-s');
        foreach ($results as $r) {
            $msg_data = array(
                'Phone Number'  => $r->phone_number,
                'Email Address' => $r->email_address,
                'User Name'     => $r->user_name,
                'Company'       => $r->company,
                'First Name'    => $r->first_name,
                'Last Name'     => $r->last_name,
            );
            if (isset($r->message) && $r->message != '') {
                $message1    = $r->message;
                $get_message = $this->renderSMS($message1, $msg_data);
                ScheduleSMS::create([
                    'userid'       => 0,
                    'sender'       => $sender_id,
                    'receiver'     => $r->phone_number,
                    'amount'       => $msgcount,
                    'original_msg' => $get_message,
                    'encrypt_msg'  => base64_encode($get_message),
                    'submit_time'  => $schedule_time,
                    'ip'           => request()->ip(),
                    'use_gateway'  => $gateway_id,
                    'file_send'    => $fileSend,
                ]);
            } else {
                $get_message = $this->renderSMS($message, $msg_data);
                ScheduleSMS::create([
                    'userid'       => 0,
                    'sender'       => $sender_id,
                    'receiver'     => $r->phone_number,
                    'amount'       => $msgcount,
                    'original_msg' => $get_message,
                    'encrypt_msg'  => base64_encode($get_message),
                    'submit_time'  => $schedule_time,
                    'ip'           => request()->ip(),
                    'use_gateway'  => $gateway_id,
                    'file_send'    => $fileSend,
                ]);
            }

        }

        return redirect('sms/send-schedule-sms-file')->with([
            'message' => language_data('SMS are scheduled. Deliver in correct time'),
        ]);

    }

    //======================================================================
    // updateScheduleSMS Function Start Here
    //======================================================================
    public function updateScheduleSMS()
    {
        $self = 'send-schedule-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $sms_history = ScheduleSMS::all();
        return view('admin.update-schedule-sms', compact('sms_history', 'gateways'));
    }

    //======================================================================
    // manageUpdateScheduleSMS Function Start Here
    //======================================================================
    public function manageUpdateScheduleSMS($id)
    {
        $sh = ScheduleSMS::find($id);

        if ($sh) {
            $gateways = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
            return view('admin.manage-update-schedule-sms', compact('gateways', 'sh'));
        } else {
            return redirect('sms/update-schedule-sms')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // manageUpdateScheduleSMS Function Start Here
    //======================================================================
    public function manageUpdateScheduleSMSFile($id)
    {
        $sh = ScheduleSMS::find($id);

        if ($sh) {
            $gateways = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
            return view('admin.manage-update-schedule-sms-file', compact('gateways', 'sh'));
        } else {
            return redirect('sms/historyFileSchedule')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // deleteScheduleSMS Function Start Here
    //======================================================================
    public function deleteScheduleSMS($id)
    {
        $sh = ScheduleSMS::find($id);
        if ($sh) {
            $sh->delete();
            return redirect('sms/update-schedule-sms')->with([
                'message' => language_data('SMS info deleted successfully'),
            ]);
        } else {
            return redirect('sms/update-schedule-sms')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // deleteScheduleSMSFile Function Start Here
    //======================================================================
    public function deleteScheduleSMSFile($id)
    {
        $sh = ScheduleSMS::find($id);
        if ($sh) {
            $sh->delete();
            return redirect('sms/historyFileSchedule')->with([
                'message' => language_data('SMS info deleted successfully'),
            ]);
        } else {
            return redirect('sms/historyFileSchedule')->with([
                'message'           => language_data('Please try again'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // sendBulkBirthdaySMS Function Start Here
    //======================================================================
    public function sendBulkBirthdaySMS()
    {

        $self = 'bulk-birthday-sms';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));

        $sender_ids    = SenderIdManage::all();
        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-bulk-birthday-sms', compact('gateways', 'sms_templates', 'sender_ids'));
    }

    //======================================================================
    // sendBulkSMSRemainder Function Start Here
    //======================================================================
    public function sendBulkSMSRemainder()
    {

        $self = 'bulk-sms-remainder';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        $all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $gateways      = SMSGateways::where('status', 'Active')->where('schedule', 'Yes')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-bulk-remainder-sms', compact('gateways', 'sms_templates', 'sender_ids'));
    }

    //======================================================================
    // smsTemplates Function Start Here
    //======================================================================
    public function smsTemplates()
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $sms_templates = SMSTemplates::all();
        return view('admin.sms-templates', compact('sms_templates'));
    }

    //======================================================================
    // createSmsTemplate Function Start Here
    //======================================================================
    public function createSmsTemplate()
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        return view('admin.create-sms-template');
    }

    //======================================================================
    // postSmsTemplate Function Start Here
    //======================================================================
    public function postSmsTemplate(Request $request)
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'template_name' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/create-sms-template')->withErrors($v->errors());
        }

        $exist = SMSTemplates::where('template_name', $request->template_name)->first();

        if ($exist) {
            return redirect('sms/create-sms-template')->with([
                'message'           => language_data('Template already exist'),
                'message_important' => true,
            ]);
        }

        $st                = new SMSTemplates();
        $st->cl_id         = '0';
        $st->template_name = $request->template_name;
        $st->from          = $request->from;
        $st->message       = $request->message;
        $st->global        = 'no';
        $st->status        = 'active';
        $st->save();

        return redirect('sms/sms-templates')->with([
            'message' => language_data('Sms template created successfully'),
        ]);

    }

    //======================================================================
    // manageSmsTemplate Function Start Here
    //======================================================================
    public function manageSmsTemplate($id)
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $st = SMSTemplates::find($id);

        if ($st) {

            return view('admin.manage-sms-template', compact('st'));

        } else {
            return redirect('sms/sms-templates')->with([
                'message'           => language_data('Sms template not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // postManageSmsTemplate Function Start Here
    //======================================================================
    public function postManageSmsTemplate(Request $request)
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'template_name' => 'required', 'message' => 'required', 'status' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/manage-sms-template/' . $cmd)->withErrors($v->errors());
        }

        $st = SMSTemplates::find($cmd);

        if ($st) {
            if ($st->template_name != $request->template_name) {
                $exist = SMSTemplates::where('template_name', $request->template_name)->first();

                if ($exist) {
                    return redirect('sms/manage-sms-template/' . $cmd)->with([
                        'message'           => language_data('Template already exist'),
                        'message_important' => true,
                    ]);
                }
            }

            $st->template_name = $request->template_name;
            $st->from          = $request->from;
            $st->message       = $request->message;
            $st->status        = $request->status;
            $st->save();

            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template updated successfully'),
            ]);

        } else {
            return redirect('sms/sms-templates')->with([
                'message'           => language_data('Sms template not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // deleteSmsTemplate Function Start Here
    //======================================================================
    public function deleteSmsTemplate($id)
    {

        $self = 'sms-templates';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $st = SMSTemplates::find($id);
        if ($st) {
            $st->delete();

            return redirect('sms/sms-templates')->with([
                'message' => language_data('Sms template delete successfully'),
            ]);

        } else {
            return redirect('sms/sms-templates')->with([
                'message'           => language_data('Sms template not found'),
                'message_important' => true,
            ]);
        }
    }

    //======================================================================
    // apiInfo Function Start Here
    //======================================================================
    public function apiInfo()
    {

        $self = 'sms-api';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $gateways = SMSGateways::where('status', 'Active')->get();
        return view('admin.sms-api-info', compact('gateways'));
    }

    //======================================================================
    // updateApiInfo Function Start Here
    //======================================================================
    public function updateApiInfo(Request $request)
    {

        $self = 'sms-api';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'api_url' => 'required', 'api_key' => 'required', 'sms_gateway' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('ultimate-sms-api/info')->withErrors($v->errors());
        }

        if ($request->api_url != '') {
            AppConfig::where('setting', '=', 'api_url')->update(['value' => $request->api_url]);
        }

        if ($request->api_key != '') {
            AppConfig::where('setting', '=', 'api_key')->update(['value' => $request->api_key]);
        }

        if ($request->sms_gateway != '') {
            AppConfig::where('setting', '=', 'sms_api_gateway')->update(['value' => $request->sms_gateway]);
        }

        return redirect('ultimate-sms-api/info')->with([
            'message' => language_data('API information updated successfully'),
        ]);

    }

    /*Verson 1.2*/

    //======================================================================
    // importPhoneNumber Function Start Here
    //======================================================================
    public function importPhoneNumber()
    {

        $self = 'import-phone-number';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $clientGroups = ImportPhoneNumber::where('user_id', '0')->get();
        return view('admin.import-phone-number', compact('clientGroups'));
    }

    //======================================================================
    // postImportPhoneNumber Function Start Here
    //======================================================================
    public function postImportPhoneNumber(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/import-phone-number')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'import-phone-number';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        $v = \Validator::make($request->all(), [
            'import_numbers' => 'required', 'group_name' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/import-phone-number')->withErrors($v->errors());
        }

        $file_extension = Input::file('import_numbers')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('sms/import-phone-number')->with([
                'message'           => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true,
            ]);
        }

        $results = Excel::load($request->import_numbers)->get()->toArray();
        $results = json_encode($results);

        ImportPhoneNumber::create([
            'user_id'    => 0,
            'group_name' => $request->group_name,
            'numbers'    => $results,
        ]);

        return redirect('sms/import-phone-number')->with([
            'message' => 'Phone number imported successfully',
        ]);

    }

    //======================================================================
    // deleteImportPhoneNumber Function Start Here
    //======================================================================
    public function deleteImportPhoneNumber($id)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/import-phone-number')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'import-phone-number';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $clientGroup = ImportPhoneNumber::find($id);

        if ($clientGroup) {

            $clientGroup->delete();

            return redirect('sms/import-phone-number')->with([
                'message' => language_data('Client group deleted successfully'),
            ]);

        } else {
            return redirect('sms/import-phone-number')->with([
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

        $self = 'sms-by-phone-number';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        //huyhh add 2018.01.04
        /*$all_sender_id = SenderIdManage::all();
        //$all_ids = [];
        foreach ($all_sender_id as $sid) {
        if (in_array(Auth::guard('client')->user()->id, json_decode($sid->cl_id))) {
        array_push($all_ids, $sid->id);
        }
        }
        $sender_ids = SenderIdManage::whereIn('id', $all_ids)->get();*/
        //return view('client.send-sms-file', compact('sender_ids'));
        $sender_ids    = SenderIdManage::all();
        $client_group  = ImportPhoneNumber::where('user_id', '0')->get();
        $gateways      = SMSGateways::where('status', 'Active')->get();
        $sms_templates = SMSTemplates::where('status', 'active')->get();

        return view('admin.send-sms-by-phone-number', compact('client_group', 'gateways', 'sms_templates', 'sender_ids'));

    }

    //======================================================================
    // postSendSMSByPhoneNumber Function Start Here
    //======================================================================
    public function postSendSMSByPhoneNumber(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('sms/send-sms-phone-number')->with([
                'message'           => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true,
            ]);
        }

        $self = 'sms-by-phone-number';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'client_group' => 'required', 'sms_gateway' => 'required', 'message' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('sms/send-sms-phone-number')->withErrors($v->errors());
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('sms/send-sms-phone-number')->with([
                'message'           => language_data('SMS gateway not active'),
                'message_important' => true,
            ]);
        }

        $message     = $request->message;
        $msgcount    = strlen($message);
        $msgcount    = $msgcount / 160;
        $msgcount    = ceil($msgcount);
        $sender_id   = $request->sender_id;
        $get_numbers = ImportPhoneNumber::find($request->client_group);
        $fileSend    = 'gui-theo-danh-ba-' . date('d-m-Y-H-i-s');
        if ($get_numbers) {

            StoreBulkSMS::create([
                'userid'      => 0,
                'sender'      => $sender_id,
                'receiver'    => $get_numbers->numbers,
                'amount'      => $msgcount,
                'message'     => $message,
                'status'      => 0,
                'use_gateway' => $gateway->id,
                'file_send'   => $fileSend,
            ]);

            return redirect('sms/send-sms-phone-number')->with([
                'message' => language_data('SMS added in queue and will deliver one by one'),
            ]);
        } else {
            return redirect('sms/send-sms-phone-number')->with([
                'message'           => language_data('Client Group not found'),
                'message_important' => true,
            ]);
        }
    }
}
