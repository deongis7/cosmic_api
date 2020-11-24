<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TblProtokol extends Model
{
    protected $table = 'table_protokol';
	protected $primaryKey = 'tbpt_id';
	const CREATED_AT = 'tbpt_date_insert';
	const UPDATED_AT = 'tbpt_date_update';
	protected $fillable = [
       'tbpt_mc_id','tbpt_mpt_id', 'tbpt_filename', 'tbpt_user_insert','tbpt_user_update',
    ];
}
