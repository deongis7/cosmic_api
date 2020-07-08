<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sosialisasi extends Model {
    protected $table = 'transaksi_sosialisasi';
    protected $primaryKey = 'ts_id';
    protected $fillable = [
        'ts_nama_kegiatan','ts_tanggal','mc_id'
    ];
}
