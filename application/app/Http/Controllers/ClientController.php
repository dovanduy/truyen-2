<?php

namespace App\Http\Controllers;

use App\Classes\Permission;
use App\Client;
use App\ClientGroups;
use App\CustomSMSGateways;
use App\EmailTemplates;
use App\Invoices;
use App\Jobs\SendBulkSMS;
use App\ScheduleSMS;
use App\SenderIdManage;
use App\SMSGateways;
use App\SMSHistory;
use App\SMSTransaction;
use App\SupportTickets;
use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // allClients Function Start Here
    //======================================================================
    public function allClients()
    {
        $self = 'all-clients';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $clients = Client::all();
        return view('admin.all-clients', compact('clients'));
    }

    //======================================================================
    // addClient Function Start Here
    //======================================================================
    public function addClient()
    {
        $self = 'add-new-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroups = ClientGroups::where('status', 'Yes')->where('created_by', '0')->get();
        $sms_gateways = SMSGateways::where('status', 'Active')->get();

        return view('admin.add-client', compact('clientGroups', 'sms_gateways'));
    }

    //======================================================================
    // addClientPost Function Start Here
    //======================================================================
    public function addClientPost(Request $request)
    {
        $self = 'add-new-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'password' => 'required', 'cpassword' => 'required', 'phone' => 'required', 'country' => 'required', 'api_access' => 'required', 'client_group' => 'required', 'sms_gateway' => 'required', 'sms_limit' => 'required', 'reseller_panel' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/add')->withErrors($v->errors());
        }
        $exist_user_name = Client::where('username', $request->user_name)->first();
        $exist_user_email = Client::where('email', $request->email)->first();

        if ($exist_user_name) {
            return redirect('clients/add')->with([
                'message' => language_data('User name already exist'),
                'message_important' => true
            ]);
        }

        if ($exist_user_email) {
            return redirect('clients/add')->with([
                'message' => language_data('Email already exist'),
                'message_important' => true
            ]);
        }

        $password = $request->password;
        $cpassword = $request->cpassword;

        if ($password !== $cpassword) {
            return redirect('clients/add')->with([
                'message' => language_data('Both password does not match'),
                'message_important' => true
            ]);
        } else {
            $password = bcrypt($password);
        }

        $image = $request->image;
        if ($image != '') {
            $destinationPath = public_path() . '/assets/client_pic/';
            $image_name = $image->getClientOriginalName();
            Input::file('image')->move($destinationPath, $image_name);
        } else {
            $image_name = 'profile.jpg';
        }

