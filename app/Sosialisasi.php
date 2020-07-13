<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sosialisasi extends Model {
    protected $table = 'transaksi_sosialisasi';
    protected $primaryKey = 'ts_id';
    protected $fillable = [
        'ts_mc_id','ts_nama_kegiatan','ts_tanggal',
        'ts_user_insert','ts_file1','ts_file2','ts_file1_tumb','ts_file2_tumb'
    ];
    const CREATED_AT = 'ts_date_insert';
    const UPDATED_AT = 'ts_date_update';
}
