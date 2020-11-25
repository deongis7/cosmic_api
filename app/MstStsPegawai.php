<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MstStsPegawai extends Model {
    protected $table = 'master_status_pegawai';
	protected $primaryKey = 'msp_id';
	const CREATED_AT = 'msp_date_insert';
	const UPDATED_AT = 'msp_date_update';
	protected $fillable = [
	    'msp_name'
    ];
}
