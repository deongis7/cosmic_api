<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TblStatusPengajuanAtestasi extends Model {
    protected $table = 'table_status_pengajuan_atestasi';
    protected $primaryKey = 'tbspa_id';
    protected $fillable = [
        'tbspa_id','tbspa_mpm_id','tbspa_status','tbspa_tbpa_id','tbspa_petugas'
    ];
    const CREATED_AT = 'tbspa_date_insert';
    const UPDATED_AT = 'tbspa_date_update';
}
