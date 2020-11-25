<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TblPerimeterDetail extends Model
{
    protected $table = 'table_perimeter_detail';
	protected $primaryKey = 'tpmd_id';
	protected $fillable = [
       'tpmd_mpml_id','tpmd_mcr_id', 'tpmd_cek', 'tpmd_order'
    ];
}
