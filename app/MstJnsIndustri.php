<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class MstJnsIndustri extends Model {
    protected $table = 'master_jns_industri';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'jenis'
    ];
}