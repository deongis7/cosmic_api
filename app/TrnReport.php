<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TrnReport extends Model
{
    protected $table = 'transaksi_report';
	protected $primaryKey = 'tr_id';
  const CREATED_AT = 'tr_date_insert';
  const UPDATED_AT = 'tr_date_update';
	protected $fillable = [
       'tr_id','tr_mpml_id', 'tr_laporan', 'tr_file1', 't_file2','t_no','tr_user_update','tr_user_insert','tr_tl_file1','tr_tl_file2','tr_close'
    ];
}
