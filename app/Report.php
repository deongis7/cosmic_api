<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model {
    protected $table = 'transaksi_report';
    protected $primaryKey = 'tr_id';
    protected $fillable = [
        'tr_mpml_id',
        'tr_laporan',
        'tr_file1',
        'tr_file2',
        'tr_user_insert',
        'tr_user_update',
        'tr_no',
        'tr_penanggungjawab',
        'tr_tl_file1',
        'tr_tl_file2',
        'tr_close'
    ];
    const CREATED_AT = 'tr_date_insert';
    const UPDATED_AT = 'tr_date_update';
}
