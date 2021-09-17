<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class PerimeterPedulilindungi extends Model
{
    protected $table = 'master_perimeter_pl';
	protected $primaryKey = 'mppl_id';
	const CREATED_AT = 'mppl_date_insert';
	const UPDATED_AT = 'mppl_date_update';
	protected $fillable = [
	    'mppl_mc_id',
	    'mppl_name',
	    'mppl_jml_lantai',
	    'mppl_kapasitas',
	    'mppl_alamat',
	    'mppl_mpro_id',
	    'mppl_mkab_id',
	    'mppl_gmap',
	    'mppl_mpmk_id',
	    'mppl_pic',
	    'mppl_email',
	    'mppl_no_hp',
	    'mppl_user_insert',
	    'mppl_user_update',
	    'mppl_qr'
    ];
}
