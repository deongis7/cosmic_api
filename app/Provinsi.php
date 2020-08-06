<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Provinsi extends Model
{
    protected $table = 'master_provinsi';
	protected $primaryKey = 'mpro_id';
	const CREATED_AT = 'mpro_date_insert';
	const UPDATED_AT = 'mpro_date_update';
	protected $fillable = [
        'mpro_name'
    ];
}
