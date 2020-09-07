<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MstSosialisasiKategori extends Model {
    protected $table = 'master_sosialisasi_kategori';
	protected $primaryKey = 'mslk_id';
	const CREATED_AT = 'mslk_date_insert';
	const UPDATED_AT = 'mslk_date_update';
	protected $fillable = [
	    'mslk_name'
    ];
}
