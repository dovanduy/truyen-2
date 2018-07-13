<?php

namespace App\Http\Controllers;

use App\Classes\Permission;
use App\SMSHistory;
use App\SMSInbox;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ScheduleSMS;
class ReportsController extends Controller
{

    /**
     * ReportsController constructor.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    //======================================================================
    // smsHistory Function Start Here
    //======================================================================
    public function smsHistory()
    {

        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $sms_history = SMSHistory::orderBy('updated_at', 'desc')->get();
        return view('admin.sms-history', compact('sms_history'));
    }

    //======================================================================
    // smsViewInbox Function Start Here
    //======================================================================
    public function smsViewInbox($id)
    {

        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $inbox_info = SMSHistory::find($id);

        if ($inbox_info) {
            $sms_inbox = SMSInbox::where('msg_id', $id)->get();
            return view('admin.sms-inbox', compact('sms_inbox', 'inbox_info'));
        } else {
            return redirect('sms/history')->with([
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

        $self = 'sms-history';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }

        $inbox_info = SMSHistory::find($id);

        if ($inbox_info) {
            SMSInbox::where('msg_id', $id)->delete();
            $inbox_info->delete();

            return redirect('sms/history')->with([
                'message' => language_data('SMS info deleted successfully'),
            ]);

        } else {
            return redirect('sms/history')->with([
                'message'           => language_data('SMS Not Found'),
                'message_important' => true,
            ]);
        }

    }

    public function smsHistoryFile(Request $request)
    {
        $self = 'sms-history-file';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
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

        if ($request->has('created_date')) {
            $createdDate = $request->created_date;
            $createdDate = date('Y-m-d', strtotime($createdDate));
            $sql .= " and i.created_at like '" . $createdDate . "%' ";
        }

        if($request->has('gui_mot_tin_lap_lich')){
            $sql .= " and i.file_send='" . $request->gui_mot_tin_lap_lich . "'";
        }


        if($request->has('gui_theo_danh_ba')){
            $sql .= " and i.file_send='" . $request->gui_theo_danh_ba . "'";
        }

        if($request->has('gui_tin_cho_nhom_kh')){
            $sql .= " and i.file_send='" . $request->gui_tin_cho_nhom_kh . "'";
        }

        if($request->has('gui_hang_loat_tu_file')){
            $sql .= " and i.file_send='" . $request->gui_hang_loat_tu_file . "'";
        }

        if($request->has('gui_tin_sinh_nhat')){
            $sql .= " and i.file_send='" . $request->gui_tin_sinh_nhat. "'";
        }

        if($request->has('gui_tin_nhac_nho')){
            $sql .= " and i.file_send='" . $request->gui_tin_nhac_nho. "'";
        }

        if($request->has('gui_tin_lap_lich_cho_nhom_kh')){
            $sql .= " and i.file_send='" . $request->gui_tin_lap_lich_cho_nhom_kh. "'";
        }

        if($request->has('lap_lich_gui_tu_file')){
            $sql .= " and i.file_send='" . $request->lap_lich_gui_tu_file. "'";
        }

        $sql = "select h.id,h.userid,h.receiver,h.sender,i.original_msg,i.`status`,i.created_at,i.send_by,i.file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id " . $sql;
        $sql .= " order by i.id desc";
        $sms_history = DB::select($sql);

        //gui 1 tin lap lich
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-mot-tin-lap-lich-%'";
        $sql .= " order by i.id desc";
        $gui_mot_tin_lap_lich = DB::select($sql);

        //gui-theo-danh-ba
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-theo-danh-ba-%'";
        $sql .= " order by i.id desc";
        $gui_theo_danh_ba = DB::select($sql);

        //gui_tin_cho_nhom_kh
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-nhom-kh-%'";
        $sql .= " order by i.id desc";
        $gui_tin_cho_nhom_kh = DB::select($sql);

        //gui-hang-loat-tu-file-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-hang-loat-tu-file-%'";
        $sql .= " order by i.id desc";
        $gui_hang_loat_tu_file = DB::select($sql);

        //gui-tin-sinh-nhat-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-sinh-nhat-%'";
        $sql .= " order by i.id desc";
        $gui_tin_sinh_nhat = DB::select($sql);

        ////gui tin nhac nho
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-nhac-nho-%'";
        $sql .= " order by i.id desc";
        $gui_tin_nhac_nho = DB::select($sql);

        //gui-tin-lap-lich-nhom-kh-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'gui-tin-lap-lich-nhom-kh-%'";
        $sql .= " order by i.id desc";
        $gui_tin_lap_lich_cho_nhom_kh = DB::select($sql);

        //lap-lich-gui-tu-file-
        $sql = "select DISTINCT file_send from sys_sms_history h,sys_sms_inbox i where h.id=i.msg_id and i.file_send like 'lap-lich-gui-tu-file-%'";
        $sql .= " order by i.id desc";
        $lap_lich_gui_tu_file = DB::select($sql);

        return view('admin.sms-history-file', compact('sms_history','fileSendList','gui_mot_tin_lap_lich','gui_tin_sinh_nhat','gui_tin_nhac_nho','gui_tin_lap_lich_cho_nhom_kh','lap_lich_gui_tu_file','gui_theo_danh_ba','gui_tin_cho_nhom_kh','gui_hang_loat_tu_file'));
    }

    public function findMobile(Request $request)
    {
        $smsHistory = SMSHistory::where('receiver', 'like', '%' . $request->get('q') . '%')->take(5)->get();
        return response()->json($smsHistory);
    }

    public function findMobileSchedule(Request $request)
    {
        $smsHistory = ScheduleSMS::where('receiver', 'like', '%' . $request->get('q') . '%')->take(5)->get();
        return response()->json($smsHistory);
    }

    public function smsHistoryFileSchedule(Request $request)
    {
        $self = 'sms-history-file-schedule';
        if (Auth::user()->username !== 'admin') {
            $get_perm = Permission::permitted($self);

            if ($get_perm == 'access denied') {
                return redirect('permission-error')->with([
                    'message'           => language_data('You do not have permission to view this page'),
                    'message_important' => true,
                ]);
            }
        }
        $sql = " where 1=1 ";
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
        if($request->has('gui_mot_tin_lap_lich')){
            $sql .= " and s.file_send='" . $request->gui_mot_tin_lap_lich . "'";
        }

        if($request->has('gui_tin_lap_lich_cho_nhom_kh')){
            $sql .= " and s.file_send='" . $request->gui_tin_lap_lich_cho_nhom_kh. "'";
        }

        if($request->has('gui_tin_sinh_nhat')){
            $sql .= " and s.file_send='" . $request->gui_tin_sinh_nhat. "'";
        }

        if($request->has('gui_tin_nhac_nho')){
            $sql .= " and s.file_send='" . $request->gui_tin_nhac_nho. "'";
        }

        if($request->has('lap_lich_gui_tu_file')){
            $sql .= " and s.file_send='" . $request->lap_lich_gui_tu_file. "'";
        }

        $sql = "select * from sys_schedule_sms as s" . $sql;
        $sql .= " order by id desc";
        //echo $sql;
        $sms_history  = DB::select($sql);

        //gui 1 tin lap lich
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-mot-tin-lap-lich-%'";
        $sql .= " order by id desc";
        $gui_mot_tin_lap_lich = DB::select($sql);

        //gui-tin-lap-lich-cho-nhom-khach-hang
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-tin-lap-lich-nhom-kh-%'";
        $sql .= " order by id desc";
        $gui_tin_lap_lich_cho_nhom_kh = DB::select($sql);

        //gui-tin-sinh-nhat-
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-tin-sinh-nhat-%'";
        $sql .= " order by id desc";
        $gui_tin_sinh_nhat = DB::select($sql);

        //gui tin nhac nho
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'gui-tin-nhac-nho-%'";
        $sql .= " order by id desc";
        $gui_tin_nhac_nho = DB::select($sql);

        //lap-lich-gui-tu-file-
        $sql = "select DISTINCT file_send from sys_schedule_sms as s where s.file_send like 'lap-lich-gui-tu-file-%'";
        $sql .= " order by id desc";
        $lap_lich_gui_tu_file = DB::select($sql);

        //get all file
        //$sql          = "select DISTINCT file_send from sys_schedule_sms";
        //$fileSendList = DB::select($sql);
        return view('admin.sms-history-file-schedule', compact('sms_history', 'fileSendList','gui_mot_tin_lap_lich','gui_tin_sinh_nhat','gui_tin_nhac_nho','gui_tin_lap_lich_cho_nhom_kh','lap_lich_gui_tu_file'));
    }

}
