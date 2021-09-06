<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TmpPerimeterList extends Model
{
  protected $connection = 'pgsql4';
  protected $table = 'temp_parameter_list';
  protected $primaryKey = 'id';
	protected $fillable = [
        'mc_id','status_pic','status_fo','status_bumn','last_update','nik_pic','nik_fo','id_region','region','id_perimeter','nama_perimeter','alamat','kategori','status_monitoring','percentage','provinsi','kabupaten','lockdown','keterangan_lockdown'
    ];
    const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';
}
