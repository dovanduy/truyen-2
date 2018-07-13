<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMSHistory extends Model
{
    protected $table='sys_sms_history';
    protected $fillable=['userid','sender','receiver','use_gateway','api_key'];
}
