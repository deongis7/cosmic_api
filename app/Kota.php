<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Kota extends Model
{
    protected $table = 'master_kabupaten';
	protected $primaryKey = 'mkab_id';
	const CREATED_AT = 'mkab_date_insert';
	const UPDATED_AT = 'mkab_date_update';
	protected $fillable = [
        'mkab_name','mkab_mpro_id','mkab_mpro_name'
    ];
}
