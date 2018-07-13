<?php

namespace App\Http\Controllers;

use App\Client;
use App\Invoices;
use App\SMSHistory;
use App\SMSInbox;
use App\SupportTickets;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class ClientDashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('client');
    }


    //======================================================================
    // dashboard Function Start Here
    //======================================================================
    public function dashboard()
    {

        //For Invoice chart

        $inv_unpaid=Invoices::where('status','Unpaid')->where('cl_id',Auth::guard('client')->user()->id)->count();
        $inv_paid=Invoices::where('status','Paid')->where('cl_id',Auth::guard('client')->user()->id)->count();
        $inv_cancelled=Invoices::where('status','Cancelled')->where('cl_id',Auth::guard('client')->user()->id)->count();
        $inv_partially_paid=Invoices::where('status','Partially Paid')->where('cl_id',Auth::guard('client')->user()->id)->count();

        $invoices_json = app()->chartjs
            ->name('invoiceChart')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Unpaid', 'Paid', 'Cancelled', 'Partially Paid'])
            ->datasets([
                [
                    'backgroundColor' => ['#F0AD4E', '#30DDBC','#D9534F','#5BC0DE'],
                    'hoverBackgroundColor' => ['#F0AD4E', '#30DDBC','#D9534F','#5BC0DE'],
                    'data' => [$inv_unpaid, $inv_paid,$inv_cancelled,$inv_partially_paid]
                ]
            ])
            ->options([
                'legend'=>['display'=>false]
            ]);



        //For Support Ticket Chart

        $st_pending=SupportTickets::where('status','Pending')->where('cl_id',Auth::guard('client')->user()->id)->count();
        $st_answered=SupportTickets::where('status','Answered')->where('cl_id',Auth::guard('client')->user()->id)->count();
        $st_replied=SupportTickets::where('status','Customer Reply')->where('cl_id',Auth::guard('client')->user()->id)->count();
        $st_closed=SupportTickets::where('status','Closed')->where('cl_id',Auth::guard('client')->user()->id)->count();


        $tickets_json = app()->chartjs
            ->name('supportTicketChart')
            ->type('doughnut')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Pending', 'Answered', 'Customer Reply', 'Closed'])
            ->datasets([
                [
                    'backgroundColor' => ['#d9534f', '#30DDBC','#5bc0de','#7E57C2'],
                    'hoverBackgroundColor' => ['#d9534f', '#30DDBC','#5bc0de','#7E57C2'],
                    'data' => [$st_pending, $st_answered,$st_replied,$st_closed]
                ]
            ])
            ->options([
                'legend'=>['display'=>false]
            ]);



        //For SMS Status Chart


        $sms_history=SMSHistory::where('userid',Auth::guard('client')->user()->id)->select('id')->get();
        $client_ids=[];

        foreach ($sms_history as $sh){
            array_push($client_ids,$sh->id);
        }

        $sms_count=SMSInbox::whereIn('msg_id',$client_ids)->count();
        $sms_success=SMSInbox::whereIn('msg_id',$client_ids)->where('status','like','%Success%')->count();
        $sms_failed=$sms_count-$sms_success;

        $sms_status_json = app()->chartjs
            ->name('smsStatusChat')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Success', 'Failed'])
            ->datasets([
                [
                    'backgroundColor' => ['#30DDBC', '#F95F5B'],
                    'hoverBackgroundColor' => ['#30DDBC', '#F95F5B'],
                    'data' => [$sms_success, $sms_failed]
                ]
            ])
            ->options([
                'legend'=>['display'=>false]
            ]);


        //For SMS History Chart
        /*$day_10=get_date_format(Carbon::now(app_config('Timezone'))->subDays(9));
        $day_9=get_date_format(Carbon::now(app_config('Timezone'))->subDays(8));
        $day_8=get_date_format(Carbon::now(app_config('Timezone'))->subDays(7));
        $day_7=get_date_format(Carbon::now(app_config('Timezone'))->subDays(6));
        $day_6=get_date_format(Carbon::now(app_config('Timezone'))->subDays(5));
        $day_5=get_date_format(Carbon::now(app_config('Timezone'))->subDays(4));
        $day_4=get_date_format(Carbon::now(app_config('Timezone'))->subDays(3));
        $day_3=get_date_format(Carbon::now(app_config('Timezone'))->subDays(2));
        $day_2=get_date_format(Carbon::now(app_config('Timezone'))->subDays(1));
        $day_1=get_date_format(Carbon::now(app_config('Timezone')));*/
        $date   = date('Y-m-d');
        $day_10 = date("Y-m-d", strtotime($date . " -9 day"));
        $day_9  = date("Y-m-d", strtotime($date . " -8 day"));
        $day_8  = date("Y-m-d", strtotime($date . " -7 day"));
        $day_7  = date("Y-m-d", strtotime($date . " -6 day"));
        $day_6  = date("Y-m-d", strtotime($date . " -5 day"));
        $day_5  = date("Y-m-d", strtotime($date . " -4 day"));
        $day_4  = date("Y-m-d", strtotime($date . " -3 day"));
        $day_3  = date("Y-m-d", strtotime($date . " -2 day"));
        $day_2  = date("Y-m-d", strtotime($date . " -1 day"));
        $day_1  = $date;

        $day_10_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_10))->count();
        $day_10_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_10))->count();

        $day_9_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_9))->count();
        $day_9_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_9))->count();

        $day_8_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_8))->count();
        $day_8_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_8))->count();

        $day_7_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_7))->count();
        $day_7_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_7))->count();

        $day_6_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_6))->count();
        $day_6_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_6))->count();

        $day_5_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_5))->count();
        $day_5_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_5))->count();

        $day_4_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_4))->count();
        $day_4_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_4))->count();

        $day_3_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_3))->count();
        $day_3_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_3))->count();

        $day_2_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_2))->count();
        $day_2_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_2))->count();

        $day_1_count_inbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','receiver')->whereDay('created_at',get_mysql_date($day_1))->count();
        $day_1_count_outbound=SMSInbox::whereIn('msg_id',$client_ids)->where('send_by','sender')->whereDay('created_at',get_mysql_date($day_1))->count();
        $day_10 = date('d-m-Y', strtotime($day_10));
        $day_9  = date('d-m-Y', strtotime($day_9));
        $day_8  = date('d-m-Y', strtotime($day_8));
        $day_7  = date('d-m-Y', strtotime($day_7));
        $day_6  = date('d-m-Y', strtotime($day_6));
        $day_5  = date('d-m-Y', strtotime($day_5));
        $day_4  = date('d-m-Y', strtotime($day_4));
        $day_3  = date('d-m-Y', strtotime($day_3));
        $day_2  = date('d-m-Y', strtotime($day_2));
        $day_1  = date('d-m-Y', strtotime($day_1));

        $sms_history= app()->chartjs
            ->name('smsHistoryChart')
            ->type('line')
            ->size(['width' => 200, 'height' => 50])
            ->labels([$day_10,$day_9, $day_8, $day_7, $day_6, $day_5, $day_4,$day_3,$day_2,$day_1])
            ->datasets([
                [
                    "label" => "Outbound",
                    'backgroundColor' => "rgba(0, 51, 102, 0.5)",
                    'borderColor' => "rgba(0, 51, 102, 0.8)",
                    "pointBorderColor" => "rgba(0, 51, 102, 0.8)",
                    "pointBackgroundColor" => "rgba(0, 51, 102, 0.8)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    'data' => [$day_10_count_outbound,$day_9_count_outbound , $day_8_count_outbound, $day_7_count_outbound, $day_6_count_outbound, $day_5_count_outbound, $day_4_count_outbound, $day_3_count_outbound, $day_2_count_outbound, $day_1_count_outbound],
                ],
                [
                    "label" => "Inbound",
                    'backgroundColor' => "rgba(233, 114, 76, 0.5)",
                    'borderColor' => "rgba(233, 114, 76, 0.8)",
                    "pointBorderColor" => "rgba(233, 114, 76, 0.8)",
                    "pointBackgroundColor" => "rgba(233, 114, 76, 0.8)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    'data' => [$day_10_count_inbound,$day_9_count_inbound , $day_8_count_inbound, $day_7_count_inbound, $day_6_count_inbound, $day_5_count_inbound, $day_4_count_inbound, $day_3_count_inbound, $day_2_count_inbound, $day_1_count_inbound],
                ]
            ])
            ->options([
                'legend'=>['display'=>false]
            ]);

        $recent_five_invoices=Invoices::orderBy('id','desc')->where('cl_id',Auth::guard('client')->user()->id)->take(5)->get();
        $recent_five_tickets=SupportTickets::orderBy('id','desc')->where('cl_id',Auth::guard('client')->user()->id)->take(5)->get();

        return view('client.dashboard',compact('invoices_json','sms_history','tickets_json','sms_status_json','recent_five_invoices','recent_five_tickets'));
    }

    //======================================================================
    // menuOpenStatus Function Start Here
    //======================================================================
    public function menuOpenStatus()
    {
        $client = Client::find(Auth::guard('client')->user()->id);
        if ($client->menu_open == 0) {
            $client->menu_open = '1';
        } else {
            $client->menu_open = '0';
        }
        $client->save();
    }

    //======================================================================
    // logout Function Start Here
    //======================================================================
    public function logout()
    {
        Auth::guard('client')->logout();
        return redirect('/')->with([
            'message' => 'Logout Successfully'
        ]);

    }



    //======================================================================
    // editProfile Function Start Here
    //======================================================================
    public function editProfile()
    {
        $client = client_info(Auth::guard('client')->user()->id);
        return view('client.edit-profile', compact('client'));
    }


    /* postPersonalInfo  Function Start Here */
    public function postPersonalInfo(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/edit-profile')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'phone' => 'required', 'country' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('user/edit-profile')->withErrors($v->errors());
        }

        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client->email != $request->email) {
            $exist_user_email = Client::where('email', $request->email)->first();
            if ($exist_user_email) {
                return redirect('user/edit-profile')->with([
                    'message' => language_data('Email already exist'),
                    'message_important' => true
                ]);
            }
        }

        $client->fname = $request->first_name;
        $client->lname = $request->last_name;
        $client->company = $request->company;
        $client->website = $request->website;
        $client->email = $request->email;
        $client->address1 = $request->address;
        $client->address2 = $request->more_address;
        $client->state = $request->state;
        $client->city = $request->city;
        $client->postcode = $request->postcode;
        $client->country = $request->country;
        $client->phone = $request->phone;
        $client->save();

        return redirect('user/edit-profile')->with([
            'message' => language_data('Profile Updated Successfully')
        ]);

    }

