<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TrnDataWFHWFO extends Model
{
  protected $table = 'transaksi_wfh_wfo';
	protected $primaryKey = 'tw_id';
  const CREATED_AT = 'tw_date_insert';
  const UPDATED_AT = 'tw_date_update';
	protected $fillable = [
       'tw_id','tw_bulan', 'tw_tahun', 'tw_jml_peg_tetap', 'tw_jml_peg_kontrak','tw_jml_peg_alihdaya','tw_jml_rata_peg_masuk','tw_jns_industri',
       'tw_file_protokol_wfh','tw_file_jadwal','tw_flag_dok_protokol','tw_user_insert','tw_user_update','tw_mc_id','tw_date_file_jadwal','tw_date_file_protokol'
    ];
}
