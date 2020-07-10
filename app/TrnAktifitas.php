<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TrnAktifitas extends Model
{
    protected $table = 'transaksi_aktifitas';
	protected $primaryKey = 'ta_id';
	const CREATED_AT = 'ta_date_insert';
	const UPDATED_AT = 'ta_date_update';
	protected $fillable = [
       'ta_nik','ta_tpmd_id','ta_kcar_id','ta_date','ta_file','ta_filetumb',
    ];

}
