<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Perimeter extends Model
{
    protected $table = 'master_perimeter';
	protected $primaryKey = 'mpm_id';
	const CREATED_AT = 'mpm_date_insert';
	const UPDATED_AT = 'mpm_date_update';
}
