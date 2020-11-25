<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Protokol extends Model
{
    protected $table = 'master_protokol';
	protected $primaryKey = 'mpt_id';
	const CREATED_AT = 'tbpt_date_insert';
	const UPDATED_AT = 'tbpt_date_update';
	protected $fillable = [
       'mpt_name','mpt_active'
    ];
}
