<?php

namespace App\Http\Controllers;

use App\Classes\Permission;
use App\Client;
use App\InvoiceItems;
use App\Invoices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // allInvoices Function Start Here
    //======================================================================
    public function allInvoices()
    {
        $self = 'all-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $invoices = Invoices::all();
        return view('admin.all-invoices', compact('invoices'));
    }

    //======================================================================
    // recurringInvoices Function Start Here
    //======================================================================
    public function recurringInvoices()
    {
        $self = 'recurring-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $invoices = Invoices::where('recurring', '!=', '0')->get();
        return view('admin.all-invoices', compact('invoices'));
    }

    //======================================================================
    // addInvoice Function Start Here
    //======================================================================
    public function addInvoice()
    {
        $self = 'add-new-invoice';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $clients = Client::where('status', 'Active')->get();
        return view('admin.add-new-invoice', compact('clients'));
    }

    //======================================================================
    // postInvoice Function Start Here
    //======================================================================
    public function postInvoice(Request $request)
    {
        $self = 'add-new-invoice';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $v = \Validator::make($request->all(), ['client_id' => 'required', 'invoice_date' => 'required', 'invoice_type' => 'required']);
        if ($v->fails()) {
            return redirect('invoices/add')->withErrors($v->errors());
        }
        $cid = Input::get('client_id');
        $notes = Input::get('notes');
        $amount = Input::get('amount');
        $idate = Input::get('invoice_date');
        $invoice_type = Input::get('invoice_type');

        if ($invoice_type == 'recurring') {
            $pdate = Input::get('paid_date_recurring');
        } else {
            $pdate = Input::get('paid_date');
            $ddate = Input::get('due_date');
        }

        if ($cid == '') {
            return redirect('invoices/add')->with(array('message' => language_data('Select a Customer'), 'message_important' => true));
        }
        if ($idate == '') {
            return redirect('invoices/add')->with(array('message' => language_data('Invoice Created date is required'), 'message_important' => true));
        }
        if ($pdate == '') {
            return redirect('invoices/add')->with(array('message' => language_data('Invoice Paid date is required'), 'message_important' => true));
        }
        if ($amount == '') {
            return redirect('invoices/add')->with(array('message' => language_data('At least one item is required'), 'message_important' => true));
        }
        $qty = Input::get('qty');
        $sTotal = '0';
        $i = '0';
        foreach ($amount as $samount) {
            $amount[$i] = $samount;
            $sTotal += $samount * ($qty[$i]);
            $i++;
        }
        $ltotal = Input::get('ltotal');
        $pTotal = '0';
        $x = '0';
        foreach ($ltotal as $lt) {
            $ltotal[$x] = $lt;
            $pTotal += $lt;
            $x++;
        }

        $nd = $pdate;

        if ($invoice_type == 'recurring') {

            $repeat = Input::get('repeat_type');
            $its = strtotime($idate);

            if ($repeat == 'week1') {
                $r = '+1 week';
                $nd = date('Y-m-d', strtotime('+1 week', $its));
            } elseif ($repeat == 'weeks2') {
                $r = '+2 weeks';
                $nd = date('Y-m-d', strtotime('+2 weeks', $its));
            } elseif ($repeat == 'month1') {
                $r = '+1 month';
                $nd = date('Y-m-d', strtotime('+1 month', $its));
            } elseif ($repeat == 'months2') {
                $r = '+2 months';
                $nd = date('Y-m-d', strtotime('+2 months', $its));
            } elseif ($repeat == 'months3') {
                $r = '+3 months';
                $nd = date('Y-m-d', strtotime('+3 months', $its));
            } elseif ($repeat == 'months6') {
                $r = '+6 months';
                $nd = date('Y-m-d', strtotime('+6 months', $its));
            } elseif ($repeat == 'year1') {
                $r = '+1 year';
                $nd = date('Y-m-d', strtotime('+1 year', $its));
            } elseif ($repeat == 'years2') {
                $r = '+2 years';
                $nd = date('Y-m-d', strtotime('+2 years', $its));
            } elseif ($repeat == 'years3') {
                $r = '+3 years';
                $nd = date('Y-m-d', strtotime('+3 years', $its));
            } else {
                return redirect('invoices/add')->with(array('message' => language_data('Date Parsing Error'), 'message_important' => true));
            }
            $ddate = $nd;
            $bill_created = 'no';
        } else {
            $r = '0';
            $bill_created = 'yes';
        }

        if ($ddate == '') {
            return redirect('invoices/add')->with(array('message' => language_data('Invoice Due date is required'), 'message_important' => true));
        }

        $cl = Client::find($cid);
        $cl_name = $cl['fname'] . ' ' . $cl['lname'];
        $cl_email = $cl->email;
        $inv = new Invoices();
        $inv->cl_id = $cid;
        $inv->client_name = $cl_name;
        $inv->created_by = Auth::user()->id;
        $inv->created = $idate;
        $inv->duedate = $ddate;
        $inv->datepaid = $nd;
        $inv->subtotal = $sTotal;
        $inv->total = $pTotal;
        $inv->status = 'Unpaid';
        $inv->pmethod = '';
        $inv->recurring = $r;
        $inv->bill_created = $bill_created;
        $inv->note = $notes;
        $inv->save();
        $inv_id = $inv->id;
        $tax = Input::get('taxed');
        $discount = Input::get('discount');
        $description = Input::get('desc');
        $i = '0';
        foreach ($description as $item) {
            $ltotal = ($amount[$i]) * ($qty[$i]);
            $ttotal = ($ltotal * $tax[$i]) / 100;
            $dtotal = ($ltotal * $discount[$i]) / 100;
            $fTotal = $ltotal + $ttotal - $dtotal;
            $d = new InvoiceItems();
            $d->inv_id = $inv_id;
            $d->cl_id = $cid;
            $d->item = $item;
            $d->qty = $qty[$i];
            $d->price = $amount[$i];
            $d->tax = $ttotal;
            $d->discount = $dtotal;
            $d->subtotal = $ltotal;
            $d->total = $fTotal;
            $d->save();
            $i++;
        }

        return redirect('invoices/view/' . $inv_id)->with(['message' => language_data('Invoice Created Successfully')]);
    }

    //======================================================================
    // viewInvoice Function Start Here
    //======================================================================
    public function viewInvoice($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
			$total_docchu = InvoiceController::readNumber12Digits($inv->total);
            return view('admin.view-invoice', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }

    }

    //======================================================================
    // editInvoice Function Start Here
    //======================================================================
    public function editInvoice($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            return view('admin.edit-invoice', compact('client', 'inv', 'inv_items'));
        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }


    //======================================================================
    // postEditInvoice Function Start Here
    //======================================================================
    public function postEditInvoice(Request $request)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $id = Input::get('cmd');

        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('invoices/edit/' . $id)->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $v = \Validator::make($request->all(), ['invoice_date' => 'required', 'invoice_type' => 'required']);
        if ($v->fails()) {
            return redirect('invoices/edit/' . $id)->withErrors($v->errors());
        }
        $cid = Input::get('client_id');
        $notes = Input::get('notes');
        $amount = Input::get('amount');
        $idate = Input::get('invoice_date');
        $invoice_type = Input::get('invoice_type');

        if ($invoice_type == 'recurring') {
            $pdate = Input::get('paid_date_recurring');
        } else {
            $pdate = Input::get('paid_date');
            $ddate = Input::get('due_date');
        }

        if ($cid == '') {
            return redirect('invoices/edit/' . $id)->with(array('message' => language_data('Select a Customer'), 'message_important' => true));
        }
        if ($idate == '') {
            return redirect('invoices/edit/' . $id)->with(array('message' => language_data('Invoice Created date is required'), 'message_important' => true));
        }

        if ($pdate == '') {
            return redirect('invoices/edit/' . $id)->with(array('message' => language_data('Invoice Paid date is required'), 'message_important' => true));
        }
        if ($amount == '') {
            return redirect('invoices/edit/' . $id)->with(array('message' => language_data('At least one item is required'), 'message_important' => true));
        }
        $qty = Input::get('qty');
        $sTotal = '0';
        $i = '0';
        foreach ($amount as $samount) {
            $amount[$i] = $samount;
            $sTotal += $samount * ($qty[$i]);
            $i++;
        }
        $ltotal = Input::get('ltotal');
        $pTotal = '0';
        $x = '0';
        foreach ($ltotal as $lt) {
            $ltotal[$x] = $lt;
            $pTotal += $lt;
            $x++;
        }

        $nd = $pdate;

        if ($invoice_type == 'recurring') {
            $repeat = Input::get('repeat_type');
            $its = strtotime($idate);

            if ($repeat == 'week1') {
                $r = '+1 week';
                $nd = date('Y-m-d', strtotime('+1 week', $its));
            } elseif ($repeat == 'weeks2') {
                $r = '+2 weeks';
                $nd = date('Y-m-d', strtotime('+2 weeks', $its));
            } elseif ($repeat == 'month1') {
                $r = '+1 month';
                $nd = date('Y-m-d', strtotime('+1 month', $its));
            } elseif ($repeat == 'months2') {
                $r = '+2 months';
                $nd = date('Y-m-d', strtotime('+2 months', $its));
            } elseif ($repeat == 'months3') {
                $r = '+3 months';
                $nd = date('Y-m-d', strtotime('+3 months', $its));
            } elseif ($repeat == 'months6') {
                $r = '+6 months';
                $nd = date('Y-m-d', strtotime('+6 months', $its));
            } elseif ($repeat == 'year1') {
                $r = '+1 year';
                $nd = date('Y-m-d', strtotime('+1 year', $its));
            } elseif ($repeat == 'years2') {
                $r = '+2 years';
                $nd = date('Y-m-d', strtotime('+2 years', $its));
            } elseif ($repeat == 'years3') {
                $r = '+3 years';
                $nd = date('Y-m-d', strtotime('+3 years', $its));
            } else {
                return redirect('invoices/add')->with(array('message' => language_data('Date Parsing Error'), 'message_important' => true));
            }
            $ddate = $nd;
        } else {
            $r = '0';
        }

        if ($ddate == '') {
            return redirect('invoices/edit/' . $id)->with(array('message' => language_data('Invoice Due date is required'), 'message_important' => true));
        }

        $invoice = Invoices::find($id);

        if ($invoice) {
            $invoice->created = $idate;
            $invoice->duedate = $ddate;
            $invoice->subtotal = $sTotal;
            $invoice->total = $pTotal;
            $invoice->datepaid = $nd;
            $invoice->recurring = $r;
            $invoice->note = $notes;
            $invoice->save();
        } else {
            return redirect('invoices/edit/' . $id)->with([
                'message' => language_data('Invoice not found'),
                'message_true' => true
            ]);
        }
        $tax = Input::get('taxed');
        $discount = Input::get('discount');
        $description = Input::get('desc');
        InvoiceItems::where('inv_id', $id)->delete();
        $i = '0';
        foreach ($description as $item) {
            $ltotal = ($amount[$i]) * ($qty[$i]);
            $ttotal = ($ltotal * $tax[$i]) / 100;
            $dtotal = ($ltotal * $discount[$i]) / 100;
            $fTotal = $ltotal + $ttotal - $dtotal;
            $d = new InvoiceItems();
            $d->inv_id = $id;
            $d->cl_id = $cid;
            $d->item = $item;
            $d->qty = $qty[$i];
            $d->price = $amount[$i];
            $d->tax = $ttotal;
            $d->discount = $dtotal;
            $d->subtotal = $ltotal;
            $d->total = $fTotal;
            $d->save();
            $i++;
        }
        return redirect('invoices/edit/' . $id)->with([
            'message' => language_data('Invoice Updated Successfully')
        ]);
    }

    //======================================================================
    // markInvoicePaid Function Start Here
    //======================================================================
    public function markInvoicePaid($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $invoice = Invoices::find($id);
        if ($invoice) {
            $invoice->status = 'Paid';
            $invoice->datepaid = date('Y-m-d');
            $invoice->save();

            return redirect('invoices/view/' . $id)->with([
                'message' => language_data('Invoice Marked as Paid')
            ]);

        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // markInvoiceUnpaid Function Start Here
    //======================================================================
    public function markInvoiceUnpaid($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $invoice = Invoices::find($id);
        if ($invoice) {
            $invoice->status = 'Unpaid';
            $invoice->save();

            return redirect('invoices/view/' . $id)->with([
                'message' => language_data('Invoice Marked as Unpaid')
            ]);

        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // markInvoicePartiallyPaid Function Start Here
    //======================================================================
    public function markInvoicePartiallyPaid($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $invoice = Invoices::find($id);
        if ($invoice) {
            $invoice->status = 'Partially Paid';
            $invoice->save();

            return redirect('invoices/view/' . $id)->with([
                'message' => language_data('Invoice Marked as Partially Paid')
            ]);

        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // markInvoiceCancelled Function Start Here
    //======================================================================
    public function markInvoiceCancelled($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $invoice = Invoices::find($id);
        if ($invoice) {
            $invoice->status = 'Cancelled';
            $invoice->save();

            return redirect('invoices/view/' . $id)->with([
                'message' => language_data('Invoice Marked as Cancelled')
            ]);

        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // clientIView Function Start Here
    //======================================================================
    public function clientIView($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
			$total_docchu = InvoiceController::readNumber12Digits($inv->total);
            return view('admin.invoice-client-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // printView Function Start Here
    //======================================================================
    public function printView($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
			$total_docchu = InvoiceController::readNumber12Digits($inv->total);
            return view('admin.invoice-print-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // downloadPdf Function Start Here
    //======================================================================
    public function downloadPdf($id)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        $client = Client::where('status', 'Active')->find($inv->cl_id);
        $inv_items = InvoiceItems::where('inv_id', $id)->get();
        $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
        $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
		$total_docchu = InvoiceController::readNumber12Digits($inv->total);
        $data = view('admin.invoice-print-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        $html = $data->render();
        $pdf = \App::make('snappy.pdf.wrapper');
        $pdf->loadHTML($html)->setPaper('a4')->setOption('margin-bottom', 0);
        return $pdf->download('invoice.pdf');
    }

    //======================================================================
    // sendInvoiceEmail Function Start Here
    //======================================================================
    public function sendInvoiceEmail(Request $request)
    {

        $self = 'manage-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $id = Input::get('cmd');

        $v = \Validator::make($request->all(), [
            'subject' => 'required'
        ]);

        if ($v->fails()) {
            return redirect('invoices/view/' . $id)->withErrors($v->errors());
        }
        $inv = Invoices::find($id);
        $client = Client::where('status', 'Active')->find($inv->cl_id);
        $inv_items = InvoiceItems::where('inv_id', $id)->get();
        $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
        $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
		$total_docchu = InvoiceController::readNumber12Digits($inv->total);
        $data = view('admin.invoice-print-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        $html = $data->render();
        $file_path = public_path('assets/invoice_file/Invoice_' . time() . '.pdf');
        $pdf = \App::make('snappy.pdf.wrapper');
        $pdf->loadHTML($html)->setPaper('a4')->setOption('margin-bottom', 0)->save($file_path);


        $sysEmail = app_config('Email');
        $sysCompany = app_config('AppName');

        $template = $request->message;
        $subject = $request->subject;
        $client_name = $client->fname .' '. $client->lname;

        $default_gt = app_config('Gateway');

        if ($default_gt == 'default') {

            $mail = new \PHPMailer();

            try {
                $mail->setFrom($sysEmail, $sysCompany);
                $mail->addAddress($client->email, $client_name);     // Add a recipient
                $mail->addAttachment($file_path);
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $subject;
                $mail->Body = $template;

                if (!$mail->send()) {
                    return redirect('invoices/view/'.$id)->with([
                        'message' => 'Please Check your Email Settings',
                        'message_important'=>true
                    ]);
                } else {
                    return redirect('invoices/view/'.$id)->with([
                        'message' => language_data('Invoice Send Successfully')
                    ]);
                }

            } catch (\phpmailerException $e) {
                return redirect('invoices/view/'.$id)->with([
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
                $mail->addAddress($client->email, $client_name);     // Add a recipient
                $mail->addAttachment($file_path);

                $mail->isHTML(true);                                  // Set email format to HTML

                $mail->Subject = $subject;
                $mail->Body = $template;

                if (!$mail->send()) {
                    return redirect('invoices/view/'.$id)->with([
                        'message' => language_data('Please Check your Email Settings'),
                        'message_important'=>true
                    ]);
                } else {
                    return redirect('invoices/view/'.$id)->with([
                        'message' => language_data('Invoice Send Successfully')
                    ]);
                }

            } catch (\phpmailerException $e) {
                return redirect('invoices/view/'.$id)->with([
                    'message' => $e->getMessage(),
                    'message_important' => true
                ]);
            }
        }
    }


    //======================================================================
    // deleteInvoice Function Start Here
    //======================================================================
    public function deleteInvoice($id)
    {
        $appStage = app_config('AppStage');
        if ($appStage == 'Demo') {
            return redirect('invoices/all')->with([
                'message' => language_data('This Option is Disable In Demo Mode'),
                'message_important' => true
            ]);
        }

        $self = 'all-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        if ($inv) {
            InvoiceItems::where('inv_id', $id)->delete();
            $inv->delete();

            return redirect('invoices/all')->with([
                'message' => language_data('Invoice deleted successfully'),
            ]);

        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

    //======================================================================
    // stopRecurringInvoice Function Start Here
    //======================================================================
    public function stopRecurringInvoice($id)
    {

        $self = 'recurring-invoices';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message' => language_data('You do not have permission to view this page'),
                    'message_important' => true
                ]);
            }
        }

        $inv = Invoices::find($id);
        if ($inv) {
            $inv->recurring = '0';
            $inv->save();

            return redirect('invoices/all')->with([
                'message' => language_data('Stop Recurring Invoice Successfully'),
            ]);

        } else {
            return redirect('invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }
    }

	public function convertNumber2WordsVN( $number )
	{
		$hyphen = ' ';
		$conjunction = '  ';
		$separator = ' ';
		$negative = 'âm ';
		$decimal = ' phẩy ';
		$dictionary = array(
			0 => 'Không',
			1 => 'Một',
			2 => 'Hai',
			3 => 'Ba',
			4 => 'Bốn',
			5 => 'Năm',
			6 => 'Sáu',
			7 => 'Bảy',
			8 => 'Tám',
			9 => 'Chín',
			10 => 'Mười',
			11 => 'Mười Một',
			12 => 'Mười Hai',
			13 => 'Mười Ba',
			14 => 'Mười Bốn',
			15 => 'Mười Năm',
			16 => 'Mười Sáu',
			17 => 'Mười Bảy',
			18 => 'Mười Tám',
			19 => 'Mười Chín',
			20 => 'Hai Mươi',
			30 => 'Ba Mươi',
			40 => 'Bốn Mươi',
			50 => 'Năm Mươi',
			60 => 'Sáu Mươi',
			70 => 'Bảy Mươi',
			80 => 'Tám Mươi',
			90 => 'Chín Mươi',
			100 => 'Trăm',
			1000 => 'Nghìn',
			1000000 => 'Triệu',
			1000000000 => 'Tỷ',
			1000000000000 => 'Nghìn Tỷ',
			1000000000000000 => 'Nghìn Triệu Triệu',
			1000000000000000000 => 'Tỷ Tỷ'
		);

		if( !is_numeric( $number ) )
		{
			return false;
		}

		if( ($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX )
		{
			// overflow
			trigger_error( 'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING );
			return false;
		}

		if( $number < 0 )
		{
			return $negative . InvoiceController::convertNumber2WordsVN( abs( $number ) );
		}

		$string = $fraction = null;

		if( strpos( $number, '.' ) !== false )
		{
			list( $number, $fraction ) = explode( '.', $number );
		}

		switch (true)
		{
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens = ((int)($number / 10)) * 10;
				$units = $number % 10;
				$string = $dictionary[$tens];
				if( $units )
				{
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if( $remainder )
				{
					$string .= $conjunction . InvoiceController::convertNumber2WordsVN( $remainder );
				}
				break;
			default:
				$baseUnit = pow( 1000, floor( log( $number, 1000 ) ) );
				$numBaseUnits = (int)($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = InvoiceController::convertNumber2WordsVN( $numBaseUnits ) . ' ' . $dictionary[$baseUnit];
				if( $remainder )
				{
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= InvoiceController::convertNumber2WordsVN( $remainder );
				}
				break;
		}

		if( null !== $fraction && is_numeric( $fraction ) )
		{
			$string .= $decimal;
			$words = array( );
			foreach( str_split((string) $fraction) as $number )
			{
				$words[] = $dictionary[$number];
			}
			$string .= implode( ' ', $words );
		}

		return $string;
	}

//huyhh
	public function readNumber3Digits($number, $dictionnaryNumber, $readFull = true){
		
	    	// 01 - LẤY CHỮ SỐ HÀNG TRĂM, HÀNG CHỤC, HÀNG ĐƠN VỊ
	    	$number 	= strval($number);
		    $number 	= str_pad($number, 3, 0, STR_PAD_LEFT);
		    $digit_0 	= substr($number, 2, 1);
		    $digit_00 	= substr($number, 1, 1);
		    $digit_000 	= substr($number, 0, 1);
			
		    // 02 - HÀNG TRĂM
	    	$str_000 = $dictionnaryNumber[$digit_000] . " trăm ";
			
	    	// 03 - HÀNG CHỤC
	    	$str_00 = $dictionnaryNumber[$digit_00] . " mươi ";
	    	if($digit_00 == 0) $str_00 = " linh ";
	    if($digit_00 == 1) $str_00 = " mười ";
			
		    // 04 - HÀNG ĐƠN VỊ
	    	$str_0 = $dictionnaryNumber[$digit_0];
	    	if($digit_00 > 1 && $digit_0 == 1) $str_0 = " mốt ";
	    	if($digit_00 > 0 && $digit_0 == 5) $str_0 = " lăm ";
	    	if($digit_00 == 0 && $digit_0 == 0){
	        		$str_0 	= "";
	        		$str_00 = "";
	    	}
		
	    	if($digit_0 == 0){
	        		$str_0 	= "";
	    	}
		
		    if($readFull == false){
	        		if($digit_000 == 0) $str_000 = "";
			        if($digit_000 == 0 && $digit_00 == 0) $str_00 = "";
		    }
		
	    	$result = $str_000 . $str_00 . $str_0;
		
	    	return $result;
	}

	public function formatString($str, $type = null){
	    	// Dua tat ca cac ky tu ve dang chu thuong
		    $str	= strtolower($str);

		    // Loai bo khoang trang dau va cuoi chuoi
		    $str	= trim($str);

	    	// Loai bo khoang trang du thua giua cac tu

		    $array 	= explode(" ", $str);
	    	foreach($array as $key => $value){
			        if(trim($value) == null) {
	        			    unset($array[$key]);
	            			continue;
			        }
				
			        // Xu ly cho danh tu
			        if($type=="danh-tu") {
	            			$array[$key] = ucfirst($value);
	    		    }
		    }

	    	$result = implode(" ", $array);

		    // Chuyen ky tu dau tien thanh chu hoa
		    $result	= ucfirst($result);

		    return $result;
	}

	public function readNumber12Digits($number){

	$dictionnaryNumbers 	= array(
		0 => "không",
		1 => "một",
		2 => "hai",
		3 => "ba",
		4 => "bốn",
		5 => "năm",
		6 => "sáu",
		7 => "bẩy",
		8 => "tám",
		9 => "chín",
	);

	$dictionnaryUnits 	= array(
		0 => "tỷ",		
		1 => "triệu",		
		2 => "nghìn",		
		3 => "đồng",		
	);

		if( !is_numeric( $number ) )
		{
			return false;
		}

		if( ($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX )
		{
			// overflow
			trigger_error( 'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING );
			return false;
		}

		if( $number < 0 )
		{
			return $negative . InvoiceController::convertNumber2WordsVN( abs( $number ) );
		}

		$string = $fraction = null;

		if( strpos( $number, '.' ) !== false )
		{
			list( $number, $fraction ) = explode( '.', $number );
		}
		
		$number 	= strval($number);
		$number 	= str_pad($number, 12, 0, STR_PAD_LEFT);
		$arrNumber 	= str_split($number, 3);
	
		foreach($arrNumber as $key => $value){
				if($value != "000"){
						$index = $key;
						break;
				}
		}
		foreach($arrNumber as $key => $value){
				if($key >= $index){
						$readFull = true;
						if($key >= $index) $readFull = false;
						$result[$key] = InvoiceController::readNumber3Digits($value, $dictionnaryNumbers, $readFull) . " " . $dictionnaryUnits[$key];
				}
		}
		$result = implode(" ", $result);
		$result = InvoiceController::formatString($result);
	
	// 		$result = str_replace("không đồng", "đồng", $result);
	// 		$result = str_replace("không trăm đồng", "đồng", $result);
	// 		$result = str_replace("không nghìn đồng", "đồng", $result);
	// 		$result = str_replace("không trăm nghìn đồng", "đồng", $result);
		$result = str_replace("triệu nghìn đồng", "triệu đồng", $result);
		$result = str_replace("tỷ triệu đồng", "tỷ đồng", $result).".";
		return $result;
	}

	
}