<?php

namespace App\Http\Controllers;

use App\Admin;
use App\AppConfig;
use App\Client;
use App\EmailTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use ReCaptcha\ReCaptcha;

class AuthController extends Controller
{
    //======================================================================
    // clientLogin Function Start Here
    //======================================================================
    public function clientLogin()
    {
        if (!env('DB_DATABASE')) {
            return redirect('install');
        }

        if (Auth::guard('client')->check()) {
            return redirect('dashboard');
        }

        return view('client.login');
    }

    //======================================================================
    // clientGetLogin Function Start Here
    //======================================================================
    public function clientGetLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required', 'password' => 'required'
        ]);

        $check_input = $request->only('username', 'password');
        $remember = (Input::has('remember')) ? true : false;


        if (app_config('captcha_in_client') == '1') {
            if (isset($_POST['g-recaptcha-response'])) {
                $getCaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptcha = new ReCaptcha(app_config('captcha_secret_key'));
                $resp = $recaptcha->verify($getCaptchaResponse);

                if (!$resp->isSuccess()) {
                    if (array_key_exists('0', $resp->getErrorCodes())) {
                        $error_msg = $resp->getErrorCodes()[0];
                    } else {
                        $error_msg = language_data('Invalid Captcha');
                    }

                    return redirect('/')->with([
                        'message' => $error_msg,
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('/')->with([
                    'message' => language_data('Invalid Captcha'),
                    'message_important' => true
                ]);
            }
        }

        if (Auth::guard('client')->attempt($check_input, $remember)) {
            return redirect()->intended('dashboard');
        } else {
            return redirect('/')->withInput($request->only('username'))->withErrors([
                'username' => language_data('Invalid User name or Password')
            ]);
        }
    }



    //======================================================================
    // clientRegistrationVerification Function Start Here
    //======================================================================
    public function clientRegistrationVerification()
    {
        return view('client.user-verification');
    }


    //======================================================================
    // postVerificationToken Function Start Here
    //======================================================================
    public function postVerificationToken(Request $request)
    {
        $cmd = Input::get('cmd');

        if ($cmd == '') {
            return redirect('/')->with([
                'message' => language_data('Invalid Request'),
                'message_important' => true
            ]);
        }


        $ef = Client::find($cmd);

        if ($ef) {

            $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');

            $name = $ef->fname . ' ' . $ef->lname;
            $email = $ef->email;
            /*For Email Confirmation*/

            $conf = EmailTemplates::where('tplname', '=', 'Client Registration Verification')->first();

            $estatus = $conf->status;
            if ($estatus == '1') {


                $ef->pwresetkey = $fprand;
                $ef->save();

                $sysEmail = app_config('Email');
                $sysCompany = app_config('AppName');
                $fpw_link = url('/verify-user/' . $fprand);

                $template = $conf->message;
                $subject = $conf->subject;

                $data = array('name' => $name,
                    'business_name' => $sysCompany,
                    'template' => $template,
                    'sys_url' => $fpw_link
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
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;
                        if (!$mail->send()) {
                            return redirect('user/registration-verification')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('user/registration-verification')->with([
                                'message' => language_data('Verification code send successfully. Please check your email')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('user/registration-verification')->with([
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
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;

                        if (!$mail->send()) {
                            return redirect('user/registration-verification')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('user/registration-verification')->with([
                                'message' => language_data('Verification code send successfully. Please check your email')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('user/registration-verification')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }

                }

            } else {
                return redirect('user/registration-verification')->with([
                    'message' => language_data('Something wrong, Please contact with your provider')
                ]);
            }

        } else {
            return redirect('/')->with([
                'message' => language_data('Invalid Request'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // verifyUserAccount Function Start Here
    //======================================================================
    public function verifyUserAccount($token)
    {


        $tfnd = Client::where('pwresetkey', '=', $token)->count();

        if ($tfnd == '1') {
            $d = Client::where('pwresetkey', '=', $token)->first();
            $d->status = 'Active';
            $d->pwresetkey = '';
            $d->save();

            return redirect()->intended('dashboard');

        } else {
            return redirect('/')->with([
                'message' => language_data('Verification code not found'),
                'message_important' => true
            ]);
        }

    }


    //======================================================================
    // forgotUserPassword Function Start Here
    //======================================================================
    public function forgotUserPassword()
    {
        return view('client.forgot-password');
    }



    //======================================================================
    // clientSignUp Function Start Here
    //======================================================================
    public function clientSignUp()
    {
        if (app_config('client_registration') != '1') {
            return redirect('/')->with([
                'message' => language_data('Invalid Request'),
                'message_important' => true
            ]);
        }

        return view('client.registration');

    }

    //======================================================================
    // postUserRegistration Function Start Here
    //======================================================================
    public function postUserRegistration(Request $request)
    {

        $v = \Validator::make($request->all(), [
            'first_name' => 'required', 'user_name' => 'required', 'email' => 'required', 'password' => 'required', 'cpassword' => 'required', 'phone' => 'required', 'country' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('signup')->withErrors($v->errors());
        }


        if (app_config('captcha_in_client_registration') == '1') {
            if (isset($_POST['g-recaptcha-response'])) {
                $getCaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptcha = new ReCaptcha(app_config('captcha_secret_key'));
                $resp = $recaptcha->verify($getCaptchaResponse);

                if (!$resp->isSuccess()) {
                    if (array_key_exists('0', $resp->getErrorCodes())) {
                        $error_msg = $resp->getErrorCodes()[0];
                    } else {
                        $error_msg = language_data('Invalid Captcha');
                    }

                    return redirect('signup')->with([
                        'message' => $error_msg,
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('signup')->with([
                    'message' => language_data('Invalid Captcha'),
                    'message_important' => true
                ]);
            }
        }

        $exist_user_name = Client::where('username', $request->user_name)->first();
        $exist_user_email = Client::where('email', $request->email)->first();

        if ($exist_user_name) {
            return redirect('signup')->with([
                'message' => language_data('User name already exist'),
                'message_important' => true
            ]);
        }

        if ($exist_user_email) {
            return redirect('signup')->with([
                'message' => language_data('Email already exist'),
                'message_important' => true
            ]);
        }

        $password = $request->password;
        $cpassword = $request->cpassword;

        if ($password !== $cpassword) {
            return redirect('signup')->with([
                'message' => language_data('Both password does not match'),
                'message_important' => true
            ]);
        } else {
            $password = bcrypt($password);
        }

        if (app_config('registration_verification') == '1') {
            $status = 'Inactive';
        } else {
            $status = 'Active';
        }

        $email_notify = $request->email_notify;
        if ($email_notify == 'yes') {
            $email_notify = 'Yes';
        } else {
            $email_notify = 'No';
        }

        $email = $request->email;

        $api_key_generate = $request->user_name . ':' . $password;
        $client = new Client();
        $client->parent = '0';
        $client->fname = $request->first_name;
        $client->lname = $request->last_name;
        $client->email = $email;
        $client->username = $request->user_name;
        $client->password = $password;
        $client->country = $request->country;
        $client->phone = $request->phone;
        $client->image = 'profile.jpg';
        $client->datecreated = date('Y-m-d');
        $client->sms_limit = '0';
        $client->api_access = 'No';
        $client->api_key = base64_encode($api_key_generate);
        $client->status = $status;
        $client->reseller = 'No';
        $client->sms_gateway = app_config('sms_api_gateway');
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
                            return redirect('/')->with([
                                'message' => language_data('Registration Successful')
                            ]);
                        } else {
                            return redirect('/')->with([
                                'message' => language_data('Registration Successful')
                            ]);
                        }

                    } catch (\phpmailerException $e) {
                        return redirect('signup')->with([
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
                            return redirect('/')->with([
                                'message' => language_data('Registration Successful')
                            ]);
                        } else {
                            return redirect('/')->with([
                                'message' => language_data('Registration Successful')
                            ]);
                        }

                    } catch (\phpmailerException $e) {
                        return redirect('signup')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }
                }
            }
        }

        return redirect('/')->with([
            'message' => language_data('Registration Successful')
        ]);

    }



    //======================================================================
    // adminLogin Function Start Here
    //======================================================================
    public function adminLogin()
    {

        if (Auth::check()) {
            return redirect('admin/dashboard');
        }

        return view('admin.login');
    }



    //======================================================================
    // adminGetLogin Function Start Here
    //======================================================================
    public function adminGetLogin(Request $request)
    {

        $this->validate($request, [
            'username' => 'required', 'password' => 'required'
        ]);

        $check_input = $request->only('username', 'password');
        $remember = (Input::has('remember')) ? true : false;

        if (app_config('captcha_in_admin') == '1') {
            if (isset($_POST['g-recaptcha-response'])) {
                $getCaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptcha = new ReCaptcha(app_config('captcha_secret_key'));
                $resp = $recaptcha->verify($getCaptchaResponse);

                if (!$resp->isSuccess()) {
                    if (array_key_exists('0', $resp->getErrorCodes())) {
                        $error_msg = $resp->getErrorCodes()[0];
                    } else {
                        $error_msg = language_data('Invalid Captcha');
                    }

                    return redirect('admin')->with([
                        'message' => $error_msg,
                        'message_important' => true
                    ]);
                }
            } else {
                return redirect('admin')->with([
                    'message' => language_data('Invalid Captcha'),
                    'message_important' => true
                ]);
            }
        }

        if (Auth::attempt($check_input, $remember)) {
            return redirect()->intended('admin/dashboard');
        } else {
            return redirect('admin')->withInput($request->only('username'))->withErrors([
                'username' => language_data('Invalid User name or Password')
            ]);
        }
    }

    //======================================================================
    // permissionError Function Start Here
    //======================================================================
    public function permissionError()
    {
        return view('admin.permission-error');
    }

    //======================================================================
    // forgotPassword Function Start Here
    //======================================================================
    public function forgotPassword()
    {
        return view('admin.forgot-password');
    }


    //======================================================================
    // forgotPasswordToken Function Start Here
    //======================================================================
    public function forgotPasswordToken(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('forgot-password')->withErrors($v->errors());
        }

        $email = Input::get('email');

        $d = Admin::where('email', '=', $email)->count();
        if ($d == '1') {
            $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $ef = Admin::where('email', '=', $email)->first();
            $name = $ef->fname . ' ' . $ef->lname;
            $username = $ef->username;
            $ef->pwresetkey = $fprand;
            $ef->save();

            /*For Email Confirmation*/

            $conf = EmailTemplates::where('tplname', '=', 'Forgot Admin Password')->first();

            $estatus = $conf->status;
            if ($estatus == '1') {
                $sysEmail = app_config('Email');
                $sysCompany = app_config('AppName');
                $fpw_link = url('admin/forgot-password-token-code/' . $fprand);

                $template = $conf->message;
                $subject = $conf->subject;

                $data = array('name' => $name,
                    'business_name' => $sysCompany,
                    'username' => $username,
                    'from' => $sysEmail,
                    'template' => $template,
                    'forgotpw_link' => $fpw_link
                );

                $message = _render($template, $data);
                $mail_subject = _render($subject, $data);
                $body = $message;

                /*Set Authentication*/

                $default_gt = app_config('Gateway');

                if ($default_gt == 'default') {

                    $mail = new \PHPMailer();

                    try {
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );
                        $mail->CharSet  = "utf-8";
                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;
                        if (!$mail->send()) {
                            return redirect('admin/forgot-password')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('admin/forgot-password')->with([
                                'message' => language_data('Password Reset Successfully. Please check your email')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('admin/forgot-password')->with([
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
                        $mail->isSMTP(); 
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );
                        $mail->CharSet  = "utf-8";                                       // Set mailer to use SMTP
                        $mail->Host = $host;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true;                               // Enable SMTP authentication
                        $mail->Username = $smtp_username;                 // SMTP username
                        $mail->Password = $stmp_password;                           // SMTP password
                        $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                        $mail->Port = $port;

                        $mail->setFrom($sysEmail,'Đổi mật khẩu');
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;

                        if (!$mail->send()) {
                            return redirect('admin/forgot-password')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('admin/forgot-password')->with([
                                'message' => language_data('Password Reset Successfully. Please check your email')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('admin/forgot-password')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }
                }
            }

            return redirect('admin/forgot-password')->with([
                'message' => language_data('Your Password Already Reset. Please Check your email')
            ]);
        } else {
            return redirect('admin/forgot-password')->with([
                'message' => language_data('Sorry There is no registered user with this email address'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // forgotPasswordTokenCode Function Start Here
    //======================================================================
    public function forgotPasswordTokenCode($token)
    {


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('admin')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $tfnd = Admin::where('pwresetkey', '=', $token)->count();

        if ($tfnd == '1') {
            $d = Admin::where('pwresetkey', '=', $token)->first();
            $name = $d->fname . ' ' . $d->lname;
            $email = $d->email;
            $username = $d->username;

            $rawpass = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $password = bcrypt($rawpass);

            $d->password = $password;
            $d->pwresetkey = '';
            $d->save();

            /*For Email Confirmation*/

            $conf = EmailTemplates::where('tplname', '=', 'Admin Password Reset')->first();

            $estatus = $conf->status;
            if ($estatus == '1') {
                $sysEmail = app_config('Email');
                $sysCompany = app_config('AppName');
                $fpw_link = url('admin');

                $template = $conf->message;
                $subject = $conf->subject;

                $data = array('name' => $name,
                    'business_name' => $sysCompany,
                    'username' => $username,
                    'password' => $rawpass,
                    'template' => $template,
                    'sys_url' => $fpw_link
                );

                $message = _render($template, $data);
                $mail_subject = _render($subject, $data);
                $body = $message;

                /*Set Authentication*/

                $default_gt = app_config('Gateway');

                if ($default_gt == 'default') {

                    $mail = new \PHPMailer();

                    try {
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );
                        $mail->CharSet  = "utf-8";
                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;
                        if (!$mail->send()) {
                            return redirect('admin')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('admin')->with([
                                'message' => language_data('A New Password Generated. Please Check your email.')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('admin')->with([
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

                        $mail->isSMTP(); 
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );
                        $mail->CharSet  = "utf-8";                                     // Set mailer to use SMTP
                        $mail->Host = $host;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true;                               // Enable SMTP authentication
                        $mail->Username = $smtp_username;                 // SMTP username
                        $mail->Password = $stmp_password;                           // SMTP password
                        $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                        $mail->Port = $port;

                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;

                        if (!$mail->send()) {
                            return redirect('admin')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('admin')->with([
                                'message' => language_data('A New Password Generated. Please Check your email.')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('admin')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }

                }

            }
            return redirect('admin')->with([
                'message' => language_data('A New Password Generated. Please Check your email.')
            ]);
        } else {
            return redirect('admin')->with([
                'message' => language_data('Sorry Password reset Token expired or not exist, Please try again.'),
                'message_important' => true
            ]);
        }


    }



    //======================================================================
    // forgotUserPasswordToken Function Start Here
    //======================================================================
    public function forgotUserPasswordToken(Request $request)
    {

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('forgot-password')->withErrors($v->errors());
        }

        $email = Input::get('email');

        $d = Client::where('email', '=', $email)->count();
        if ($d == '1') {
            $fprand = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $ef = Client::where('email', '=', $email)->first();
            $name = $ef->fname . ' ' . $ef->lname;
            $username = $ef->username;
            $ef->pwresetkey = $fprand;
            $ef->save();

            /*For Email Confirmation*/

            $conf = EmailTemplates::where('tplname', '=', 'Forgot Client Password Viet Nam')->first();

            $estatus = $conf->status;
            if ($estatus == '1') {
                $sysEmail = app_config('Email');
                $sysCompany = app_config('AppName');
                $fpw_link = url('user/forgot-password-token-code/' . $fprand);

                $template = $conf->message;
                $subject = $conf->subject;

                $data = array('name' => $name,
                    'business_name' => $sysCompany,
                    'username' => $username,
                    'from' => $sysEmail,
                    'template' => $template,
                    'forgotpw_link' => $fpw_link
                );

                $message = _render($template, $data);
                $mail_subject = _render($subject, $data);
                $body = $message;

                /*Set Authentication*/

                $default_gt = app_config('Gateway');

                if ($default_gt == 'default') {

                    $mail = new \PHPMailer();

                    try {
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );    
                        $mail->CharSet  = "utf-8";
                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;
                        if (!$mail->send()) {
                            return redirect('forgot-password')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('forgot-password')->with([
                                'message' => language_data('Password Reset Successfully. Please check your email')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('forgot-password')->with([
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
                        $mail->isSMTP(); 
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );    
                        $mail->CharSet  = "utf-8";                                     // Set mailer to use SMTP
                        $mail->Host = $host;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true;                               // Enable SMTP authentication
                        $mail->Username = $smtp_username;                 // SMTP username
                        $mail->Password = $stmp_password;                           // SMTP password
                        $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                        $mail->Port = $port;
                        $mail->setFrom($sysEmail,'Yêu cầu đổi mật khẩu');
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;

                        if (!$mail->send()) {
                            return redirect('forgot-password')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('forgot-password')->with([
                                'message' => language_data('Password Reset Successfully. Please check your email')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('forgot-password')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }
                }
            }

            return redirect('forgot-password')->with([
                'message' => language_data('Your Password Already Reset. Please Check your email')
            ]);
        } else {
            return redirect('forgot-password')->with([
                'message' => language_data('Sorry There is no registered user with this email address'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // forgotUserPasswordTokenCode Function Start Here
    //======================================================================
    public function forgotUserPasswordTokenCode($token)
    {


        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $tfnd = Client::where('pwresetkey', '=', $token)->count();

        if ($tfnd == '1') {
            $d = Client::where('pwresetkey', '=', $token)->first();
            $name = $d->fname . ' ' . $d->lname;
            $email = $d->email;
            $username = $d->username;

            $rawpass = substr(str_shuffle(str_repeat('0123456789', '16')), 0, '16');
            $password = bcrypt($rawpass);

            $d->password = $password;
            $d->pwresetkey = '';
            $d->save();

            /*For Email Confirmation*/

            $conf = EmailTemplates::where('tplname', '=', 'Client Password Reset Viet Nam')->first();

            $estatus = $conf->status;
            if ($estatus == '1') {
                $sysEmail = app_config('Email');
                $sysCompany = app_config('AppName');
                $fpw_link = url('/');

                $template = $conf->message;
                $subject = $conf->subject;

                $data = array('name' => $name,
                    'business_name' => $sysCompany,
                    'username' => $username,
                    'password' => $rawpass,
                    'template' => $template,
                    'sys_url' => $fpw_link
                );

                $message = _render($template, $data);
                $mail_subject = _render($subject, $data);
                $body = $message;

                /*Set Authentication*/

                $default_gt = app_config('Gateway');

                if ($default_gt == 'default') {

                    $mail = new \PHPMailer();

                    try {
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );    
                        $mail->CharSet  = "utf-8";   
                        $mail->setFrom($sysEmail, $sysCompany);
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;
                        if (!$mail->send()) {
                            return redirect('/')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('/')->with([
                                'message' => language_data('A New Password Generated. Please Check your email.')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('/')->with([
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

                        $mail->isSMTP(); 
                        $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                        );    
                        $mail->CharSet  = "utf-8";                                        // Set mailer to use SMTP
                        $mail->Host = $host;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth = true;                               // Enable SMTP authentication
                        $mail->Username = $smtp_username;                 // SMTP username
                        $mail->Password = $stmp_password;                           // SMTP password
                        $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                        $mail->Port = $port;

                        $mail->setFrom($sysEmail,'Mật khẩu mới');
                        $mail->addAddress($email, $name);     // Add a recipient
                        $mail->isHTML(true);                                  // Set email format to HTML

                        $mail->Subject = $mail_subject;
                        $mail->Body = $body;

                        if (!$mail->send()) {
                            return redirect('/')->with([
                                'message' => language_data('Please Check your Email Settings'),
                                'message_important' => true
                            ]);
                        } else {
                            return redirect('/')->with([
                                'message' => language_data('A New Password Generated. Please Check your email.')
                            ]);
                        }
                    } catch (\phpmailerException $e) {
                        return redirect('/')->with([
                            'message' => $e->getMessage(),
                            'message_important' => true
                        ]);
                    }

                }

            }
            return redirect('/')->with([
                'message' => language_data('A New Password Generated. Please Check your email.')
            ]);
        } else {
            return redirect('/')->with([
                'message' => language_data('Sorry Password reset Token expired or not exist, Please try again.'),
                'message_important' => true
            ]);
        }
    }


    /* updateApplication  Function Start Here */
    public function updateApplication()
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('/')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $msg = 'Running SQL Update.... <br>';
        $v = '1.1';
        $latest = '1.2';

        $find = app_config('SoftwareVersion');

        if ($find == '1.2') {
            $v = '1.2';
            $msg .= 'It seems, your version is up to date for version 1.2 <br>';
        } else {
            $msg .= 'Running update for Version 1.2 ..... <br>';

            $sql = <<<EOF
            
INSERT INTO `sys_app_config` (`id`, `setting`, `value`, `created_at`, `updated_at`) VALUES (NULL, 'sender_id_verification', '1', '2017-07-14 13:42:24', '2017-07-14 13:42:24');

CREATE TABLE `sys_bulk_sms` (
  `id` int(10) UNSIGNED NOT NULL,
  `userid` int(11) NOT NULL,
  `sender` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int(11) NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `use_gateway` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sys_import_phone_number` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numbers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `sys_import_phone_number`
  ADD PRIMARY KEY (`id`);
  
  ALTER TABLE `sys_import_phone_number`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

DELETE FROM `sys_language_data` WHERE `lan_id`='1';


INSERT INTO `sys_language_data` (`id`, `lan_id`, `lan_data`, `lan_value`, `created_at`, `updated_at`) VALUES
(NULL, 1, 'Admin', 'Admin', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Login', 'Login', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Forget Password', 'Forget Password', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Sign to your account', 'Sign to your account', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'User Name', 'User Name', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Password', 'Password', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Remember Me', 'Remember Me', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Reset your password', 'Reset your password', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Email', 'Email', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Add New Client', 'Add New Client', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'First Name', 'First Name', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Last Name', 'Last Name', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Company', 'Company', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Website', 'Website', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'If you leave this, then you can not reset password or can not maintain email related function', 'If you leave this, then you can not reset password or can not maintain email related function', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Confirm Password', 'Confirm Password', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Phone', 'Phone', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'Address', 'Address', '2017-07-14 13:42:42', '2017-07-14 13:42:42'),
(NULL, 1, 'More Address', 'More Address', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'State', 'State', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'City', 'City', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Postcode', 'Postcode', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Country', 'Country', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Api Access', 'Api Access', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Yes', 'Yes', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'No', 'No', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Client Group', 'Client Group', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'None', 'None', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'SMS Gateway', 'SMS Gateway', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'SMS Limit', 'SMS Limit', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Avatar', 'Avatar', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Browse', 'Browse', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Notify Client with email', 'Notify Client with email', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Add', 'Add', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Add New Invoice', 'Add New Invoice', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Client', 'Client', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'Invoice Type', 'Invoice Type', '2017-07-14 13:42:43', '2017-07-14 13:42:43'),
(NULL, 1, 'One Time', 'One Time', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Recurring', 'Recurring', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Invoice Date', 'Invoice Date', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Due Date', 'Due Date', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Paid Date', 'Paid Date', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Repeat Every', 'Repeat Every', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Week', 'Week', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, '2 Weeks', '2 Weeks', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Month', 'Month', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, '2 Months', '2 Months', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, '3 Months', '3 Months', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, '6 Months', '6 Months', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Year', 'Year', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, '2 Years', '2 Years', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, '3 Years', '3 Years', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Item Name', 'Item Name', '2017-07-14 13:42:44', '2017-07-14 13:42:44'),
(NULL, 1, 'Price', 'Price', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Qty', 'Qty', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Quantity', 'Quantity', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Tax', 'Tax', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Discount', 'Discount', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Per Item Total', 'Per Item Total', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Add Item', 'Add Item', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Item', 'Item', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Delete', 'Delete', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Total', 'Total', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Invoice Note', 'Invoice Note', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Create Invoice', 'Create Invoice', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Add Plan Feature', 'Add Plan Feature', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Show In Client', 'Show In Client', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Feature Name', 'Feature Name', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Feature Value', 'Feature Value', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Action', 'Action', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Add More', 'Add More', '2017-07-14 13:42:45', '2017-07-14 13:42:45'),
(NULL, 1, 'Save', 'Save', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Add SMS Price Plan', 'Add SMS Price Plan', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Plan Name', 'Plan Name', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Mark Popular', 'Mark Popular', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Popular', 'Popular', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Show', 'Show', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Hide', 'Hide', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Add Plan', 'Add Plan', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Add Sender ID', 'Add Sender ID', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'All', 'All', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Status', 'Status', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Block', 'Block', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Unblock', 'Unblock', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Sender ID', 'Sender ID', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Add SMS Gateway', 'Add SMS Gateway', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Gateway Name', 'Gateway Name', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Gateway API Link', 'Gateway API Link', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Api link execute like', 'Api link execute like', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Active', 'Active', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Inactive', 'Inactive', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Parameter', 'Parameter', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Value', 'Value', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Add On URL', 'Add On URL', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Username_Key', 'Username/Key', '2017-07-14 13:42:46', '2017-07-14 13:42:46'),
(NULL, 1, 'Set Blank', 'Set Blank', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Add on parameter', 'Add on parameter', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Source', 'Source', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Destination', 'Destination', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Message', 'Message', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Unicode', 'Unicode', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Type_Route', 'Type/Route', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Language', 'Language', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Custom Value 1', 'Custom Value 1', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Custom Value 2', 'Custom Value 2', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Custom Value 3', 'Custom Value 3', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Administrator Roles', 'Administrator Roles', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Add Administrator Role', 'Add Administrator Role', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Role Name', 'Role Name', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'SL', 'SL', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Set Roles', 'Set Roles', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Administrators', 'Administrators', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Add New Administrator', 'Add New Administrator', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Role', 'Role', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Notify Administrator with email', 'Notify Administrator with email', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Name', 'Name', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'All Clients', 'All Clients', '2017-07-14 13:42:47', '2017-07-14 13:42:47'),
(NULL, 1, 'Clients', 'Clients', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Created', 'Created', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Created By', 'Created By', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Manage', 'Manage', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Closed', 'Closed', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'All Invoices', 'All Invoices', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Client Name', 'Client Name', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Amount', 'Amount', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Type', 'Type', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Unpaid', 'Unpaid', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Paid', 'Paid', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Cancelled', 'Cancelled', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Partially Paid', 'Partially Paid', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Onetime', 'Onetime', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Recurring', 'Recurring', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Stop Recurring', 'Stop Recurring', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'View', 'View', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Change Password', 'Change Password', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Current Password', 'Current Password', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'New Password', 'New Password', '2017-07-14 13:42:48', '2017-07-14 13:42:48'),
(NULL, 1, 'Update', 'Update', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Edit', 'Edit', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Clients Groups', 'Clients Groups', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Add New Group', 'Add New Group', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Group Name', 'Group Name', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Export Clients', 'Export Clients', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'View Profile', 'View Profile', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Location', 'Location', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'SMS Balance', 'SMS Balance', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Send SMS', 'Send SMS', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Update Limit', 'Update Limit', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Change Image', 'Change Image', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Edit Profile', 'Edit Profile', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Support Tickets', 'Support Tickets', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Change', 'Change', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Basic Info', 'Basic Info', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Invoices', 'Invoices', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'SMS Transaction', 'SMS Transaction', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Leave blank if you do not change', 'Leave blank if you do not change', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Subject', 'Subject', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Date', 'Date', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Pending', 'Pending', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Answered', 'Answered', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Customer Reply', 'Customer Reply', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'characters remaining', 'characters remaining', '2017-07-14 13:42:49', '2017-07-14 13:42:49'),
(NULL, 1, 'Close', 'Close', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Send', 'Send', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Update with previous balance. Enter (-) amount for decrease limit', 'Update with previous balance. Enter (-) amount for decrease limit', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Update Image', 'Update Image', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Coverage', 'Coverage', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'ISO Code', 'ISO Code', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Country Code', 'Country Code', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Tariff', 'Tariff', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Live', 'Live', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Offline', 'Offline', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Create New Ticket', 'Create New Ticket', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Ticket For Client', 'Ticket For Client', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Department', 'Department', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Create Ticket', 'Create Ticket', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Create SMS Template', 'Create SMS Template', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'SMS Templates', 'SMS Templates', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Select Template', 'Select Template', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Template Name', 'Template Name', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'From', 'From', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Insert Merge Filed', 'Insert Merge Filed', '2017-07-14 13:42:50', '2017-07-14 13:42:50'),
(NULL, 1, 'Select Merge Field', 'Select Merge Field', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Phone Number', 'Phone Number', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Add New', 'Add New', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Tickets', 'Tickets', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Invoices History', 'Invoices History', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Tickets History', 'Tickets History', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'SMS Success History', 'SMS Success History', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'SMS History By Date', 'SMS History By Date', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Recent 5 Invoices', 'Recent 5 Invoices', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Recent 5 Support Tickets', 'Recent 5 Support Tickets', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Edit Invoice', 'Edit Invoice', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'View Invoice', 'View Invoice', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Send Invoice', 'Send Invoice', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Access Role', 'Access Role', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Super Admin', 'Super Admin', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Personal Details', 'Personal Details', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Unique For every User', 'Unique For every User', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Email Templates', 'Email Templates', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Manage Email Template', 'Manage Email Template', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Export and Import Clients', 'Export and Import Clients', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Export Clients', 'Export Clients', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Export Clients as CSV', 'Export Clients as CSV', '2017-07-14 13:42:51', '2017-07-14 13:42:51'),
(NULL, 1, 'Sample File', 'Sample File', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Download Sample File', 'Download Sample File', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Import Clients', 'Import Clients', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'It will take few minutes. Please do not reload the page', 'It will take few minutes. Please do not reload the page', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Import', 'Import', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Reset My Password', 'Reset My Password', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Back To Sign in', 'Back To Sign in', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Invoice No', 'Invoice No', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Invoice', 'Invoice', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Invoice To', 'Invoice To', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Printable Version', 'Printable Version', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Invoice Status', 'Invoice Status', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Subtotal', 'Subtotal', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Grand Total', 'Grand Total', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Amount Due', 'Amount Due', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Add Language', 'Add Language', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Flag', 'Flag', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'All Languages', 'All Languages', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Translate', 'Translate', '2017-07-14 13:42:52', '2017-07-14 13:42:52'),
(NULL, 1, 'Language Manage', 'Language Manage', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Language Name', 'Language Name', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'English To', 'English To', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'English', 'English', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Localization', 'Localization', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Date Format', 'Date Format', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Timezone', 'Timezone', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Default Language', 'Default Language', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Current Code', 'Current Code', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Current Symbol', 'Current Symbol', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Default Country', 'Default Country', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Manage Administrator', 'Manage Administrator', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Manage Coverage', 'Manage Coverage', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Cost for per SMS', 'Cost for per SMS', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'SMS Gateway Manage', 'SMS Gateway Manage', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Manage Plan Feature', 'Manage Plan Feature', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'SMS Plan Features', 'SMS Plan Features', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Update Feature', 'Update Feature', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Manage SMS Price Plan', 'Manage SMS Price Plan', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'SMS Price Plan', 'SMS Price Plan', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Update Plan', 'Update Plan', '2017-07-14 13:42:53', '2017-07-14 13:42:53'),
(NULL, 1, 'Msisdn', 'Msisdn', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Account Sid', 'Account Sid', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'SMS Api', 'SMS Api', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'SMS Api User name', 'SMS Api User name', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Auth ID', 'Auth ID', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Auth Token', 'Auth Token', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'SMS Api key', 'SMS Api key', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'SMS Api Password', 'SMS Api Password', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Extra Value', 'Extra Value', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Schedule SMS', 'Schedule SMS', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Manage SMS Template', 'Manage SMS Template', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Edit Administrator Role', 'Edit Administrator Role', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Manage Payment Gateway', 'Manage Payment Gateway', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Publishable Key', 'Publishable Key', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Bank Details', 'Bank Details', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Api Login ID', 'Api Login ID', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Secret_Key_Signature', 'Secret Key/Signature', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Transaction Key', 'Transaction Key', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Payment Gateways', 'Payment Gateways', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Send Bulk SMS', 'Send Bulk SMS', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Bulk SMS', 'Bulk SMS', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'After click on Send button, do not refresh your browser', 'After click on Send button, do not refresh your browser', '2017-07-14 13:42:54', '2017-07-14 13:42:54'),
(NULL, 1, 'Schedule Time', 'Schedule Time', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Import Numbers', 'Import Numbers', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Set Rules', 'Set Rules', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Check All', 'Check All', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Send SMS From File', 'Send SMS From File', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Schedule SMS From File', 'Schedule SMS From File', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'SMS History', 'SMS History', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Add Price Plan', 'Add Price Plan', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Sender ID Management', 'Sender ID Management', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Support Department', 'Support Department', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Department Name', 'Department Name', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Department Email', 'Department Email', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'System Settings', 'System Settings', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Language Settings', 'Language Settings', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'SMS API Info', 'SMS API Info', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'SMS API URL', 'SMS API URL', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Generate New', 'Generate New', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'SMS API Details', 'SMS API Details', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Add Gateway', 'Add Gateway', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Two Way', 'Two Way', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Send By', 'Send By', '2017-07-14 13:42:55', '2017-07-14 13:42:55'),
(NULL, 1, 'Sender', 'Sender', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Receiver', 'Receiver', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Inbox', 'Inbox', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Add Feature', 'Add Feature', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'View Features', 'View Features', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Create Template', 'Create Template', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Application Name', 'Application Name', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Application Title', 'Application Title', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'System Email', 'System Email', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Remember: All Email Going to the Receiver from this Email', 'Remember: All Email Going to the Receiver from this Email', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Footer Text', 'Footer Text', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Application Logo', 'Application Logo', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Application Favicon', 'Application Favicon', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'API Permission', 'API Permission', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Allow Client Registration', 'Allow Client Registration', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Client Registration Verification', 'Client Registration Verification', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Email Gateway', 'Email Gateway', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Server Default', 'Server Default', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'SMTP', 'SMTP', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Host Name', 'Host Name', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Port', 'Port', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'Secure', 'Secure', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'TLS', 'TLS', '2017-07-14 13:42:56', '2017-07-14 13:42:56'),
(NULL, 1, 'SSL', 'SSL', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Mark As', 'Mark As', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Preview', 'Preview', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'PDF', 'PDF', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Print', 'Print', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Ticket Management', 'Ticket Management', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Ticket Details', 'Ticket Details', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Ticket Discussion', 'Ticket Discussion', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Ticket Files', 'Ticket Files', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Created Date', 'Created Date', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Created By', 'Created By', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Department', 'Department', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Closed By', 'Closed By', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'File Title', 'File Title', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Select File', 'Select File', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Files', 'Files', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Size', 'Size', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Upload By', 'Upload By', '2017-07-14 13:42:57', '2017-07-14 13:42:57'),
(NULL, 1, 'Upload', 'Upload', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Dashboard', 'Dashboard', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Settings', 'Settings', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Logout', 'Logout', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Recent 5 Unpaid Invoices', 'Recent 5 Unpaid Invoices', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'See All Invoices', 'See All Invoices', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Recent 5 Pending Tickets', 'Recent 5 Pending Tickets', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'See All Tickets', 'See All Tickets', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Update Profile', 'Update Profile', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'You do not have permission to view this page', 'You do not have permission to view this page', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'This Option is Disable In Demo Mode', 'This Option is Disable In Demo Mode', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'User name already exist', 'User name already exist', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Email already exist', 'Email already exist', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Both password does not match', 'Both password does not match', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator added successfully', 'Administrator added successfully', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator not found', 'Administrator not found', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator updated successfully', 'Administrator updated successfully', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator have support tickets. First delete support ticket', 'Administrator have support tickets. First delete support ticket', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator have SMS Log. First delete all sms', 'Administrator have SMS Log. First delete all sms', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator created invoice. First delete all invoice', 'Administrator created invoice. First delete all invoice', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator delete successfully', 'Administrator delete successfully', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator Role added successfully', 'Administrator Role added successfully', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator Role already exist', 'Administrator Role already exist', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator Role updated successfully', 'Administrator Role updated successfully', '2017-07-14 13:42:58', '2017-07-14 13:42:58'),
(NULL, 1, 'Administrator Role info not found', 'Administrator Role info not found', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Permission not assigned', 'Permission not assigned', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Permission Updated', 'Permission Updated', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'An Administrator contain this role', 'An Administrator contain this role', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Administrator role deleted successfully', 'Administrator role deleted successfully', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Invalid User name or Password', 'Invalid User name or Password', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Please Check your Email Settings', 'Please Check your Email Settings', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Password Reset Successfully. Please check your email', 'Password Reset Successfully. Please check your email', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Your Password Already Reset. Please Check your email', 'Your Password Already Reset. Please Check your email', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Sorry There is no registered user with this email address', 'Sorry There is no registered user with this email address', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'A New Password Generated. Please Check your email.', 'A New Password Generated. Please Check your email.', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Sorry Password reset Token expired or not exist, Please try again.', 'Sorry Password reset Token expired or not exist, Please try again.', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Client Added Successfully But Email Not Send', 'Client Added Successfully But Email Not Send', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Client Added Successfully', 'Client Added Successfully', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Client info not found', 'Client info not found', '2017-07-14 13:42:59', '2017-07-14 13:42:59'),
(NULL, 1, 'Limit updated successfully', 'Limit updated successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Image updated successfully', 'Image updated successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Please try again', 'Please try again', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Client updated successfully', 'Client updated successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'SMS gateway not active', 'SMS gateway not active', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Please check sms history', 'Please check sms history', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Insert Valid Excel or CSV file', 'Insert Valid Excel or CSV file', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Client imported successfully', 'Client imported successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Client Group added successfully', 'Client Group added successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Client Group updated successfully', 'Client Group updated successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Client Group not found', 'Client Group not found', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'This Group exist in a client', 'This Group exist in a client', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Client group deleted successfully', 'Client group deleted successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Invoice not found', 'Invoice not found', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Logout Successfully', 'Logout Successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Profile Updated Successfully', 'Profile Updated Successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Upload an Image', 'Upload an Image', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Password Change Successfully', 'Password Change Successfully', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Current Password Does Not Match', 'Current Password Does Not Match', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Select a Customer', 'Select a Customer', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Invoice Created date is required', 'Invoice Created date is required', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Invoice Paid date is required', 'Invoice Paid date is required', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Date Parsing Error', 'Date Parsing Error', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'Invoice Due date is required', 'Invoice Due date is required', '2017-07-14 13:43:00', '2017-07-14 13:43:00'),
(NULL, 1, 'At least one item is required', 'At least one item is required', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Updated Successfully', 'Invoice Updated Successfully', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Marked as Paid', 'Invoice Marked as Paid', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Marked as Unpaid', 'Invoice Marked as Unpaid', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Marked as Partially Paid', 'Invoice Marked as Partially Paid', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Marked as Cancelled', 'Invoice Marked as Cancelled', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Send Successfully', 'Invoice Send Successfully', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice deleted successfully', 'Invoice deleted successfully', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Stop Recurring Invoice Successfully', 'Stop Recurring Invoice Successfully', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice Created Successfully', 'Invoice Created Successfully', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Reseller Panel', 'Reseller Panel', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Captcha In Admin Login', 'Captcha In Admin Login', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Captcha In Client Login', 'Captcha In Client Login', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Captcha In Client Registration', 'Captcha In Client Registration', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'reCAPTCHA Site Key', 'reCAPTCHA Site Key', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'reCAPTCHA Secret Key', 'reCAPTCHA Secret Key', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Registration Successful', 'Registration Successful', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Payment gateway required', 'Payment gateway required', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Cancelled the Payment', 'Cancelled the Payment', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Invoice paid successfully', 'Invoice paid successfully', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'Purchase successfully.Wait for administrator response', 'Purchase successfully.Wait for administrator response', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'SMS Not Found', 'SMS Not Found', '2017-07-14 13:43:01', '2017-07-14 13:43:01'),
(NULL, 1, 'SMS info deleted successfully', 'SMS info deleted successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Setting Update Successfully', 'Setting Update Successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Email Template Not Found', 'Email Template Not Found', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Email Template Update Successfully', 'Email Template Update Successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Payment Gateway not found', 'Payment Gateway not found', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Payment Gateway update successfully', 'Payment Gateway update successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Language Already Exist', 'Language Already Exist', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Language Added Successfully', 'Language Added Successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Language Translate Successfully', 'Language Translate Successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Language not found', 'Language not found', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Language updated Successfully', 'Language updated Successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Can not delete active language', 'Can not delete active language', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Language deleted successfully', 'Language deleted successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Information not found', 'Information not found', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Coverage updated successfully', 'Coverage updated successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Sender Id added successfully', 'Sender Id added successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Sender Id not found', 'Sender Id not found', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Sender id updated successfully', 'Sender id updated successfully', '2017-07-14 13:43:02', '2017-07-14 13:43:02'),
(NULL, 1, 'Sender id deleted successfully', 'Sender id deleted successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan already exist', 'Plan already exist', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan added successfully', 'Plan added successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan not found', 'Plan not found', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan updated successfully', 'Plan updated successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan features added successfully', 'Plan features added successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan feature not found', 'Plan feature not found', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Feature already exist', 'Feature already exist', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Feature updated successfully', 'Feature updated successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Plan feature deleted successfully', 'Plan feature deleted successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Price Plan deleted successfully', 'Price Plan deleted successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Gateway already exist', 'Gateway already exist', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Custom gateway added successfully', 'Custom gateway added successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Parameter or Value is empty', 'Parameter or Value is empty', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Gateway information not found', 'Gateway information not found', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Gateway name required', 'Gateway name required', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Custom gateway updated successfully', 'Custom gateway updated successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Client are registered with this gateway', 'Client are registered with this gateway', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Gateway deleted successfully', 'Gateway deleted successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Delete option disable for this gateway', 'Delete option disable for this gateway', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'SMS added in queue and will deliver one by one', 'SMS added in queue and will deliver one by one', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Insert Valid Excel or CSV file', 'Insert Valid Excel or CSV file', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'SMS are scheduled. Deliver in correct time', 'SMS are scheduled. Deliver in correct time', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Template already exist', 'Template already exist', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Sms template created successfully', 'Sms template created successfully', '2017-07-14 13:43:03', '2017-07-14 13:43:03'),
(NULL, 1, 'Sms template not found', 'Sms template not found', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Sms template updated successfully', 'Sms template updated successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Sms template delete successfully', 'Sms template delete successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'API information updated successfully', 'API information updated successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Invalid Access', 'Invalid Access', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Invalid Captcha', 'Invalid Captcha', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Invalid Request', 'Invalid Request', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Verification code send successfully. Please check your email', 'Verification code send successfully. Please check your email', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Something wrong, Please contact with your provider', 'Something wrong, Please contact with your provider', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Verification code not found', 'Verification code not found', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Department Already exist', 'Department Already exist', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Department Added Successfully', 'Department Added Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Department Updated Successfully', 'Department Updated Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Support Ticket Created Successfully But Email Not Send', 'Support Ticket Created Successfully But Email Not Send', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Support Ticket Created Successfully', 'Support Ticket Created Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Basic Info Update Successfully', 'Basic Info Update Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Ticket Reply Successfully But Email Not Send', 'Ticket Reply Successfully But Email Not Send', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Ticket Reply Successfully', 'Ticket Reply Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'File Uploaded Successfully', 'File Uploaded Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Please Upload a File', 'Please Upload a File', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'File Deleted Successfully', 'File Deleted Successfully', '2017-07-14 13:43:04', '2017-07-14 13:43:04'),
(NULL, 1, 'Ticket File not found', 'Ticket File not found', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Ticket Deleted Successfully', 'Ticket Deleted Successfully', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Ticket info not found', 'Ticket info not found', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Department Deleted Successfully', 'Department Deleted Successfully', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'There Have no Department For Delete', 'There Have no Department For Delete', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'You do not have enough sms balance', 'You do not have enough sms balance', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'SMS gateway not active.Contact with Provider', 'SMS gateway not active.Contact with Provider', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Sender ID required', 'Sender ID required', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Request send successfully', 'Request send successfully', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'This Sender ID have Blocked By Administrator', 'This Sender ID have Blocked By Administrator', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Phone Number Coverage are not active', 'Phone Number Coverage are not active', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'SMS plan not found', 'SMS plan not found', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Schedule feature not supported', 'Schedule feature not supported', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Need Account', 'Need Account', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Sign up', 'Sign up', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'here', 'here', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'User Registration', 'User Registration', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Already have an Account', 'Already have an Account', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Request New Sender ID', 'Request New Sender ID', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Purchase Now', 'Purchase Now', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Purchase SMS Plan', 'Purchase SMS Plan', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Select Payment Method', 'Select Payment Method', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Pay with Credit Card', 'Pay with Credit Card', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'User Registration Verification', 'User Registration Verification', '2017-07-14 13:43:05', '2017-07-14 13:43:05'),
(NULL, 1, 'Verify Your Account', 'Verify Your Account', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Send Verification Email', 'Send Verification Email', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Pay', 'Pay', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Pay Invoice', 'Pay Invoice', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Reply Ticket', 'Reply Ticket', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Whoops! Page Not Found, Go To', 'Whoops! Page Not Found, Go To', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Home Page', 'Home Page', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Error', 'Error', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Client contain in', 'Client contain in', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Client sms limit not empty', 'Client sms limit not empty', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'This client have some customer', 'This client have some customer', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Client delete successfully', 'Client delete successfully', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Client Group is empty', 'Client Group is empty', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Country flag required', 'Country flag required', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Single', 'Single', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'SMS', 'SMS', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Client ID', 'Client ID', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Client Secret', 'Client Secret', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Bulk Birthday SMS', 'Bulk Birthday SMS', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Bulk SMS Remainder', 'Bulk SMS Remainder', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'SMS By Phone Number', 'SMS By Phone Number', '2017-07-14 13:43:06', '2017-07-14 13:43:06'),
(NULL, 1, 'Import Phone Number', 'Import Phone Number', '2017-07-14 13:43:07', '2017-07-14 13:43:07'),
(NULL, 1, 'Sender ID Verification', 'Sender ID Verification', '2017-07-14 13:43:07', '2017-07-14 13:43:07');


INSERT INTO `sys_payment_gateways` (`id`, `name`, `value`, `settings`, `extra_value`, `password`, `status`, `created_at`, `updated_at`) VALUES
(NULL, 'PayU', '300046', 'payu', 'c8d4b7ac61758704f38ed5564d8c0ae0', NULL, 'Active', '2017-07-14 13:42:29', '2017-07-14 13:42:29'),
(NULL, 'Slydepay', 'merchantEmail', 'slydepay', 'merchantSecretKey', NULL, 'Active', '2017-07-14 13:42:29', '2017-07-14 13:42:29');


INSERT INTO `sys_sms_gateways` (`id`, `name`, `api_link`, `username`, `password`, `api_id`, `schedule`, `custom`, `status`, `two_way`, `created_at`, `updated_at`) VALUES
(NULL, 'Asterisk', 'http://127.0.0.1', 'username', 'secret', '5038', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:26', '2017-07-14 13:42:26'),
(NULL, 'Elibom', 'https://www.elibom.com/messages', 'your_elibom_email', 'your_api_passwrod', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28'),
(NULL, 'Hablame', 'https://api.hablame.co/sms/envio', 'client_id', 'api_secret', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28'),
(NULL, 'Wavecell', 'https://api.wavecell.com/sms/v1/', 'sub_account_id', 'api_password', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28'),
(NULL, 'SIPTraffic', 'https://www.siptraffic.com', 'sub_account_id', 'api_password', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28'),
(NULL, 'SMSMKT', 'http://member.smsmkt.com/SMSLink/SendMsg/index.php', 'username', 'password', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28'),
(NULL, 'MLat', 'https://m-lat.net:8443/axis2/services/SMSServiceWS', 'user', 'password', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28'),
(NULL, 'NRSGateway', 'https://gateway.plusmms.net/send.php', 'tu_user', 'tu_login', '', 'Yes', 'No', 'Active', 'No', '2017-07-14 13:42:28', '2017-07-14 13:42:28');

ALTER TABLE `sys_bulk_sms`
  ADD PRIMARY KEY (`id`);
  
  ALTER TABLE `sys_bulk_sms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

EOF;

            $msg .= 'Importing Version 1.2 SQL Data....... <br>';

            // Execute SQL QUERY
            \DB::connection()->getPdo()->exec($sql);

            AppConfig::where('setting', '=', 'SoftwareVersion')->update(['value' => '1.2']);

            $msg .= 'Data import Completed....... <br>';
            $msg .= '=====Version 1.2 Update Complete ======" <br>';

        }

        if ($v == $latest) {
            echo 'Your Version is Up to Date!';
        } else {

            echo $msg;
        }

    }


}
