<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TrnSurveiKepuasan extends Model
{
    protected $table = 'transaksi_survei_kepuasan';
	protected $primaryKey = 'tsk_id';
  const CREATED_AT = 'tsk_date_insert';
  const UPDATED_AT = 'tsk_date_update';
	protected $fillable = [
       'tsk_id','tsk_username', 'tsk_rate_kp', 'tsk_rate_rek', 'tsk_ket_kp','tsk_ket_rek','tsk_kritik_saran'
    ];
}
