<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MstStsKasus extends Model {
    protected $table = 'master_status_kasus';
	protected $primaryKey = 'msk_id';
	const CREATED_AT = 'msk_date_insert';
	const UPDATED_AT = 'msk_date_update';
	protected $fillable = [
	    'msk_name',
	    'msk_name2'
    ];
}
