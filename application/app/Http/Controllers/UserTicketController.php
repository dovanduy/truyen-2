<?php

namespace App\Http\Controllers;

use App\Client;
use App\EmailTemplates;
use App\SupportDepartments;
use App\SupportTicketFiles;
use App\SupportTickets;
use App\SupportTicketsReplies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Mail;
class UserTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }


    /* allSupportTickets  Function Start Here */
    public function allSupportTickets()
    {
        $st = SupportTickets::where('cl_id', Auth::guard('client')->user()->id)->get();
        return view('client.support-tickets', compact('st'));
    }

    /* createNewTicket  Function Start Here */
    public function createNewTicket()
    {
        
        $sd = SupportDepartments::where('show', 'Yes')->get();
        return view('client.create-new-ticket', compact('sd'));
    }


    /* postTicket  Function Start Here */
    public function postTicket(Request $request)
    {
        /*Mail::send('testmail',
            array('email' =>'hai',
            ), function ($message) {
            $message->to('haitv1@spt.vn', 'test email')->subject('test email');
            });
        die;*/
        $v = \Validator::make($request->all(), [
            'subject' => 'required', 'message' => 'required', 'did' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/tickets/create-new')->withErrors($v->errors());
        }

        $subject = Input::get('subject');
        $subjectTitle=$subject;
        $st_message = Input::get('message');
        $did = Input::get('did');

        $cl = Client::find(Auth::guard('client')->user()->id);
        $cl_name = $cl->fname . ' ' . $cl->lname;
        $cl_email = $cl->email;

        $d = new SupportTickets();
        $d->did = $did;
        $d->cl_id = Auth::guard('client')->user()->id;
        $d->admin_id = '0';
        $d->name = $cl_name;
        $d->email = $cl_email;
        $d->date = date('Y-m-d');
        $d->subject = $subject;
        $d->message = $st_message;
        $d->status = 'Pending';
        $d->admin = '0';
        $d->replyby = '';
        $d->closed_by = '';
        $d->save();
        $cmd = $d->id;


        /*For Email Confirmation*/

        $conf = EmailTemplates::where('tplname', '=', 'Ticket For Admin Viet Nam')->first();
        $estatus = $conf->status;
        if ($estatus == '1') {

            $deprt = SupportDepartments::find($did);

            $sysEmail = $deprt->email;
            $sysCompany = $deprt->name;
            $sysUrl = url('/');

            $template = $conf->message;
            $subject = $conf->subject;

            $data = array('name' => $cl_name,
                'business_name' => $sysCompany,
                'ticket_id' => $cmd,
                'ticket_subject' => $subject,
                'subjectTitle'=>$subjectTitle,
                'message' => $st_message,
                'template' => $template,
                'sys_url' => $sysUrl
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
                    $mail->addAddress($cl_email, $cl_name);     // Add a recipient
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = $mail_subject;
                    $mail->Body = $body;
                    if (!$mail->send()) {

                        return redirect('user/tickets/view-ticket/' . $cmd)->with([
                            'message' => language_data('Support Ticket Created Successfully But Email Not Send')
                        ]);
                    } else {
                        return redirect('user/tickets/view-ticket/' . $cmd)->with([
                            'message' => language_data('Support Ticket Created Successfully')
                        ]);
                    }
                } catch (\phpmailerException $e) {

                    return redirect('user/tickets/view-ticket/' . $cmd)->with([
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

                try{
                    $mail->isSMTP();
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );                                            // Set mailer to use SMTP
                    $mail->Host = $host;  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true;                               // Enable SMTP authentication
                    $mail->Username = $smtp_username;                 // SMTP username
                    $mail->Password = $stmp_password;                           // SMTP password
                    $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = $port;
                    $mail->CharSet  = "utf-8";

                    $mail->setFrom($sysEmail, $sysCompany);
                    $mail->addAddress($cl_email, $cl_name);     // Add a recipient
                    $mail->isHTML(true);                                  // Set email format to HTML

                    $mail->Subject = $mail_subject;
                    $mail->Body = $body;

                    if (!$mail->send()) {

                        return redirect('user/tickets/view-ticket/' . $cmd)->with([
                            'message' => language_data('Support Ticket Created Successfully But Email Not Send')
                        ]);
                    } else {

                        return redirect('user/tickets/view-ticket/' . $cmd)->with([
                            'message' => language_data('Support Ticket Created Successfully')
                        ]);
                    }
                }catch (\phpmailerException $e){
                    return redirect('user/tickets/view-ticket/' . $cmd)->with([
                        'message' => $e->getMessage(),
                        'message_important' => true
                    ]);
                }

            }
        }
        return redirect('user/tickets/view-ticket/' . $cmd)->with([
            'message' => language_data('Support Ticket Created Successfully')
        ]);
    }


    /* viewTicket  Function Start Here */
    public function viewTicket($id)
    {
        $st = SupportTickets::where('cl_id',Auth::guard('client')->user()->id)->find($id);
        $did = $st->did;
        $td = SupportDepartments::find($did);
        $trply = SupportTicketsReplies::where('tid', $id)->orderBy('date', 'desc')->get();
        $department = SupportDepartments::all();
        $ticket_file = SupportTicketFiles::where('ticket_id', $id)->get();

        return view('client.view-support-ticket', compact('st', 'sd', 'td', 'trply', 'department', 'ticket_file'));
    }

    /* replayTicket  Function Start Here */
    public function replayTicket(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'message' => 'required'
        ]);

        $cmd = Input::get('cmd');

        if ($v->fails()) {
            return redirect('user/tickets/view-ticket/' . $cmd)->withErrors($v->errors());
        }

        $message = Input::get('message');

        $st = SupportTickets::find($cmd);
        $cid = $st->cl_id;
        $did = $st->did;

        $cl = Client::find($cid);
        $cl_name = $cl->fname . ' ' . $cl->lname;
        $cl_email = $cl->email;

        SupportTicketsReplies::insert([
            'tid' => $cmd,
            'cl_id' => $cid,
            'admin_id' => '0',
            'name' => $cl_name,
            'date' => date('Y-m-d'),
            'message' => $message,
            'admin' => 'client',
            'image' => $cl->image,
        ]);

        $st->replyby = $cl_name;
        $st->status = 'Customer Reply';
        $st->save();

        /*For Email Confirmation*/

        $conf = EmailTemplates::where('tplname', '=', 'Client Ticket Reply Viet Nam')->first();
        $estatus = $conf->status;

        if ($estatus == '1') {
            $deprt = SupportDepartments::find($did);

            $sysEmail = $deprt->email;
            $sysDepartment = $deprt->name;
            
            $sysCompany = app_config('AppName');
            $sysUrl = url('/');

            $template = $conf->message;
            $subject = $conf->subject;

            $data = array('name' => $sysDepartment,
                'business_name' => $sysCompany,
                'ticket_id' => $cmd,
                'ticket_subject' => $subject,
                'message' => $message,
                'reply_by' => $cl_name,
                'template' => $template,
                'sys_url' => $sysUrl
            );

            $message = _render($template, $data);
            $mail_subject = _render($subject, $data);
            $body = $message;


            /*Set Authentication*/

            $default_gt = app_config('Gateway');

            if ($default_gt == 'default') {

                $mail = new \PHPMailer();
                $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                $mail->CharSet  = "utf-8";
                $mail->setFrom($cl_email, $cl_name);
                $mail->addAddress($sysEmail, $sysDepartment);     // Add a recipient
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $mail_subject;
                $mail->Body = $body;
                if (!$mail->send()) {
                    return redirect('user/tickets/view-ticket/' . $cmd)->with([
                        'message' => language_data('Ticket Reply Successfully But Email Not Send')
                    ]);
                } else {
                    return redirect('user/tickets/view-ticket/' . $cmd)->with([
                        'message' => language_data('Ticket Reply Successfully')
                    ]);
                }

            } else {
                $host = app_config('SMTPHostName');
                $smtp_username = app_config('SMTPUserName');
                $stmp_password = app_config('SMTPPassword');
                $port = app_config('SMTPPort');
                $secure = app_config('SMTPSecure');


                $mail = new \PHPMailer();

                $mail->isSMTP();   
                $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                $mail->CharSet  = "utf-8";                                   // Set mailer to use SMTP
                $mail->Host = $host;  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = $smtp_username;                 // SMTP username
                $mail->Password = $stmp_password;                           // SMTP password
                $mail->SMTPSecure = $secure;                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = $port;

                $mail->addAddress($sysEmail, $sysCompany);
                $mail->setFrom($cl_email, $cl_name);     // Add a recipient
                $mail->isHTML(true);                                  // Set email format to HTML

                $mail->Subject = $mail_subject;
                $mail->Body = $body;

                if (!$mail->send()) {
                    return redirect('user/tickets/view-ticket/' . $cmd)->with([
                        'message' => language_data('Ticket Reply Successfully But Email Not Send')
                    ]);
                } else {
                    return redirect('user/tickets/view-ticket/' . $cmd)->with([
                        'message' => language_data('Ticket Reply Successfully')
                    ]);
                }

            }
        }
        return redirect('user/tickets/view-ticket/' . $cmd)->with([
            'message' => language_data('Ticket Reply Successfully')
        ]);

    }


    /* postTicketFiles  Function Start Here */
    public function postTicketFiles(Request $request)
    {
        $cmd = Input::get('cmd');
        $v = \Validator::make($request->all(), [
            'file_title' => 'required', 'file' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('user/tickets/view-ticket/' . $cmd)->withErrors($v->errors());
        }

        $file_title = Input::get('file_title');
        $file = Input::file('file');

        if ($file != '') {
            $destinationPath = public_path() . '/assets/ticket_file/';
            $file_name = $file->getClientOriginalName();
            $file_size = $file->getSize();
            Input::file('file')->move($destinationPath, $file_name);

            $tf = new SupportTicketFiles();
            $tf->ticket_id = $cmd;
            $tf->cl_id = Auth::guard('client')->user()->id;
            $tf->admin_id = '0';
            $tf->admin = 'client';
            $tf->file_title = $file_title;
            $tf->file_size = $file_size;
            $tf->file = $file_name;
            $tf->save();

            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => language_data('File Uploaded Successfully')
            ]);

        } else {
            return redirect('user/tickets/view-ticket/' . $cmd)->with([
                'message' => language_data('Please Upload a File'),
                'message_important' => true
            ]);
        }

    }

    /* downloadTicketFile  Function Start Here */
    public function downloadTicketFile($id)
    {
        $ticket_file = SupportTicketFiles::find($id)->file;
        return response()->download(public_path('assets/ticket_file/' . $ticket_file));
    }
}