//        if (app_config('registration_verification') == '1') {
//            $status = 'Inactive';
//        } else {
//            $status = 'Active';
//        }

        $email_notify = $request->email_notify;
        if ($email_notify == 'yes') {
            $email_notify = 'Yes';
        } else {
            $email_notify = 'No';
        }

        $email = $request->email;

        $api_key_generate = $request->user_name . ':' . $password;
        $client = new Client();
        $client->groupid = $request->client_group;
        $client->parent = '0';
        $client->fname = $request->first_name;
        $client->lname = $request->last_name;
        $client->company = $request->company;
        $client->website = $request->website;
        $client->email = $email;
        $client->username = $request->user_name;
        $client->password = $password;
        $client->address1 = $request->address;
        $client->address2 = $request->more_address;
        $client->state = $request->state;
        $client->city = $request->city;
        $client->postcode = $request->postcode;
        $client->country = $request->country;
        $client->phone = $request->phone;
        $client->image = $image_name;
        $client->datecreated = date('Y-m-d');
        $client->sms_limit = $request->sms_limit;
        $client->api_access = $request->api_access;
        $client->api_key = base64_encode($api_key_generate);
        $client->status = 'Active';
        $client->reseller = $request->reseller_panel;
        $client->sms_gateway = $request->sms_gateway;
        $client->emailnotify = $email_notify;
        $client->save();
        $client_id = $client->id;

        /*For Email Confirmation*/
        if (is_int($client_id) && $email_notify == 'Yes' && $email != '') {

            $conf = EmailTemplates::where('tplname', '=', 'Client SignUp')->first();

            $estatus = $conf->status;

            if ($estatus == '1') {

                $sysEmail = app_config('Email');
                $sysCompany = app_config('AppName');
                $sysUrl = url('/');

                $template = $conf->message;
                $subject = $conf->subject;
                $client_name = $request->first_name . ' ' . $request->last_name;
                $data = array(
                    'name' => $client_name,
                    'business_name' => $sysCompany,
                    'from' => $sysEmail,
                    'username' => $request->user_name,
                    'email' => $email,
                    'password' => $cpassword,
                    'sys_url' => $sysUrl,
                    'template' => $template
                );

                $message = _render($template, $data);
                $mail_subject = _render($subject, $data);
                $body = $message;

                /*Set Authentication*/

                $default_gt = app_config('Gateway');

                if ($default_gt == 'default') {

                    $mail = new \PHPMailer();

                    try {
                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $client_name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;

                        if (!$mail->send()) {
                            return redirect('clients/all')->with([
                                'message' => language_data('Client Added Successfully But Email Not Send')
                            ]);
                        } else {
                            return redirect('clients/all')->with([
                                'message' => language_data('Client Added Successfully')
                            ]);
                        }

                    } catch (\phpmailerException $e) {
                        return redirect('clients/add')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }

                } else {
                    $host = app_config('SMTPHostName');
                    $smtp_username = app_config('SMTPUserName');
                    $stmp_password = app_config('SMTPPassword');
                    $port = app_config('SMTPPort');
                    $secure = app_config('SMTPSecure');


                    $mail = new \PHPMailer();

                    try {

                        $mail->isSMTP();                                      // Set mailer to use SMTP
                        $mail->Host = $host;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true;                               // Enable SMTP authentication
                        $mail->Username = $smtp_username;                 // SMTP username
                        $mail->Password = $stmp_password;                           // SMTP password
                        $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                        $mail->Port = $port;

                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $client_name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;


                        if (!$mail->send()) {
                            return redirect('clients/all')->with([
                                'message' => language_data('Client Added Successfully But Email Not Send')
                            ]);
                        } else {
                            return redirect('clients/all')->with([
                                'message' => language_data('Client Added Successfully')
                            ]);
                        }

                    } catch (\phpmailerException $e) {
                        return redirect('clients/add')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }
                }
            }
        }

        return redirect('clients/all')->with([
            'message' => language_data('Client Added Successfully')
        ]);

    }

    //======================================================================
    // viewClient Function Start Here
    //======================================================================
    public function viewClient($id)
    {

        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client = Client::find($id);
        if ($client) {
            $invoices = Invoices::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $tickets = SupportTickets::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $sms_gateways = SMSGateways::where('status', 'Active')->get();
            $sms_transaction = SMSTransaction::where('cl_id', $id)->orderBy('id', 'ASC')->get();
            $clientGroups = ClientGroups::where('status', 'Yes')->where('created_by', '0')->get();
            return view('admin.client-manage', compact('client', 'invoices', 'tickets', 'sms_gateways', 'sms_transaction', 'clientGroups'));
        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // updateLimit Function Start Here
    //======================================================================
    public function updateLimit(Request $request)
    {
        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = Input::get('cmd');
        $v = \Validator::make($request->all(), [
            'sms_amount' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client) {

            SMSTransaction::create([
                'cl_id' => $cmd,
                'amount' => $request->sms_amount
            ]);

            $client->sms_limit += $request->sms_amount;
            $client->save();
        }

        return redirect('clients/view/' . $cmd)->with([
            'message' => language_data('Limit updated successfully')
        ]);
    }

    //======================================================================
    // updateImage Function Start Here
    //======================================================================
    public function updateImage(Request $request)
    {
        $cmd = Input::get('cmd');
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        $v = \Validator::make($request->all(), [
            'client_image' => 'required | mimes:jpeg,jpg,png',
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client) {
            $image = $request->client_image;
            if ($image != '') {
                $destinationPath = public_path() . '/assets/client_pic/';
                $image_name = $image->getClientOriginalName();
                Input::file('client_image')->move($destinationPath, $image_name);

                $client->image = $image_name;
                $client->save();

                return redirect('clients/view/' . $cmd)->with([
                    'message' => language_data('Image updated successfully')
                ]);
            } else {
                return redirect('clients/view/' . $cmd)->with([
                    'message' => language_data('Please try again'),
                    'message_important' => true
                ]);
            }
        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // updateClient Function Start Here
    //======================================================================
    public function updateClient(Request $request)
    {
        $cmd = Input::get('cmd');
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'phone' => 'required', 'country' => 'required', 'api_access' => 'required', 'client_group' => 'required', 'status' => 'required', 'reseller_panel' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client) {
            if ($client->username != $request->user_name) {
                $exist_user_name = Client::where('username', $request->user_name)->first();
                if ($exist_user_name) {
                    return redirect('clients/view/' . $cmd)->with([
                        'message' => language_data('User Name already exist'),
                        'message_important' => true
                    ]);
                }
            }

            if ($client->email != $request->email) {

                $exist_user_email = Client::where('email', $request->email)->first();
                if ($exist_user_email) {
                    return redirect('clients/view/' . $cmd)->with([
                        'message' => language_data('Email already exist'),
                        'message_important' => true
                    ]);
                }
            }

            $password = $request->password;

            if ($password != '') {
                $password = bcrypt($password);
            } else {
                $password = $client->password;
            }

            $client->groupid = $request->client_group;
            $client->fname = $request->first_name;
            $client->lname = $request->last_name;
            $client->company = $request->company;
            $client->website = $request->website;
            $client->email = $request->email;
            $client->username = $request->user_name;
            $client->password = $password;
            $client->address1 = $request->address;
            $client->address2 = $request->more_address;
            $client->state = $request->state;
            $client->city = $request->city;
            $client->postcode = $request->postcode;
            $client->country = $request->country;
            $client->phone = $request->phone;
            $client->api_access = $request->api_access;
            $client->status = $request->status;
            $client->reseller = $request->reseller_panel;
            $client->sms_gateway = $request->sms_gateway;
            $client->save();

            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('Client updated successfully')
            ]);
        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // sendSMS Function Start Here
    //======================================================================
    public function sendSMS(Request $request)
    {
        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }
        $cmd = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'message' => 'required', 'sms_gateway' => 'required',
        ]);

        if ($v->fails()) {
            return redirect('clients/view/' . $cmd)->withErrors($v->errors());
        }

        $client = Client::find($cmd);
        if ($client == '') {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }

        $gateway = SMSGateways::find($request->sms_gateway);
        if ($gateway->status != 'Active') {
            return redirect('clients/view/' . $cmd)->with([
                'message' => language_data('SMS gateway not active'),
                'message_important' => true
            ]);
        }

        if ($gateway->custom == 'Yes') {
            $cg_info = CustomSMSGateways::where('gateway_id', $request->sms_gateway)->first();
        } else {
            $cg_info = '';
        }

        $message = $request->message;


        $msgcount = strlen($message);
        $msgcount = $msgcount / 160;
        $msgcount = ceil($msgcount);
        $sender_id = $request->sender_id;

        $this->dispatch(new SendBulkSMS('0', $client->phone, $gateway, $sender_id, $message, $msgcount, $cg_info));

        return redirect('clients/view/' . $cmd)->with([
            'message' => language_data('Please check sms history')
        ]);
    }



    //======================================================================
    // exportImport Function Start Here
    //======================================================================
    public function exportImport()
    {
        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $client_groups = ClientGroups::where('status', 'Yes')->where('created_by', '0')->get();
        $sms_gateways = SMSGateways::where('status', 'Active')->get();
        return view('admin.export-n-import', compact('client_groups', 'sms_gateways'));
    }


    //======================================================================
    // exportClients Function Start Here
    //======================================================================
    public function exportClients()
    {
        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        Excel::create('Clients', function ($excel) {
            $excel->sheet('Sheetname', function ($sheet) {
                $sheet->fromModel(Client::all());
            });

        })->export('csv');
    }

    //======================================================================
    // downloadSampleCSV Function Start Here
    //======================================================================
    public function downloadSampleCSV()
    {
        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        return response()->download('assets/test_file/test_file.csv');
    }

    /**
     * @param $array
     * @param $key
     * @return array
     */
    public function checkUsername($array, $key, $client_group = '0', $sms_gateway = '1')
    {
        $client_data = [];
        $i = 0;
        $key_array = [];
        $keep_ids = [];
        $final_data = [];

        foreach ($array as $k => $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $client_data[$i] = $val;
                if (!array_key_exists('id', $client_data[$k])) {
                    $client_data[$k]['password'] = bcrypt($val['password']);
                    $client_data[$k]['groupid'] = $client_group;
                    $client_data[$k]['sms_gateway'] = $sms_gateway;
                    $client_data[$k]['created_at'] = DateTime::dateTime();
                    $client_data[$k]['updated_at'] = DateTime::dateTime();
                    array_push($keep_ids, $k);
                }
            }
            $i++;
        }
        foreach ($keep_ids as $v) {
            if (array_key_exists($v, $client_data)) {
                array_push($final_data, $client_data[$v]);
            }
        }

        return $final_data;
    }

    /**
     * @param $array
     * @return mixed
     */
    public function prepareForInsert($array)
    {
        foreach ($array as $k => $v) {
            if (!array_key_exists('id', $v)) {
                $array[$k] ['fname'] = $array[$k] ['first_name'];
                $array[$k] ['lname'] = $array[$k] ['last_name'];
                $array[$k] ['address1'] = $array[$k] ['address'];
                $array[$k] ['address2'] = $array[$k] ['more_address'];
                $array[$k] ['postcode'] = $array[$k] ['zip_code'];
                unset($array[$k]['first_name']);
                unset($array[$k]['last_name']);
                unset($array[$k]['address']);
                unset($array[$k]['more_address']);
                unset($array[$k]['zip_code']);
            }
        }
        $array = array_map('array_filter', $array);
        return $array;
    }


    //======================================================================
    // addNewClientCSV Function Start Here
    //======================================================================
    public function addNewClientCSV(Request $request)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/export-n-import')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'export-n-import-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'import_client' => 'required', 'client_group' => 'required', 'sms_gateway' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/export-n-import')->withErrors($v->errors());
        }

        $file_extension = Input::file('import_client')->getClientOriginalExtension();

        $supportedExt = array('csv', 'xls', 'xlsx');

        if (!in_array_r($file_extension, $supportedExt)) {
            return redirect('clients/export-n-import')->with([
                'message' => language_data('Insert Valid Excel or CSV file'),
                'message_important' => true
            ]);
        }

        $client_group = Input::get('client_group');

        $existing_clients = Client::all()->toArray();
        $results = Excel::load($request->import_client)->get()->toArray();
        $client_data = array_merge($existing_clients, $results);


        $details = $this->checkUsername($client_data, 'username', $client_group, $request->sms_gateway);
        $final_data = $this->prepareForInsert($details);

        Client::insert($final_data);

        return redirect('clients/all')->with([
            'message' => language_data('Client imported successfully')
        ]);
    }



    //======================================================================
    // clientGroups Function Start Here
    //======================================================================
    public function clientGroups()
    {
        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroups = ClientGroups::all();
        return view('admin.client-groups', compact('clientGroups'));
    }

    //======================================================================
    // addNewClientGroup Function Start Here
    //======================================================================
    public function addNewClientGroup(Request $request)
    {
        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), [
            'group_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/groups')->withErrors($v->errors());
        }

        $clientGroup = new ClientGroups();
        $clientGroup->group_name = $request->group_name;
        $clientGroup->created_by = '0';
        $clientGroup->status = $request->status;
        $clientGroup->save();

        return redirect('clients/groups')->with([
            'message' => language_data('Client Group added successfully')
        ]);

    }

    //======================================================================
    // updateClientGroup Function Start Here
    //======================================================================
    public function updateClientGroup(Request $request)
    {

        $self = 'edit-client-group';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $cmd = $request->cmd;

        $v = \Validator::make($request->all(), [
            'group_name' => 'required', 'status' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('clients/groups')->withErrors($v->errors());
        }

        $clientGroup = ClientGroups::find($cmd);

        if ($clientGroup) {
            $clientGroup->group_name = $request->group_name;
            $clientGroup->status = $request->status;
            $clientGroup->save();

            return redirect('clients/groups')->with([
                'message' => language_data('Client Group updated successfully')
            ]);

        } else {
            return redirect('clients/groups')->with([
                'message' => language_data('Client Group not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // exportClientGroup Function Start Here
    //======================================================================
    public function exportClientGroup($id)
    {
        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        Excel::create('Clients', function ($excel) use ($id) {
            $excel->sheet('Sheetname', function ($sheet) use ($id) {
                $sheet->fromModel(Client::where('groupid', $id)->get());
            });

        })->export('csv');
    }


    //======================================================================
    // deleteClientGroup Function Start Here
    //======================================================================
    public function deleteClientGroup($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('clients/groups')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'client-groups';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clientGroup = ClientGroups::find($id);

        if ($clientGroup) {
            $exist_client = Client::where('groupid', $id)->first();
            if ($exist_client) {
                return redirect('clients/groups')->with([
                    'message' => language_data('This Group exist in a client'),
                    'message_important' => true
                ]);
            }

            $clientGroup->delete();

            return redirect('clients/groups')->with([
                'message' => language_data('Client group deleted successfully')
            ]);

        } else {
            return redirect('clients/groups')->with([
                'message' => language_data('Client Group not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // deleteClient Function Start Here
    //======================================================================
    public function deleteClient($id)
    {

        //Check Permission
        $self = 'manage-client';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }


        //check client info
        $client = Client::find($id);
        if ($client) {

            //Check Client Group
            $client_group= ClientGroups::where('created_by',$id)->first();

            if ($client_group){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('Client contain in').' '.language_data('Client Group'),
                    'message_important' => true
                ]);
            }


            //Check Invoice
            $client_inv=Invoices::where('cl_id',$id)->first();

            if ($client_inv){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('Client contain in').' '.language_data('Invoice'),
                    'message_important' => true
                ]);
            }


            //Check SMS History
            $client_sms=SMSHistory::where('userid',$id)->first();

            if ($client_sms){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('Client contain in').' '.language_data('SMS History'),
                    'message_important' => true
                ]);
            }

            //Check Schedule SMS History
//            $client_schedule_sms=ScheduleSMS::where('userid',$id)->first();
//
//            if ($client_schedule_sms){
//                return redirect('clients/view/'.$id)->with([
//                    'message' => language_data('Client contain in').' '.language_data('SMS History')
//                ]);
//            }

            //Check Sender ID
            $client_sender_id=SenderIdManage::where('cl_id',$id)->first();

            if ($client_sender_id){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('Client contain in').' '.language_data('Sender ID'),
                    'message_important' => true
                ]);
            }

            //Check Client SMS Balance
            if ($client->sms_limit>0){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('Client sms limit not empty'),
                    'message_important' => true
                ]);
            }


            //Check Parent
            $parents=Client::where('parent',$id)->first();

            if ($parents){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('This client have some customer'),
                    'message_important' => true
                ]);
            }

            //Check Support Tickets
            $client_tickets=SupportTickets::where('cl_id',$id)->first();

            if ($client_tickets){
                return redirect('clients/view/'.$id)->with([
                    'message' => language_data('Client contain in').' '.language_data('Support Tickets'),
                    'message_important' => true
                ]);
            }

            // Delete Schedule SMS N SMS Transaction
            ScheduleSMS::where('userid',$id)->delete();
            SMSTransaction::where('cl_id',$id)->delete();

            //Delete Client
            $client->delete();

            return redirect('clients/all')->with([
                'message' => language_data('Client delete successfully')
            ]);

        } else {
            return redirect('clients/all')->with([
                'message' => language_data('Client info not found'),
                'message_important' => true
            ]);
        }



    }


}
