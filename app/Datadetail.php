<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Datadetail extends Model {
    protected $table = 'data_detail1';
    protected $primaryKey = 'id';
    protected $fillable = [
        'kd_perusahaan','perusahaan'
    ];
}
