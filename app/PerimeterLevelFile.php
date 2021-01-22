<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class PerimeterLevelFile extends Model
{
    protected $table = 'master_perimeter_level_file';
	protected $primaryKey = 'mpmlf_id';
	const CREATED_AT = 'mpmlf_date_insert';
	const UPDATED_AT = 'mpmlf_date_update';
	protected $fillable = [
       'mpmlf_mpml_id','mpmlf_file','mpmlf_file_tumb','mpmlf_user_insert','mpmlf_user_update'
    ];

}
