<?php

namespace App\Http\Controllers;

use App\Client;
use App\InvoiceItems;
use App\Invoices;
use App\PaymentGateways;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('client');
    }

    //======================================================================
    // allInvoices Function Start Here
    //======================================================================
    public function allInvoices()
    {
        $invoices = Invoices::where('cl_id',Auth::guard('client')->user()->id)->get();
        return view('client.all-invoices', compact('invoices'));
    }

    //======================================================================
    // recurringInvoices Function Start Here
    //======================================================================
    public function recurringInvoices()
    {

        $invoices = Invoices::where('cl_id',Auth::guard('client')->user()->id)->where('recurring', '!=', '0')->get();
        return view('client.all-invoices', compact('invoices'));
    }


    //======================================================================
    // viewInvoice Function Start Here
    //======================================================================
    public function viewInvoice($id)
    {

        $inv = Invoices::where('cl_id',Auth::guard('client')->user()->id)->find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
            $payment_gateways=PaymentGateways::where('status','Active')->get();
			$total_docchu = ClientInvoiceController::readNumber12Digits($inv->total);
            return view('client.view-invoice', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','payment_gateways','total_docchu'));
        } else {
            return redirect('user/invoices/all')->with([
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
        $inv = Invoices::where('cl_id',Auth::guard('client')->user()->id)->find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
			$total_docchu = ClientInvoiceController::readNumber12Digits($inv->total);
            return view('client.invoice-client-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        } else {
            return redirect('user/invoices/all')->with([
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
        $inv = Invoices::where('cl_id',Auth::guard('client')->user()->id)->find($id);
        if ($inv) {
            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
			$total_docchu = ClientInvoiceController::readNumber12Digits($inv->total);
            return view('client.invoice-print-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
        } else {
            return redirect('user/invoices/all')->with([
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
        $inv = Invoices::where('cl_id',Auth::guard('client')->user()->id)->find($id);

        if ($inv){

            $client = Client::where('status', 'Active')->find($inv->cl_id);
            $inv_items = InvoiceItems::where('inv_id', $id)->get();
            $tax_sum = InvoiceItems::where('inv_id', $id)->sum('tax');
            $dis_sum = InvoiceItems::where('inv_id', $id)->sum('discount');
			$total_docchu = ClientInvoiceController::readNumber12Digits($inv->total);
            $data = view('client.invoice-print-view', compact('client', 'inv', 'inv_items', 'tax_sum', 'dis_sum','total_docchu'));
            $html = $data->render();
            $pdf = \App::make('snappy.pdf.wrapper');
            $pdf->loadHTML($html)->setPaper('a4')->setOption('margin-bottom', 0);
            return $pdf->download('invoice.pdf');
        } else {
            return redirect('user/invoices/all')->with([
                'message' => language_data('Invoice not found'),
                'message_important' => true
            ]);
        }

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
			return $negative . ClientInvoiceController::convertNumber2WordsVN( abs( $number ) );
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
						$result[$key] = ClientInvoiceController::readNumber3Digits($value, $dictionnaryNumbers, $readFull) . " " . $dictionnaryUnits[$key];
				}
		}
		$result = implode(" ", $result);
		$result = ClientInvoiceController::formatString($result);
	
	// 		$result = str_replace("không đồng", "đồng", $result);
	// 		$result = str_replace("không trăm đồng", "đồng", $result);
	// 		$result = str_replace("không nghìn đồng", "đồng", $result);
	// 		$result = str_replace("không trăm nghìn đồng", "đồng", $result);
		$result = str_replace("triệu nghìn đồng", "triệu đồng", $result);
		$result = str_replace("tỷ triệu đồng", "tỷ đồng", $result).".";
		return $result;
	}


	
	

}
