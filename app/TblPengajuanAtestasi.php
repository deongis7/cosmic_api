<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TblPengajuanAtestasi extends Model {
    protected $table = 'table_pengajuan_atestasi';
    protected $primaryKey = 'tbpa_id';
    protected $fillable = [
        'tbpa_id','tbpa_mc_id','tbpa_nama_pj','tbpa_no_tlp_pj','tbpa_email_pj',
        'tbpa_perimeter','tbpa_status','tbpa_mlp_id'
    ];
    const CREATED_AT = 'tbpa_date_insert';
    const UPDATED_AT = 'tbpa_date_update';
}
