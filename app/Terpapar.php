<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Terpapar extends Model {
    protected $table = 'table_terpapar';
    protected $primaryKey = 'id';
    protected $fillable = [
        'kd_perusahaan','perusahaan'
    ];
}
