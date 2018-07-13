<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleSMS extends Model
{
    protected $table='sys_schedule_sms';
    protected $fillable=['userid','sender','receiver','amount','original_msg','encrypt_msg','status','use_gateway','submit_time','ip','file_send'];
}
