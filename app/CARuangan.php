<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CARuangan extends Model {
    protected $table = 'master_car';
    protected $primaryKey = 'mcar_id';
    protected $fillable = [
        'mcar_name','mcar_active'
    ];
    const CREATED_AT = 'mcar_date_insert';
    const UPDATED_AT = 'mcar_date_update';
}
