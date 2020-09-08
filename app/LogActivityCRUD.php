<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class LogActivityCRUD extends Model
{
    protected $table = 'log_aktivitas_crud';
	protected $primaryKey = 'lac_id';
	const CREATED_AT = 'lac_date_insert';
    const UPDATED_AT = null;
}
