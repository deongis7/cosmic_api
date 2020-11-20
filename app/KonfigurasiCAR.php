<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class KonfigurasiCAR extends Model
{
    protected $table = 'konfigurasi_car';
	protected $primaryKey = 'kcar_id';
	const CREATED_AT = 'kcar_date_insert';
	const UPDATED_AT = 'kcar_date_update';
	protected $fillable = [
       'kcar_ag_id','kcar_mcr_id','kcar_mcar_id'
    ];
}
