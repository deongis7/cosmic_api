<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TblPengajuanSertifikasi extends Model {
    protected $table = 'table_pengajuan_sertifikasi';
    protected $primaryKey = 'tbps_id';
    protected $fillable = [
        'tbps_id','tbps_mc_id','tbps_nama_pj','tbps_no_tlp_pj','tbps_email_pj',
        'tbps_status','tbps_mlp_id'
    ];
    const CREATED_AT = 'tbps_date_insert';
    const UPDATED_AT = 'tbps_date_update';
}
