<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TblPerimeterClosed extends Model
{
    protected $table = 'table_perimeter_closed';
	protected $primaryKey = 'tbpc_id';
    const CREATED_AT = 'tbpc_date_insert';
    const UPDATED_AT = 'tbpc_date_update';
	protected $fillable = [
       'tbpc_mpml_id','tbpc_status','tbpc_alasan','tbpc_requestor','tbpc_approval','tbpc_startdate', 'tbpc_enddate'
    ];
}
