<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreBulkSMS extends Model
{
    protected $table='sys_bulk_sms';
    protected $fillable=['userid','sender','receiver','amount','message','use_gateway','file_send'];
}
