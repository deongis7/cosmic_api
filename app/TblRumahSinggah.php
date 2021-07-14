<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TblRumahSinggah extends Model {
    protected $table = 'table_rumahsinggah';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id','mc_id','alamat','prov_id','kota_id','kapasitas','ruangan_available','pic_nik',
        'user_update','user_insert','biaya_ygdiperlukan','membayar','file','nama_rumahsinggah','jenis_kasus','fas_rumah_id',
        'kriteria_id','pic_kontak','file2'
    ];
    const CREATED_AT = 'date_insert';
    const UPDATED_AT = 'date_update';
}
