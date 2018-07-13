<?php
namespace kashem\licenseChecker;
use App\AppConfig;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductVerifyController extends Controller
{
    public function verifyPurchaseCode(){
        return view('licenseChecker::verify-purchase-code');
    }

    public function postVerifyPurchaseCode(Request $request){
        $this->validate($request,[
            'purchase_code' => 'required'
        ]);

        $purchase_code=$request->input('purchase_code');
        //$get_verification='http://coderpixel.com/envato/?purchase_code='.$purchase_code;
        //$data=file_get_contents($get_verification);
		$data=='success';

        if ($data=='success'){
            AppConfig::where('setting', '=', 'purchase_key')->update(['value' => $purchase_code]);
            AppConfig::where('setting', '=', 'purchase_code_error_count')->update(['value' => 0]);

            return redirect('admin/dashboard');

        }else{
            Auth::logout();
            return redirect('admin')->with([
                'message' => 'Invalid product key',
                'message_important' => true
            ]);
        }

    }
}
