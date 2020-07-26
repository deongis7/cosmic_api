<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class TrnKasus extends Model {
    protected $table = 'transaksi_kasus';
	protected $primaryKey = 'tk_id';
	const CREATED_AT = 'tk_date_insert';
	const UPDATED_AT = 'tk_date_update';
	protected $fillable = [
	    'tk_mc_id',
	    'tk_nama',
	    'tk_msp_id',
	    'tk_mpro_id',
	    'tk_mkab_id',
	    'tk_msk_id',
	    'tk_date_positif',
	    'tk_date_meninggal',
	    'tk_date_sembuh',
	    'tk_user_insert',
	    'tk_user_update',
	    'tk_tempat_perawatan',	    
    ];
}