//======================================================================
// updateAvatar Function Start Here
//======================================================================
    public function updateAvatar(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/edit-profile')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'image' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/edit-profile')->withErrors($v->errors());
        }

        $image = Input::file('image');
        $client = Client::find(Auth::guard('client')->user()->id);

        if ($client) {
            if ($image != '') {
                $destinationPath = public_path() . '/assets/client_pic/';
                $image_name = $image->getClientOriginalName();
                Input::file('image')->move($destinationPath, $image_name);

                $client->image = $image_name;
                $client->save();

                return redirect('user/edit-profile')->with([
                    'message' => language_data('Image updated successfully')
                ]);

            } else {
                return redirect('user/edit-profile')->with([
                    'message' => language_data('Upload an Image'),
                    'message_important' => true
                ]);
            }
        } else {
            return $this->logout();
        }
    }

    /* changePassword  Function Start Here */
    public function changePassword()
    {
        return view('client.change-password');
    }

//======================================================================
// updatePassword Function Start Here
//======================================================================
    public function updatePassword(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('user/change-password')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'current_password' => 'required', 'new_password' => 'required', 'confirm_password' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/change-password')->withErrors($v->errors());
        }

        $user = Client::find(Auth::guard('client')->user()->id);

        $current_password = Input::get('current_password');
        $new_password = Input::get('new_password');
        $confirm_password = Input::get('confirm_password');

        if (Hash::check($current_password, $user->password)) {

            if ($new_password == $confirm_password) {
                $user->password = bcrypt($new_password);
                $user->save();

                return redirect('user/change-password')->with([
                    'message' => language_data('Password Change Successfully')
                ]);

            } else {
                return redirect('user/change-password')->with([
                    'message' => language_data('Both password does not match'),
                    'message_important' => true
                ]);
            }

        } else {
            return redirect('user/change-password')->with([
                'message' => language_data('Current Password Does Not Match'),
                'message_important' => true
            ]);
        }
    }


}
