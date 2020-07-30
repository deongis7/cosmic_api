<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TrnAktifitasFile extends Model
{
    protected $table = 'transaksi_aktifitas_file';
	protected $primaryKey = 'taf_id';
	const CREATED_AT = 'taf_date_insert';
	const UPDATED_AT = 'taf_date_update';
	protected $fillable = [
       'taf_ta_id','taf_date','taf_file','taf_file_tumb'
    ];

}
