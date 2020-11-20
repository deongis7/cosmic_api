<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Region extends Model
{
    protected $table = 'master_region';
	protected $primaryKey = 'mr_id';
	const CREATED_AT = 'mr_date_insert';
	const UPDATED_AT = 'mr_date_update';
}
