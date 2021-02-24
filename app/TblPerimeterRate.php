<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TblPerimeterRate extends Model
{
    protected $table = 'table_perimeter_rate';
	protected $primaryKey = 'tbpmr_id';
  const CREATED_AT = 'tbpmr_date_insert';
	protected $fillable = [
       'tbpmr_mpm_id','tbpmr_rate', 'tbpmr_feedback'
    ];
}
