<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TblAgregasiDataPegawai extends Model {
    protected $table = 'table_agregasi_data_pegawai';
    protected $primaryKey = 'tad_id';
    protected $fillable = [
        'tad_id','tad_mc_id','tad_peg_tetap','tad_peg_alihdaya','tad_peg_konfirmasi','tad_peg_gejala_berat','tad_akum_peg_konfirmasi','tad_akum_peg_sembuh',
        'tad_user_update','tad_user_insert','tad_akum_peg_meninggal'
    ];
    const CREATED_AT = 'tad_date_insert';
    const UPDATED_AT = 'tad_date_update';
}
