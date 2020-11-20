<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    protected $table = 'master_layanan_produk';
    protected $primaryKey = 'mlp_id';
    protected $fillable = [
        'mlp_id','mlp_name','mlp_by','mlp_filename','mlp_desc',
        'mlp_active'
    ];
    const CREATED_AT = 'mlp_date_insert';
    const UPDATED_AT = 'mlp_date_update';
}
